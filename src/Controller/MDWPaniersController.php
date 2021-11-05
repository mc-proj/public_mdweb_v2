<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Controller\SecurityController;
use App\Entity\MDWPaniers;
use App\Entity\MDWPaniersProduits;
use App\Entity\MDWFactures;
use App\Entity\MDWFacturesProduits;
use App\Entity\MDWCodesPromosUsers;
use App\Repository\MDWProduitsRepository;
use App\Repository\MDWPaniersRepository;
use App\Repository\MDWCodesPromosRepository;
use App\Repository\MDWCodesPromosUsersRepository;
use Symfony\Component\HttpFoundation\RequestStack;
use Doctrine\ORM\EntityManagerInterface;
use DateTime;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Encoder\JsonEncode;
use Stripe\Stripe;

#[Route('/paniers')]

class MDWPaniersController extends AbstractController
{
    private $paniersRepository;
    private $produitsRepository;
    private $codesPromosRepository;
    private $codesPromosUsersRepository;
    private SecurityController $securityController;
    private $requestStack;
    private $entityManager;

    //private $usersRepository;

    public function __construct(MDWPaniersRepository $paniersRepository,
                                MDWProduitsRepository $produitsRepository,
                                MDWCodesPromosRepository $codesPromosRepository, 
                                MDWCodesPromosUsersRepository $codesPromosUsersRepository,
                                SecurityController $securityController,
                                RequestStack $requestStack,
                                EntityManagerInterface $entityManager,
                                ) {
        $this->paniersRepository = $paniersRepository;
        $this->produitsRepository = $produitsRepository;
        $this->codesPromosRepository = $codesPromosRepository;
        $this->codesPromosUsersRepository = $codesPromosUsersRepository;
        $this->securityController = $securityController;
        $this->entityManager = $entityManager;
        $this->requestStack = $requestStack;
    }

    #[Route('/', name: 'accueil_panier')]
    public function index(): Response
    {
        $panier = $this->getPanier();
        $modifications = $this->controleQuantites();

        return $this->render('mdw_paniers/index.html.twig', [
            'panier' => $panier,
            'editions' => $modifications['editions'],
            'suppressions' => $modifications['suppressions'],
            'secu_promo' => $this->controlePromoLiee(),
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
        
        //secu anti modification front par user
        if($quantite === '' || $quantite < 1 || $mode === "suppression") {
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
                    $limite = $produit->getQuantiteStock();

                    if($produit->getCommandableSansStock()) {
                        $limite = $quantite_max_article;
                    }

                    if($mode === "ajout") {
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
                    }
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
            /*$panier->setMontantHt($panier->getMontantHt() + $quantite_ajout * $tarifs['ht']);
            $panier->setMontantTtc($panier->getMontantTtc() + $quantite_ajout * $tarifs['ttc']);*/

            $panier->setMontantHt($panier->getMontantHt() + $quantite_ajout * round($tarifs['ht']));
            $panier->setMontantTtc($panier->getMontantTtc() + $quantite_ajout * round($tarifs['ttc']));




            $panier->setDateModification(new DateTime());
            $this->entityManager->persist($panier);
            $this->entityManager->flush();
            $this->setQuantitesEnSession($id_produit, $quantite_finale);

            $retour = [
                "produit_dispo_sans_stock" => $produit->getCommandableSansStock(),
                "quantite_produit_stock" => $produit->getQuantiteStock(),
                "quantite_finale_produit" => $quantite_finale,
                "nombre_articles_panier" => $nombre_articles_panier,
                "total_ht" => $panier->getMontantHt(),
                "total_ttc" => $panier->getMontantTtc(),
                "edite_supprime" => $edite_supprime,
                'infos_promo' => $this->controlePromoLiee(),
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

        $panier->setAdresseLivraison(null);
        $panier->setMessage(null);
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

        //
        $code_promo = $this->codesPromosRepository->findOneBy(["code" => $code_recu]);
        $code_use = $this->codesPromosUsersRepository->findOneBy([
            "user" => $user,
            "code_promo" => $code_promo,
        ]);

        if($code_use !== null) {
            $date_utilisation = $code_use->getDateUtilisation()->format('d/m/Y');
            $retour = json_encode([
                "erreur" => "Vous avez déjà utilisé ce code promo le " . $date_utilisation,
            ]);
            return new JsonResponse($retour);
        }

        /*  original
        //parcours des codes promos deja utilises
        foreach($user->getCodesPromos() as $code) {
            if($code->getCodePromo()->getCode() === $code_recu) {
                $date_utilisation = $code->getDateUtilisation()->format('d/m/Y');
                $retour = json_encode([
                    "erreur" => "Vous avez déjà utilisé ce code promo le " . $date_utilisation,
                ]);
                return new JsonResponse($retour);
            }
        }

        $code_promo = $this->codesPromosRepository->findOneBy(["code" => $code_recu]);
        */

        if($code_promo === null) {
            $retour = json_encode([
                "erreur" => "code promo inconnu"
            ]);
            return new JsonResponse($retour);
        } else if($code_promo->getDateDebutValidite() > new DateTime() || $code_promo->getDateFinValidite() < new DateTime()) {
            if($code_promo->getDateDebutValidite() > new DateTime()) {
                $retour = json_encode([
                    "erreur" => "ce code promo sera valide à partir du " . $code_promo->getDateDebutValidite()->format('d/m/Y')
                ]);
            } else {
                $retour = json_encode([
                    "erreur" => "ce code promo était valide jusqu'au " . $code_promo->getDateFinValidite()->format('d/m/Y')
                ]);
            }
            return new JsonResponse($retour);
        } else {
            $panier = $this->getPanier();

            $this->annulePromo(); //annule le code promo deja lie (s'il y en a un)
            //controle minimum achat trop faible
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

    #[Route('/paiement', name: 'panier_paiement')]
    public function panierPaiement() {
        if($this->getUser() === null) {
            return $this->redirectToRoute('app_login');
        }

        $panier = $this->getPanier();
        
        if($panier->getProduits()->count() === 0) { //si panier vide
            return $this->redirectToRoute('accueil_panier');
        }

        //$user = $this->getUser();
        $user = $this->getUtilisateur();
        $cartes = [];
        $methodes_de_paiement = null;
        $session = $this->requestStack->getSession();

        //a priori useless
        //$this->session->set("stripe_pk", $this->getParameter("stripe_pk"));  //needed ? si oui utiliser methode 5.3 pr session
        
        \Stripe\Stripe::setApiKey(
            $this->getParameter("app.stripe_sk")
        );


        $stripe = new \Stripe\StripeClient(
            $this->getParameter("app.stripe_sk")
        );

        $customer = null;
        $date = new DateTime();
        $date = $date->format("d.m.y");

        if($user->getIdStripe() != null && $user->getIdStripe() != "") {

            $customer = $stripe->customers->retrieve(
                $user->getIdStripe(),
                []
            );

            $methodes_de_paiement = $stripe->paymentMethods->all([
                'customer' => $user->getIdStripe(),
                'type' => 'card',
            ]);
        }

        else {

            $customer = \Stripe\Customer::create();
        }

        $session->set("stripe_customer", $customer);
        //$this->session->set("stripe_customer", $customer); //needed ? si oui utiliser methode 5.3 pr session


        $code_promo = $panier->getCodePromo();
        $total = $panier->getMontantTtc();

        if($code_promo != null) {

            /*if($code_promo->getTypePromo() == "forfaitaire") {

                $reduction = $code_promo->getValeur();
            }
            
            else {

                $reduction = $panier->getMontantTtc() * $code_promo->getValeur()/10000;
            }*/

            $reduction = $code_promo->getValeur();

            if($code_promo->getTypePromo() === "proportionnelle") {
                $reduction = $panier->getMontantTtc() * ($reduction/10000);
            }
            //---

            $total = $panier->getMontantTtc() - $reduction;
            $total = intval($total);
        }

        //l'utilisateur a au moins une carte enregistree
        if($methodes_de_paiement != null) {

            try {

                $intent =  $stripe->paymentIntents->create([
                    'amount' => $total,
                    'currency' => 'eur',
                    'customer' => $customer->id,
                    'description' => 'ACHAT CB MARCHE DU WEB ' . $date,
                    //'receipt_email' => $this->getUser()->getEmail(),
                    'receipt_email' => $user->getEmail(),
                    'payment_method' => $methodes_de_paiement["data"][0]->id,
                ]);

                foreach($methodes_de_paiement as $moyen) {

                    $carte = [];
                    $carte["methode_id"] = $moyen->id;
                    $carte["brand"] = $moyen->card["brand"];
                    $carte["last4"] = $moyen->card["last4"];
                    $carte["exp_month"] = $moyen->card["exp_month"];
                    $carte["exp_year"] = $moyen->card["exp_year"];
                    array_push($cartes, $carte);
                }
            }

            catch (\Stripe\Exception\CardException $e) {

                // Error code will be authentication_required if authentication is needed
                echo 'Error code is:' . $e->getError()->code;
                $payment_intent_id = $e->getError()->payment_intent->id;
                $intent = \Stripe\PaymentIntent::retrieve($payment_intent_id);
            }
        }

        //l'utilisateur n'a aucune carte enregistree
        else {
            
            $intent =  $stripe->paymentIntents->create([
                'amount' => $panier->getMontantTtc(),
                'currency' => 'eur',
                'customer' => $customer->id,
                'payment_method_types' => ['card'],
                'description' => 'ACHAT CB MARCHE DU WEB ' . $date,
                //'receipt_email' => $this->getUser()->getEmail()
                'receipt_email' => $user->getEmail(),
            ]);
        }

        $session->set("payment_intent", $intent); 
        //$session->set("cartes", $cartes);  //useless ? -- remplace ds vue paimement par la var cartes envoyee par ctrleur

        //$this->session->set("payment_intent", $intent);//needed ? si oui utiliser methode 5.3 pr session
        //$this->session->set("cartes", $cartes);//needed ? si oui utiliser methode 5.3 pr session          

        return $this->render("mdw_paniers/paiement.html.twig", [

            'user' => $user,
            "panier" => $panier,
            "code_promo" => $code_promo,
            "cartes" => $cartes
        ]);
    }

    #[Route('/paiement_post', name: 'panier_paiement_post', methods: 'POST')]
    public function postPaiement(Request $request) {
        $stripe = new \Stripe\StripeClient(
            $this->getParameter("app.stripe_sk")  
        );

        \Stripe\Stripe::setApiKey(
            $this->getParameter("app.stripe_sk")
        );

        $conditions_lues = true;

        if($request->request->get("conditions_lues") == "false") {
            $conditions_lues = false;
        }

        if($request->request->get("adresse_differente") == "false") {
            $panier = $this->getPanier();
            $adresse = $panier->getAdresseLivraison();

            if($adresse !== null) {
                $panier->setAdresseLivraison(null);
                $this->entityManager->persist($panier);
                $this->entityManager->remove($adresse);
                $this->entityManager->flush();
            }
        }

        /*
        sont en session

        $session->set("stripe_customer", $customer);
        $session->set("payment_intent", $intent);
        $session->set("cartes", $cartes);
        */

        $session = $this->requestStack->getSession();
        $intent = $session->get("payment_intent");

        //utilisation d'une carte enregistree
        if($request->request->get("payment_method_id") != null) {
            try {
                //si le moyen de paiement est different de celui attache par defaut, on le change
                if($intent->payment_method != $request->request->get("payment_method_id")) {
                    $intent->payment_method = $request->request->get("payment_method_id");
                }
            } catch(\Stripe\Exception\CardException $e) {
                $payment_intent_id = $e->getError()->payment_intent->id;
                $payment_intent = \Stripe\PaymentIntent::retrieve($payment_intent_id);
            }
        }

        return new JsonResponse([
            "client_secret" => $intent["client_secret"],
            "conditions_lues" => $conditions_lues
        ]);
    }

    #[Route('/paiement_success', name: 'panier_paiement_succes', methods: 'POST')]
    public function paiementReussi() {
        //$now = new dateTime();
        $reduction = 0;
        $user = $this->getUtilisateur();
        $panier = $this->getPanier();

        if($panier->getProduits()->count() === 0) { //si panier vide
            return $this->redirectToRoute('accueil_panier');
        }

        //here
        $code_promo = $panier->getCodePromo();

        if($code_promo !== null) {

            $code_promo_user = new MDWCodesPromosUsers();
            $code_promo_user->setCodePromo($code_promo);
            $code_promo_user->setUser($user);
            $code_promo_user->setDateUtilisation(new dateTime());
            $this->entityManager->persist($code_promo_user);

            //$user->addCodesPromo($code_promo);
            //$code_promo->addUser($user);
            $reduction = $code_promo->getValeur();

            if($code_promo->getTypePromo() === "proportionnelle") {
                $reduction = $panier->getMontantTtc() * ($reduction/10000);
            }
        }
        /*
        panier --> adresse_livraison_id, commande_terminee, date_creation, montant_ht, montant_ttc, message, user_id, code_promo_id, date_modification
        facture --> user_id, adresse_livraison_id, code_promo_id, date_creation, montant_total,  montant_ht, montant_ttc, message
        */
        //creation facture
        $facture = new MDWFactures();
        $facture->setUser($user);
        $facture->setAdresseLivraison($panier->getAdresseLivraison());
        $facture->setCodePromo($panier->getCodePromo());
        $facture->setDateCreation(new dateTime());
        $facture->setMontantTotal($panier->getMontantTtc() - $reduction);
        $facture->setMontantHt($panier->getMontantHt());
        $facture->setMontantTtc($panier->getMontantTtc());
        $facture->setMessage($panier->getMessage());
        $this->entityManager->persist($facture);
        $this->entityManager->flush();


        foreach($panier->getProduits() as $panier_produit) {
            $facture_produit = new MDWFacturesProduits();
            $facture_produit->setFacture($facture);
            $facture_produit->setProduit($panier_produit->getProduit());
            $facture_produit->setQuantite($panier_produit->getQuantite());
            $this->entityManager->persist($facture_produit);
        }

        $this->entityManager->flush();
        $this->videPanier();


        //@TODO: creation facture
        //@TODO: reseter panier --> $this->videPanier();
        //@TODO: faire la vue
        return $this->render('mdw_paniers/paiement_reussi.html.twig', [

            'facture' => $facture,
            //'produits_lies' => $produits_lies*/
        ]);
    }

    #[Route('/paiement_fail', name: 'panier_paiement_fail', methods: 'POST')]
    public function paiemenentEchec(Request $request) {
        $erreur = $request->request->get("erreur");

        //@TODO: faire la vue correspondante
        return $this->render('mdw_paniers/paiement_echec.html.twig', [
            'erreur' => $erreur,
        ]);
    }

    #[Route('/sauvecarte', name: 'panier_sauve_carte', methods: 'POST')]
    public function sauveCarte() {

        $session = $this->requestStack->getSession();
        $customer = $session->get("stripe_customer");
        //$user = $this->getUser();
        $user = $this->getUtilisateur();
        $user->setIdStripe($customer->id);
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        \Stripe\Stripe::setApiKey(
            $this->getParameter("app.stripe_sk")
        );

        $stripe = new \Stripe\StripeClient(
            $this->getParameter("app.stripe_sk")
        );

        return new JsonResponse([]);

        /*return new JsonResponse([
            //
        ]);*/
    }

    public function panierGuestVersPanierConnecte() {
        $session = $this->requestStack->getSession();
        $user_session = $session->get("guest");
        $panier_user = $this->getPanier();

        if($user_session !== null) {
            $panier_guest = $this->paniersRepository->findOneBy(["user" => $user_session]);
            //$panier_user = $this->getPanier();

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
                //

                if($promo_guest !== null) {
                    $liaison = $this->codesPromosUsersRepository->findOneBy([
                        "user" => $this->getUser(),
                        "code_promo" => $promo_guest
                    ]);

                    if($liaison === null) { //code promo non utilise par user
                        //remarque: les conditions d'utilisation du code promo ont ete verifiees lors de sa liaison
                        //au panier guest. A ce niveau, la panier user precedent a ete vidé (si existant) et recupere
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

    private function controlePromoLiee() {
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
            } else if($this->getUser() !== null) {  //si user connecte

                $promos_users = $this->getUser()->getCodesPromos(); //recuperation des entites pivots codePromo_users lies au user

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

    private function annulePromo() {
        $panier = $this->getPanier();
        $promo = $panier->getCodePromo();
        if($promo !== null) {
            $promo->removePanier($panier);
            $this->entityManager->persist($promo);
            $this->entityManager->flush();
        }
    }

    private function setQuantitesEnSession($id_produit, $quantite) {
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
}
