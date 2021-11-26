<?php

namespace App\Services;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\RequestStack;
use App\Entity\MDWPaniers;
use App\Repository\MDWPaniersRepository;
use App\Repository\MDWCodesPromosUsersRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Controller\SecurityController;
use DateTime;

class PaniersService {

    private $security;
    private $paniersRepository;
    private $codesPromosUsersRepository;
    private $entityManager;
    private SecurityController $securityController;

    public function __construct(Security $security,
                                MDWPaniersRepository $paniersRepository,
                                MDWCodesPromosUsersRepository $codesPromosUsersRepository,
                                EntityManagerInterface $entityManager,
                                RequestStack $requestStack,
                                SecurityController $securityController,
                                )
    {
        // Avoid calling getUser() in the constructor: auth may not
        // be complete yet. Instead, store the entire Security object.
        $this->security = $security;
        $this->requestStack = $requestStack;
        $this->paniersRepository = $paniersRepository;
        $this->codesPromosUsersRepository = $codesPromosUsersRepository;
        $this->entityManager = $entityManager;
        $this->securityController = $securityController;
    }

    public function getPanier() {
        $user = $this->getUtilisateur();
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

    public function getUtilisateur() {
        $user_connecte = $this->security->getUser();
        $user = null;

        if($user_connecte !== null) {
            $user = $user_connecte;
        } else {
            $session = $this->requestStack->getSession();
            if($session->get("guest") !== null) {
                $user = $session->get("guest");
            } else {
                $user = $this->securityController->guestCreator();
            }
        }

        return $user;
    }

    public function panierGuestVersPanierConnecte() {
        $session = $this->requestStack->getSession();
        $user_session = $session->get("guest");
        $panier_user = $this->getPanier();

        if($user_session !== null) {
            $panier_guest = $this->paniersRepository->findOneBy(["user" => $user_session]);

            if($panier_guest !== null) {
                //on verifie qu'il y a au moins 1 produit lie à panier_guest
                if($panier_guest->getProduits()->count() !== 0) {
                    //reset du contenu du panier user
                    foreach($panier_user->getProduits() as $liaison_user) {
                        $panier_user->removeProduit($liaison_user);
                    }

                    //chaque liason entre panier_guest et un produit est reaffectee a panier_user
                    foreach($panier_guest->getProduits() as $produit_guest) {
                        $panier_user->AddProduit($produit_guest);
                        $panier_guest->removeProduit($produit_guest);
                        $this->entityManager->persist($panier_guest);
                        $this->entityManager->flush();
                    }

                    $panier_user->setMontantHt($panier_guest->getMontantHt());
                    $panier_user->setMontantTtc($panier_guest->getMontantTtc());
                }

                $promo_guest = $panier_guest->getCodePromo(); //on recupere le code promo lie au panier guest

                //si panier_user contient deja un code promo, on l'efface
                //evite de devoir re-controller le code promo (si present) avec le nouveau contenu
                if($panier_user->getCodePromo() !== null) {
                    $panier_user->setCodePromo(null);
                }

                if($promo_guest !== null) {
                    $liaison = $this->codesPromosUsersRepository->findOneBy([
                        "user" => $this->security->getUser(),
                        "code_promo" => $promo_guest
                    ]);

                    if($liaison === null) { //code promo non utilise par user
                        //remarque: les conditions d'utilisation du code promo ont ete verifiees lors de sa liaison
                        //au panier guest. A ce niveau, le panier user precedent a ete vidé (si existant) et il recupere
                        //les donnees du panier guest => pas besoin de re-controler les conditions pour le code promo
                        $panier_user->setCodePromo($promo_guest);
                    }

                    //remarque: a ce niveau, on ne fait que lier le code promo au panier
                    //la liaison entre code promo et user se fait au moment du paiement valide
                }

                $session->set("guest", null);
                $panier_user->setDateModification(new DateTime());
                $this->entityManager->persist($panier_user);
                $this->entityManager->remove($panier_guest);
                $this->entityManager->flush();
            }   
        } else {
            //chargement des infos du panier user precedent en session
            $quantites_session = [];
            $nombre_articles = 0;
            foreach($panier_user->getProduits() as $panier_produit) {
                $quantites_session[$panier_produit->getProduit()->getId()] = $panier_produit->getQuantite();
                $nombre_articles += $panier_produit->getQuantite();
            }

            $quantites_session['nombre_articles_panier'] = $nombre_articles;
            $session->set('quantites_session', $quantites_session);
        }
    }

    public function controlePromoLiee() {
        $panier = $this->getPanier();
        $code_promo = $panier->getCodePromo();
        $erreur = "";
        $description = "";
        $reduction = "";
        $code = "";

        if($code_promo !== null) {
            $code = $code_promo->getCode();
            $description = $code_promo->getDescription();
            //gestion dates validite
            if($code_promo->getDateDebutValidite() > new DateTime() || $code_promo->getDateFinValidite() < new DateTime()) {
                if($code_promo->getDateDebutValidite() > new DateTime()) {
                    $erreur = "ce code promo sera valide à partir du " . $code_promo->getDateDebutValidite()->format('d/m/Y');
                } else {
                    $erreur = "ce code promo était valide jusqu'au " . $code_promo->getDateFinValidite()->format('d/m/Y');
                }
            }
            //gestion minimum d'achat
            else if($panier->getMontantTtc() < $code_promo->getMinimumAchat()) {
                $erreur = "Vous ne remplissez pas les conditions : " . $description;
            } else if($this->security->getUser() !== null) {  //si user connecte

                $user = $this->getUtilisateur();
                $promos_users = $user->getCodesPromos(); //recuperation des entites pivots codePromo_users lies au user

                foreach($promos_users as $promo_user) { //parcours des liaisons pivots
                    if($promo_user->getCodePromo() === $code_promo) {
                        $erreur = "Vous avez déjà utilisé ce code promo";  
                    }
                }
            }

            if($erreur !== "") {
                $this->annulePromo();
            } else {
                $reduction = $code_promo->getValeur();

                if($code_promo->getTypePromo() === "proportionnelle") {
                    $reduction = $panier->getMontantTtc() * ($reduction/10000);
                }
            }
        } else {
            $erreur = "nocode";
        }

        return [
            "erreur" => $erreur,
            "code" => $code,
            "description" => $description,
            "reduction" => $reduction,
        ];
    }

    public function annulePromo() {
        $panier = $this->getPanier();
        $promo = $panier->getCodePromo();
        if($promo !== null) {
            $promo->removePanier($panier);
            $this->entityManager->persist($promo);
            $this->entityManager->flush();
        }
    }

    public function setQuantitesEnSession($id_produit, $quantite) {
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

    public function controleQuantites() {
        $editions = [];
        $suppressions = [];
        $panier = $this->getPanier();

        foreach($panier->getProduits() as $panier_produit) {
            $produit = $panier_produit->getProduit();
            $tarifs = $produit->getTarifEffectif();

            if(!$produit->getCommandableSansStock()) {
                if($produit->getQuantiteStock() === 0) {
                    $panier->setMontantHt($panier->getMontantHt() - ($panier_produit->getQuantite() * round($tarifs['ht'])));
                    $panier->setMontantTtc($panier->getMontantTtc() - ($panier_produit->getQuantite() * round($tarifs['ttc'])));
                    $suppressions[$produit->getId()] = $produit->getNom();
                    $this->setQuantitesEnSession($produit->getId(), 0);
                    $panier->removeProduit($panier_produit);
                } else if($produit->getQuantiteStock() < $panier_produit->getQuantite()) {
                    $quantite_retrait = $panier_produit->getQuantite() - $produit->getQuantiteStock();
                    $panier->setMontantHt($panier->getMontantHt() - ($quantite_retrait * round($tarifs['ht'])));
                    $panier->setMontantTtc($panier->getMontantTtc() - ($quantite_retrait * round($tarifs['ttc'])));
                    $editions[$produit->getId()] = $produit->getNom();
                    $panier_produit->setQuantite($produit->getQuantiteStock());
                    $this->entityManager->persist($panier_produit);
                    $this->setQuantitesEnSession($produit->getId(), $produit->getQuantiteStock());
                }
            }
        }

        $panier->setDateModification(new DateTime());
        $this->entityManager->persist($panier);
        $this->entityManager->flush();
        return ["editions" => $editions,
                "suppressions" => $suppressions
        ];
    }
}

?>