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
use Symfony\Component\HttpFoundation\Session\SessionInterface;
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
    private $session;
    private $entityManager;

    public function __construct(MDWPaniersRepository $paniersRepository,
                                MDWProduitsRepository $produitsRepository,
                                SecurityController $securityController,
                                SessionInterface $session,
                                EntityManagerInterface $entityManager) {
        $this->paniersRepository = $paniersRepository;
        $this->produitsRepository = $produitsRepository;
        $this->securityController = $securityController;
        $this->session = $session;
        $this->entityManager = $entityManager;
    }

    #[Route('/', name: 'accueil_panier')]
    public function index(): Response
    {
        //$panier = $this->paniersRepository->findOneBy(['id' => 0]);
        //dd($panier); //bien null si rien trouve
        $panier = $this->getPanier();

        //test local conception fct calcul ttc article ac promo ou pas
        /*
                    produit->getTarif()  getTarifPromo() getTvaActive() getTauxTva()(!get entite!)
                    getDateDebutPromo()  getDateFinPromo()
                    */
            
            /*        $produit = $this->produitsRepository->findOneBy(['id' => 126]); //produit_5
            dd($produit->getTarifEffectif());*/
        //

        //@TODO: controlle des quantites produits + modification qtes + flashbag si necessaire

        return $this->render('mdw_paniers/index.html.twig', [
            //'controller_name' => 'MDWPaniersController',
            'panier' => $panier, //provi
        ]);
    }

    ///modifie-quantite
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
            $quantite_ajout = 0; //negative dans le cas d'un retrait
            $presence_produit = false;
            $panier = $this->getPanier();

            //parcours des produits lies au panier (via la table pivot paniers_produits)
            foreach($panier->getProduits() as $panier_produit) {
                //recuperation du nombre d'articles dans le panier AVANT ajout/retrait
                $nombre_articles_panier += $panier_produit->getQuantite();

                //si produit deja present dans panier
                if($panier_produit->getProduit()->getId() === $id_produit) {
                    $presence_produit = true;
                    $suppression = false;
                    //$mode => ajout retrait suppression

                    /*
                    ajouter message succes ds retour ?
                    qte bien ajoutee
                    qte all stock ajoute
                    max deja atteint => 0 ajout
                    retrait qte OK
                    retrait article ok
                    */

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
                    }
                    //persist panier ?
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
                $this->entityManager->persist($panier_produit);
            }

            if($mode !== "suppression") {
                $panier_produit->setQuantite($quantite_finale);
            }
            
            $nombre_articles_panier += $quantite_ajout; 
            $tarifs = $produit->getTarifEffectif();
            $panier->setMontantHt($panier->getMontantHt() + $quantite_ajout * $tarifs['ht']);
            $panier->setMontantTtc($panier->getMontantTtc() + $quantite_ajout * $tarifs['ttc']);
            $this->entityManager->persist($panier);
            $this->entityManager->flush();

            $this->quantitesEnSession($produit->getId(), $quantite_finale);

            $retour = [
                "quantite_finale_produit" => $quantite_finale,
                "nombre_articles_panier" => $nombre_articles_panier,
                "total_ht" => $panier->getMontantHt(),
                "total_ttc" => $panier->getMontantTtc()
            ];
        } else {
            $retour = ["erreur" => "Erreur: vous tentez une modification sur un produit inconnu"];
        }

        $response = json_encode($retour);
        $response = new JsonResponse($response);
        return $response;
    }

    private function quantitesEnSession($id_produit, $quantite) {
        $quantites = $this->session->get('quantites_session');

        if($quantites === null) {
            $this->session->set('quantites_session', [$id_produit => $quantite]);
        } else {
            $quantites[$id_produit] = $quantite;
        }

        $this->session->set('quantites_session', $quantites);
    }

    private function getPanier() {
        $user = null;

        if($this->getUser() !== null) {
            $user = $this->getUser();
        } else {
            if($this->session->get("guest") !== null) {
                $user = $this->session->get("guest");
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
