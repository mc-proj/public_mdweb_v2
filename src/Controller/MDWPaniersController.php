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

        //test local pour fct ajout panier
        /*$panier = $this->getPanier();
        $paniers_produits = $panier->getProduits();
        foreach($paniers_produits as $pp) {
            dd($pp->getProduit()->getNom());
            dd($pp->getQuantite());  //donne bien qte
        }*/
        //

        //@TODO: controlle des quantites produits + modofication qtes + flashbag si necessaire

        return $this->render('mdw_paniers/index.html.twig', [
            //'controller_name' => 'MDWPaniersController',
            'panier' => $panier, //provi
        ]);
    }

    ///modifie-quantite
    #[Route('/modifie-quantite', name: 'modifie_panier')]
    public function editeQuantite(Request $request) {
        $quantite = $request->request->get("quantite");
        $id_produit = $request->request->get("id_produit");
        $mode = $request->request->get("mode");

        //secu anti bricoleur
        if($quantite == '' || $quantite < 1) {

            //return back a erreur ?
            //$is_ok = false;
        }

        else {
            $produit = $this->produitsRepository->findOneBy(["id" => $id_produit]);

            if($produit !== null) {
                $presence_produit = false;
                $panier = $this->getPanier();

                //parcours des produits lies au panier (via la table pivot paniers_produits)
                foreach($panier->getProduits() as $panier_produit) {
                    //si produit deja present dans panier
                    if($panier_produit->getProduit()->getId() === $id_produit) {
                        $presence_produit = true;
                        $suppression = false;

                        //ajout retrait suppression

                        if($mode === "ajout") {
                            //si qte panier + qte ajout <= qte en stock ==> simple incrementation qte panier
                            if(($panier_produit->getQuantite() + $quantite) <= $produit->getQuantiteStock()) {
                                $panier_produit->setQuantite($panier_produit->getQuantite() + $quantite);
                            } else {
                                //on ajoute le met tt le stock ds panier (on veux plus que ce qui est dispo en stock a ce niveau)
                                //flashbag
                                $panier_produit->setQuantite($produit->getQuantiteStock());
                            }
                        } else if($mode === "retrait") {
                            if(($panier_produit->getQuantite() - $quantite) > 0) {
                                $panier_produit->setQuantite($quantite);
                            } else {
                                $suppression = true;
                            }
                        }

                        if($mode === "suppression" || $suppression) {
                            $panier->removeProduit($panier_produit);
                        }
                    }
                }

                //produit absent du panier de base
                if(!$presence_produit && $mode === "ajout") {
                    $panier_produit = new MDWPaniersProduits();
                    $panier_produit->setProduit($produit);

                    //la qte a ajouter est dispo en stock
                    if($quantite <= $produit->getQuantiteStock()) {
                        $panier_produit->setQuantite($quantite);
                    } else {
                        //flashbag
                        $panier_produit->setQuantite($produit->getQuantiteStock());
                    }

                    $this->entityManager->persist($panier_produit);
                    $this->entityManager->flush();
                }
            } else {
                //return erreur;
            }
            
        }
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
