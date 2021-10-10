<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\MDWPaniersRepository;
use App\Controller\SecurityController;
use App\Entity\MDWPaniers;
use App\Entity\MDWPaniersProduits;
use App\Repository\MDWProduitsRepository;
use Symfony\Component\HttpFoundation\RequestStack;
use Doctrine\ORM\EntityManagerInterface;
use DateTime;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

#[Route('/paniers')]

class MDWPaniersController extends AbstractController
{
    private $paniersRepository;
    private $produitsRepository;
    private SecurityController $securityController;
    private $requestStack;
    private $entityManager;

    public function __construct(MDWPaniersRepository $paniersRepository,
                                MDWProduitsRepository $produitsRepository,
                                SecurityController $securityController,
                                RequestStack $requestStack,
                                EntityManagerInterface $entityManager) {
        $this->paniersRepository = $paniersRepository;
        $this->produitsRepository = $produitsRepository;
        $this->securityController = $securityController;
        $this->entityManager = $entityManager;
        $this->requestStack = $requestStack;
    }

    #[Route('/', name: 'accueil_panier')]
    public function index(): Response
    {
        $panier = $this->getPanier();
        //@TODO: controlle des quantites produits + modification qtes + flashbag si necessaire

        //test begin
        /*$session = $this->requestStack->getSession();
        $quantites = $session->get('quantites_session');
        dd($quantites);*/
        //test end

        return $this->render('mdw_paniers/index.html.twig', [
            //'controller_name' => 'MDWPaniersController',
            'panier' => $panier, //provi
        ]);
    }

    #[Route('/modifie-quantite', name: 'modifie_panier', methods: 'POST')]
    public function editeQuantite(Request $request) {
        $quantite = $request->request->get("quantite");
        $id_produit = $request->request->get("id_produit");
        $mode = $request->request->get("mode");
        $retour = null;
        $produit = $this->produitsRepository->findOneBy(["id" => $id_produit]);
        $nombre_articles_panier = 0;
        
        //secu modification front par user
        if($quantite == '' || $quantite < 1) {
            $quantite = 0;
        }

        if($produit !== null) {
            $quantite_finale = 0;
            $quantite_ajout = 0; //peux avoir une valeur negative dans le cas d'un retrait
            $presence_produit = false;
            $panier = $this->getPanier();

            //parcours des produits lies au panier (via la table pivot paniers_produits)
            foreach($panier->getProduits() as $panier_produit) {
                //recuperation du nombre d'articles dans le panier AVANT ajout/retrait
                $nombre_articles_panier += $panier_produit->getQuantite();

                //si produit deja present dans panier
                if($panier_produit->getProduit()->getId() === intval($id_produit)) {
                    $presence_produit = true;
                    $suppression = false;

                    //$mode possibles => "ajout", "retrait", "suppression"                    
                    if($mode === "ajout") {
                        //si qte panier + qte ajout <= qte en stock ==> simple incrementation qte panier
                        if(($panier_produit->getQuantite() + $quantite) <= $produit->getQuantiteStock()) {
                            $quantite_finale = $panier_produit->getQuantite() + $quantite;
                        } else {
                            //on ajoute le met tt le stock ds panier (on veux plus que ce qui est dispo en stock a ce niveau)
                            $quantite_finale = $produit->getQuantiteStock();
                        }
                    } else if($mode === "retrait") {
                        if(($panier_produit->getQuantite() - $quantite) > 0) {
                            $quantite_finale = $panier_produit->getQuantite() - $quantite;
                        } else {
                            $suppression = true;
                        }
                    }
                    $quantite_ajout = $quantite_finale - $panier_produit->getQuantite();

                    if($mode === "suppression" || $suppression) {
                        $quantite_ajout = -$panier_produit->getQuantite();
                        $panier->removeProduit($panier_produit);
                        $quantite_finale = 0;
                    } else {
                        $panier_produit->setQuantite($quantite_finale);
                        $this->entityManager->persist($panier_produit);
                    }
                }
            }

            //produit absent du panier de base
            if(!$presence_produit && $mode === "ajout") {
                $panier_produit = new MDWPaniersProduits();
                $panier_produit->setPanier($panier);
                $panier_produit->setProduit($produit);

                //la qte a ajouter est dispo en stock
                if($quantite <= $produit->getQuantiteStock()) {
                    $quantite_finale = $quantite;
                } else {
                    $quantite_finale = $produit->getQuantiteStock();
                }

                $quantite_ajout = $quantite_finale;
                $panier_produit->setQuantite($quantite_finale);
                $this->entityManager->persist($panier_produit);
            }
            
            $nombre_articles_panier += $quantite_ajout; 
            $tarifs = $produit->getTarifEffectif();
            $panier->setMontantHt($panier->getMontantHt() + $quantite_ajout * $tarifs['ht']);
            $panier->setMontantTtc($panier->getMontantTtc() + $quantite_ajout * $tarifs['ttc']);
            $this->entityManager->persist($panier);
            $this->entityManager->flush();
            $this->quantitesEnSession($id_produit, $quantite_finale);

            $retour = [
                "produit_dispo_sans_stock" => $produit->getCommandableSansStock(),
                "quantite_produit_stock" => $produit->getQuantiteStock(),
                "quantite_finale_produit" => $quantite_finale,
                "nombre_articles_panier" => $nombre_articles_panier,
                "total_ht" => $panier->getMontantHt(),
                "total_ttc" => $panier->getMontantTtc(),
            ];
        } else {
            $retour = ["erreur" => "Erreur: vous tentez une modification sur un produit inconnu"];
        }

        $response = json_encode($retour);
        $response = new JsonResponse($response);
        return $response;
    }

    #[Route('/apercu_panier', name: 'panier_apercu', methods: 'POST')]
    public function getApercuPanier() {
        $panier = $this->getPanier();
        $resultats = [];

        foreach($panier->getProduits() as $panier_produit) {
            $produit = $panier_produit->getProduit();
            $images = $produit->getImages();
            $donnees = [
                "id" => $produit->getId(),
                "quantite" => $panier_produit->getQuantite(),
                "nom" => $produit->getNom(),
                "tarif" => $produit->getTarifEffectif(),
                "image" => $images[0]->getImage()
            ];
            array_push($resultats, $donnees);
        }

        $response = json_encode($resultats);
        $response = new JsonResponse($response);
        return $response;
    }

    private function quantitesEnSession($id_produit, $quantite) {
        /*$session = $this->requestStack->getSession();
        $quantites = $session->get('quantites_session');

        if($quantites === null) {
            $session->set('quantites_session', [$id_produit => $quantite]);
        } else {
            $quantites[$id_produit] = $quantite;  //a corriger: pr le moment on remplace juste la valeur
            $this->session->set('quantites_session', $quantites);
        }*/

        $session = $this->requestStack->getSession();
        $quantites = $session->get('quantites_session');
        

        if($quantites === null) {
            $session->set('quantites_session', [
                $id_produit => $quantite,
                'nombre_articles_panier' => $quantite,
            ]);
        } else {
            $quantites[$id_produit] = $quantite;
            $nombre_articles_panier = 0;
            foreach($quantites as $index => $quantite_article) {
                if($index !== 'nombre_articles_panier') {
                    $nombre_articles_panier += $quantite_article;
                }
            }
            $quantites['nombre_articles_panier'] = $nombre_articles_panier;
            $session->set('quantites_session', $quantites);
        }

        
    }

    private function getPanier() {
        $user = null;

        if($this->getUser() !== null) {
            $user = $this->getUser();
        } else {
            $session = $this->requestStack->getSession();
            if($session->get("guest") !== null) {
                $user = $session->get("guest");
            } else {
                $user = $this->securityController->guestCreator();
            }
        }

        $panier = $this->paniersRepository->findOneBy(["user" => $user]);

        if($panier === null) {
            $panier = new MDWPaniers();
            $panier->setCommandeTerminee(false);
            $panier->setDateCreation(new DateTime());
            $panier->setDateModification(new DateTime());
            $panier->setMontantHt(0);
            $panier->setMontantTtc(0);
            $panier->setUser($user);
            $this->entityManager->persist($panier);
            $this->entityManager->flush();
        }

        return $panier;
    }
}
