<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Controller\SecurityController;
use App\Entity\MDWPaniers;
use App\Entity\MDWPaniersProduits;
use App\Repository\MDWProduitsRepository;
use App\Repository\MDWPaniersRepository;
use App\Repository\MDWCodesPromosRepository;
use Symfony\Component\HttpFoundation\RequestStack;
use Doctrine\ORM\EntityManagerInterface;
use DateTime;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Encoder\JsonEncode;

#[Route('/paniers')]

class MDWPaniersController extends AbstractController
{
    private $paniersRepository;
    private $produitsRepository;
    private $codesPromosRepository;
    private SecurityController $securityController;
    private $requestStack;
    private $entityManager;

    public function __construct(MDWPaniersRepository $paniersRepository,
                                MDWProduitsRepository $produitsRepository,
                                MDWCodesPromosRepository $codesPromosRepository, 
                                SecurityController $securityController,
                                RequestStack $requestStack,
                                EntityManagerInterface $entityManager) {
        $this->paniersRepository = $paniersRepository;
        $this->produitsRepository = $produitsRepository;
        $this->codesPromosRepository = $codesPromosRepository;
        $this->securityController = $securityController;
        $this->entityManager = $entityManager;
        $this->requestStack = $requestStack;
    }

    #[Route('/', name: 'accueil_panier')]
    public function index(): Response
    {
        $panier = $this->getPanier();

        //pur test begin -- test en cours: simple edition qte  -- need presence des produits 129 et 130 ds panier (produits 8 et 9)
        //sert just a trigger le controle qtes
        /*foreach($panier->getProduits() as $liaison) {
            $produit = $liaison->getProduit();
            if($produit->getId() === 130) {
                $liaison->setQuantite(80);
                $this->entityManager->persist($liaison);
                $tarifs = $produit->getTarifEffectif();
                $panier->setMontantHt($panier->getMontantHt() + 80 * $tarifs['ht']);
                $panier->setMontantTtc($panier->getMontantTtc() + 80 * $tarifs['ttc']);
                $panier->setDateModification(new DateTime());
                $this->entityManager->persist($panier);
            } else if($produit->getId() === 129) {
                $liaison->setQuantite(70);
                $this->entityManager->persist($liaison);
                $tarifs = $produit->getTarifEffectif();
                $panier->setMontantHt(70 * $tarifs['ht']);
                $panier->setMontantTtc(70 * $tarifs['ttc']);
                $panier->setDateModification(new DateTime());
                $this->entityManager->persist($panier);
            }
            $this->entityManager->flush();
        }*/
        //pur test end
        

        //@TODO: controlle des quantites produits + modification qtes + flashbag si necessaire
        $modifications = $this->controleQuantites();
        //dd($modifications);

        /*
        array:2 [▼
        "editions" => []  // forme id => nom
        "suppressions" => []  // forme id => nom
        ]
        */





        /*
        fct pr comparer qtes commandees ac qte dispos
            -> au besoin modif des qtes ds panier
            -> retour ids et noms des produits aux qtes modifiees
            -> cas simple diminution qte : sur vue flashBag "Les produits suivants ne sont plus disponibles dans les quantites demandees : {{ liste noms }} "
            -> cas produit plus dispo : sur vue flashBag "Désolé, les produits suivants ne sont plus disponibles et ont ete retires de votre panier (Veuillez nous excuser pour la gene occasionnee ?) "
            
            -- sur liste des produits sur vue panier
                -> qtes affichees sont celles du panier qui a ete modifie en amont (rien de special a faire a ce niveau)
                -> si la qte a ete modifiee, changer couleur fond de la ligne

                cas cmd sans stock devrait pouvoir se gerer 100% cote twig
                -> ligne ac cas qte insuffisante mais commandable sans stock, changer couleur fond ligne 
                                        + msg "(seuls) {{ X }} produits sont immediatements disponibles. Vous recevrez qte_commandee - X ulterieurement"
            

        */

        //test begin
        /*$session = $this->requestStack->getSession();
        $quantites = $session->get('quantites_session');
        dd($quantites);*/
        //test end

        return $this->render('mdw_paniers/index.html.twig', [
            //'controller_name' => 'MDWPaniersController',
            'panier' => $panier, //provi
            //'modifications' => $modifications
            'editions' => $modifications['editions'],
            'suppressions' => $modifications['suppressions'],
        ]);
    }

    #[Route('/modifie-quantite', name: 'modifie_panier', methods: 'POST')]
    public function editeQuantite(Request $request) {  //modifie les quantites dans un panier, pas le stock des produits (se fait à la validation du panier)
        //si user modifie front, texte sera changé en '' (puis en 0), nombre decimal sera tronque par la convertion
        $quantite = intval($request->request->get("quantite"));
        $id_produit = $request->request->get("id_produit");
        $mode = $request->request->get("mode");
        $retour = null;
        $produit = $this->produitsRepository->findOneBy(["id" => $id_produit]);
        $nombre_articles_panier = 0;
        $edite_supprime = false;
        $quantite_max_article = $this->getParameter('app.quantite_max_commande');
        
        //secu modification front par user
        if($quantite == '' || $quantite < 1 || $mode === "suppression") {
            $quantite = 0;

            if($mode === "ajout") {
                $quantite++;
            }
        }

        if($produit !== null && gettype($quantite) === "integer") { //a priori le gettype est useless mais secu en plus
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

                    /*if(($mode === "ajout" || $mode === "edition") && $produit->getCommandableSansStock()) {
                        if(($panier_produit->getQuantite() + $quantite) > $quantite_max_article) {
                            //$quantite = $quantite_max_article - $panier_produit->getQuantite();
                            $quantite = $quantite_max_article;
                        }
                    }*/

                    $limite = $produit->getQuantiteStock();

                    if($produit->getCommandableSansStock()) {
                        $limite = $quantite_max_article;
                    }

                    if($mode === "ajout") {
                        //deplace
                        /*$limite = $produit->getQuantiteStock();

                        if($produit->getCommandableSansStock()) {
                            $limite = $quantite_max_article;
                        }*/
                        
                        if(($panier_produit->getQuantite() + $quantite) <= $limite) {
                            $quantite_finale = $panier_produit->getQuantite() + $quantite;
                        } else {
                            $quantite_finale = $limite;
                        }
                    } else if($mode ==="edition") { 
                        if($quantite === 0) {
                            $suppression = true;
                            $edite_supprime = true;
                        } else {
                            if($quantite <= $limite) {
                                $quantite_finale = $quantite;
                            } else {
                                $quantite_finale = $limite;
                            }
                        }
                    } /*else if($mode === "retrait") {  //useless ?
                        if(($panier_produit->getQuantite() - $quantite) > 0) {
                            $quantite_finale = $panier_produit->getQuantite() - $quantite;
                        } else {
                            $suppression = true;
                        }
                    }*/
                    $quantite_ajout = $quantite_finale - $panier_produit->getQuantite();

                    if($mode === "suppression" || $suppression) {
                        $quantite_ajout = -$panier_produit->getQuantite();
                        $panier->removeProduit($panier_produit);
                        $this->entityManager->persist($panier);
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

                if(($quantite <= $produit->getQuantiteStock()) || $produit->getCommandableSansStock()) {
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
            $panier->setDateModification(new DateTime());
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
                "edite_supprime" => $edite_supprime,
            ];
        } else {
            $retour = ["erreur" => "Erreur: opération incorrecte"];
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

    #[Route('/vide_panier', name: 'vide_panier', methods: 'POST')]
    public function videPanier() {
        $session = $this->requestStack->getSession();
        $session->set('quantites_session', null);
        $panier = $this->getPanier();

        foreach($panier->getProduits() as $panier_produit) {
            $panier->removeProduit($panier_produit);
            $this->entityManager->persist($panier);
        }

        $promo = $panier->getCodePromo();
        if($promo !== null) {
            $promo->removePanier($panier);
            $this->entityManager->persist($promo);
        }

        $panier->setMontantHt(0);
        $panier->setMontantTtc(0);
        $panier->setDateModification(new DateTime());
        $this->entityManager->persist($panier);
        $this->entityManager->flush();
        return new JsonResponse();
    }

    #[Route('/promo', name: 'promo_panier', methods: 'POST')]
    public function PromoSurPanier(Request $request) {
        $code_recu = $request->request->get("code");
        $user = $this->getUtilisateur();

        //parcours des codes promos deja utilises
        foreach($user->getCodesPromos() as $code) {
            if($code->getCode() === $code_recu) {
                $retour = json_encode([
                    "erreur" => "ce code promo a déjà été utilisé"
                ]);
                return new JsonResponse($retour);
            }
        }

        $code_promo = $this->codesPromosRepository->findOneBy(["code" => $code_recu]);

        if($code_promo === null) {
            $retour = json_encode([
                "erreur" => "code promo inconnu"
            ]);
            return new JsonResponse($retour);
        } else {
            $panier = $this->getPanier();

            $this->annulePromo(); //remplace bloc com suivant
            /*
            //si une promo a deja ete appliquee, on l'annule
            $ancienne_promo = $panier->getCodePromo();
            if($ancienne_promo !== null) {
                $ancienne_promo->removePanier($panier);
                //suppression de la liaison entre le panier et l'ancien code promo
                $this->entityManager->persist($ancienne_promo);
                $this->entityManager->flush();
            }*/

            //minimum achat trop faible
            if($panier->getMontantTtc() < $code_promo->getMinimumAchat()) {
                $retour = json_encode([
                    "erreur" => "Vous ne remplissez pas les conditions : " . $code_promo->getDescription()
                ]);
                return new JsonResponse($retour);
            }

            $panier->setCodePromo($code_promo);
            $reduction = $code_promo->getValeur();

            if($code_promo->getTypePromo() === "proportionnelle") {
                $reduction = $panier->getMontantTtc() * ($reduction/10000);
            }

            $this->entityManager->persist($panier);
            $this->entityManager->flush();

            $retour = json_encode([
                "description" => $code_promo->getDescription(),
                "reduction" => $reduction,
            ]);
            return new JsonResponse($retour);
        }
    }

    #[Route('/reset_promo', name: 'reset_promo', methods: 'POST')]
    public function postResetPromo() {
        $this->annulePromo();
        return new JsonResponse();
    }

    private function annulePromo() {
        $panier = $this->getPanier();
        $promo = $panier->getCodePromo();
        if($promo !== null) {
            $promo->removePanier($panier);
            $this->entityManager->persist($promo);
            $this->entityManager->flush();
        }
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

    private function controleQuantites() {
        $editions = [];
        $suppressions = [];
        $panier = $this->getPanier();

        //!!! re-calcul du cout du panier
        // modifier panier => montant_ht, montant_ttc, date_modification
        //$panier->setDateModification(new DateTime());

        foreach($panier->getProduits() as $panier_produit) {
            $produit = $panier_produit->getProduit();
            $tarifs = $produit->getTarifEffectif(); //ajout pr recalcul total panier

            if(!$produit->getCommandableSansStock()) {
                if($produit->getQuantiteStock() === 0) {
                    //array_push($suppressions, [$produit->getId() => $produit->getNom()]);

                    //recalcul prix begin part 1
                    //$qte = -$panier_produit->getQuantite();
                    $panier->setMontantHt($panier->getMontantHt() - ($panier_produit->getQuantite() * $tarifs['ht']));
                    $panier->setMontantTtc($panier->getMontantTtc() - ($panier_produit->getQuantite() * $tarifs['ttc']));
                    //$this->entityManager->persist($panier);
                    //recalcul prix end part 1

                    

                    $suppressions[$produit->getId()] = $produit->getNom();


                    $this->quantitesEnSession($produit->getId(), 0);
                    $panier->removeProduit($panier_produit);
                } else if($produit->getQuantiteStock() < $panier_produit->getQuantite()) {
                    //array_push($editions, [$produit->getId() => $produit->getNom()]);

                    //recalcul prix begin part 2
                    $quantite_retrait = $panier_produit->getQuantite() - $produit->getQuantiteStock();

                    //-------------
                    /*
                    //id 129 diff de 30 OK
                    if($produit->getId() === 130) {
                        $test = [$produit->getId(), $quantite_retrait];
                        dd($test);  //id 130 diff de 20 OK
                    }*/

                    
                    /*if($produit->getId() === 130) {
                        dd($tarifs);
                        /*
                        array:2 [▼
                        "ht" => 6282
                        "ttc" => 6627.51
                        ]
                        *
                    }*/

                    //id 129
                    /*array:2 [▼
                    "ht" => 9135
                    "ttc" => 9637.425
                    ]*/

                    //quantite_retrait ok, tarifs ok

                    //-----------------
                    



                    $panier->setMontantHt($panier->getMontantHt() - ($quantite_retrait * $tarifs['ht']));
                    $panier->setMontantTtc($panier->getMontantTtc() - ($quantite_retrait * $tarifs['ttc']));
                    //$this->entityManager->persist($panier);
                    //recalcul prix end part 2

                    $editions[$produit->getId()] = $produit->getNom();

                    $panier_produit->setQuantite($produit->getQuantiteStock());
                    $this->entityManager->persist($panier_produit);
                    $this->quantitesEnSession($produit->getId(), $produit->getQuantiteStock());
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

    private function getUtilisateur() {
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

        return $user;
    }

    private function getPanier() {
        /*$user = null;

        if($this->getUser() !== null) {
            $user = $this->getUser();
        } else {
            $session = $this->requestStack->getSession();
            if($session->get("guest") !== null) {
                $user = $session->get("guest");
            } else {
                $user = $this->securityController->guestCreator();
            }
        }*/

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
}
