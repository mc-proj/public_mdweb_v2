<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\MDWPaniersProduits;
use App\Entity\MDWFactures;
use App\Entity\MDWFacturesProduits;
use App\Entity\MDWCodesPromosUsers;
use App\Repository\MDWProduitsRepository;
use App\Repository\MDWCodesPromosRepository;
use App\Repository\MDWCodesPromosUsersRepository;
use Symfony\Component\HttpFoundation\RequestStack;
use Doctrine\ORM\EntityManagerInterface;
use DateTime;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Stripe\Stripe;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\MailerInterface;
use App\Services\PaniersService;

#[Route('/paniers')]

class MDWPaniersController extends AbstractController
{
    private $produitsRepository;
    private $codesPromosRepository;
    private $codesPromosUsersRepository;
    private $requestStack;
    private $entityManager;
    private $paniersService;

    public function __construct(MDWProduitsRepository $produitsRepository,
                                MDWCodesPromosRepository $codesPromosRepository, 
                                MDWCodesPromosUsersRepository $codesPromosUsersRepository,
                                RequestStack $requestStack,
                                EntityManagerInterface $entityManager,
                                PaniersService $paniersService,
                                ) {
        $this->produitsRepository = $produitsRepository;
        $this->codesPromosRepository = $codesPromosRepository;
        $this->codesPromosUsersRepository = $codesPromosUsersRepository;
        $this->entityManager = $entityManager;
        $this->requestStack = $requestStack;
        $this->paniersService = $paniersService;
    }

    #[Route('/', name: 'accueil_panier')]
    public function index(): Response
    {
        $panier = $this->paniersService->getPanier();
        $modifications = $this->paniersService->controleQuantites();

        return $this->render('mdw_paniers/index.html.twig', [
            'panier' => $panier,
            'editions' => $modifications['editions'],
            'suppressions' => $modifications['suppressions'],
            'secu_promo' => $this->paniersService->controlePromoLiee(),
        ]);
    }

    #[Route('/modifie-quantite', name: 'modifie_panier', methods: 'POST')]
    public function editeQuantite(Request $request) {  
        //modifie les quantites dans un panier, pas le stock des produits (se fait à la validation du panier)
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

        if($produit !== null && gettype($quantite) === "integer") {
            $quantite_finale = 0;
            $quantite_ajout = 0; //peux avoir une valeur negative dans le cas d'un retrait
            $presence_produit = false;
            $panier = $this->paniersService->getPanier();

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
            $panier->setMontantHt($panier->getMontantHt() + $quantite_ajout * round($tarifs['ht']));
            $panier->setMontantTtc($panier->getMontantTtc() + $quantite_ajout * round($tarifs['ttc']));
            $panier->setDateModification(new DateTime());
            $this->entityManager->persist($panier);
            $this->entityManager->flush();
            $this->paniersService->setQuantitesEnSession($id_produit, $quantite_finale);

            $retour = [
                "produit_dispo_sans_stock" => $produit->getCommandableSansStock(),
                "quantite_produit_stock" => $produit->getQuantiteStock(),
                "quantite_finale_produit" => $quantite_finale,
                "nombre_articles_panier" => $nombre_articles_panier,
                "total_ht" => $panier->getMontantHt(),
                "total_ttc" => $panier->getMontantTtc(),
                "edite_supprime" => $edite_supprime,
                "infos_promo" => $this->paniersService->controlePromoLiee(),
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
        $panier = $this->paniersService->getPanier();
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
        $panier = $this->paniersService->getPanier();

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
        $user = $this->paniersService->getUtilisateur();
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
            $panier = $this->paniersService->getPanier();
            $this->paniersService->annulePromo(); //annule le code promo deja lie (s'il y en a un)
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
        $this->paniersService->annulePromo();
        return new JsonResponse();
    }

    #[Route('/paiement', name: 'panier_paiement')]
    public function panierPaiement() {
        if($this->getUser() === null) {
            return $this->redirectToRoute('app_login');
        }

        $panier = $this->paniersService->getPanier();
        
        if($panier->getProduits()->count() === 0) { //si panier vide
            return $this->redirectToRoute('accueil_panier');
        }

        $user = $this->paniersService->getUtilisateur();
        $cartes = [];
        $methodes_de_paiement = null;
        $session = $this->requestStack->getSession();
        
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
        } else {
            $customer = \Stripe\Customer::create();
        }

        $session->set("stripe_customer", $customer);
        $code_promo = $panier->getCodePromo();
        $total = $panier->getMontantTtc();

        if($code_promo != null) {
            $reduction = $code_promo->getValeur();

            if($code_promo->getTypePromo() === "proportionnelle") {
                $reduction = $panier->getMontantTtc() * ($reduction/10000);
            }

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
            } catch (\Stripe\Exception\CardException $e) {
                // Error code will be authentication_required if authentication is needed
                echo 'Error code is:' . $e->getError()->code;
                $payment_intent_id = $e->getError()->payment_intent->id;
                $intent = \Stripe\PaymentIntent::retrieve($payment_intent_id);
            }
        } else { //l'utilisateur n'a aucune carte enregistree
            $intent =  $stripe->paymentIntents->create([
                'amount' => $panier->getMontantTtc(),
                'currency' => 'eur',
                'customer' => $customer->id,
                'payment_method_types' => ['card'],
                'description' => 'ACHAT CB MARCHE DU WEB ' . $date,
                'receipt_email' => $user->getEmail(),
            ]);
        }

        $session->set("payment_intent", $intent);          

        return $this->render("mdw_paniers/paiement.html.twig", [

            'user' => $user,
            "panier" => $panier,
            "code_promo" => $code_promo,
            "cartes" => $cartes
        ]);
    }

    #[Route('/paiement_post', name: 'panier_paiement_post', methods: 'POST')]
    public function postPaiement(Request $request) {
        \Stripe\Stripe::setApiKey(
            $this->getParameter("app.stripe_sk")
        );

        $conditions_lues = true;

        if($request->request->get("conditions_lues") === "false") {
            $conditions_lues = false;
        }

        if($request->request->get("adresse_differente") === "false") {
            $panier = $this->paniersService->getPanier();
            $adresse = $panier->getAdresseLivraison();

            if($adresse !== null) {
                $panier->setAdresseLivraison(null);
                $this->entityManager->persist($panier);
                $this->entityManager->remove($adresse);
                $this->entityManager->flush();
            }
        }

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
    public function paiementReussi(MailerInterface $mailer) {
        $reduction = 0;
        $user = $this->paniersService->getUtilisateur();
        $panier = $this->paniersService->getPanier();

        if($panier->getProduits()->count() === 0) {
            return $this->redirectToRoute('accueil_panier');
        }

        $code_promo = $panier->getCodePromo();

        if($code_promo !== null) {
            $code_promo_user = new MDWCodesPromosUsers();
            $code_promo_user->setCodePromo($code_promo);
            $code_promo_user->setUser($user);
            $code_promo_user->setDateUtilisation(new dateTime());
            $this->entityManager->persist($code_promo_user);
            $reduction = $code_promo->getValeur();

            if($code_promo->getTypePromo() === "proportionnelle") {
                $reduction = $panier->getMontantTtc() * ($reduction/10000);
            }
        }

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
            $produit = $panier_produit->getProduit();
            $produit->setQuantiteStock($produit->getQuantiteStock() - $panier_produit->getQuantite());
            $this->entityManager->persist($produit);
            $facture_produit = new MDWFacturesProduits();
            $facture_produit->setFacture($facture);
            $facture_produit->setProduit($panier_produit->getProduit());
            $facture_produit->setQuantite($panier_produit->getQuantite());
            $this->entityManager->persist($facture_produit);
        }

        $this->entityManager->flush();
        $this->videPanier();

        $email = (new TemplatedEmail())
                ->to($user->getEmail())
                ->subject("Marché du Web: validation de votre commande")
                ->priority(Email::PRIORITY_HIGH)
                ->htmlTemplate("email/confirmation_achat.html.twig")
                    ->context([
                        'facture' => $facture,
                ]);
        $mailer->send($email);

        return $this->render('mdw_paniers/paiement_reussi.html.twig', [
            'facture' => $facture,
        ]);
    }

    #[Route('/paiement_fail', name: 'panier_paiement_fail', methods: 'POST')]
    public function paiemenentEchec(Request $request) {
        $erreur = $request->request->get("erreur");

        return $this->render('mdw_paniers/paiement_echec.html.twig', [
            'erreur' => $erreur,
        ]);
    }

    #[Route('/sauvecarte', name: 'panier_sauve_carte', methods: 'POST')]
    public function sauveCarte() {
        $session = $this->requestStack->getSession();
        $customer = $session->get("stripe_customer");
        $user = $this->paniersService->getUtilisateur();
        $user->setIdStripe($customer->id);
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        return new JsonResponse([]);
    }
}
