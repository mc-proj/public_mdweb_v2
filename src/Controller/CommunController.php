<?php

namespace App\Controller;

use App\Entity\MDWAdressesLivraison;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\ContactFormType;
use App\Form\AdresseLivraisonType;
use App\Form\MessageLivraisonType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\MailerInterface;
use App\Repository\MDWCategoriesRepository;
use App\Repository\MDWProduitsRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Doctrine\ORM\EntityManagerInterface;
use App\Controller\MDWPaniersController;
use DateTime;

use App\Services\PaniersService;


#[Route('/commun')]

class CommunController extends AbstractController
{
    private $categoriesRepository;
    private $produitsRepository;
    private $requestStack;
    private $entityManager;
    private MDWPaniersController $panierController;
    private $paniersService;

    public function __construct(MDWCategoriesRepository $categoriesRepository,
                                MDWProduitsRepository $produitsRepository,
                                RequestStack $requestStack,
                                EntityManagerInterface $entityManager,
                                MDWPaniersController $panierController,
                                PaniersService $paniersService) {
        $this->categoriesRepository = $categoriesRepository;
        $this->produitsRepository = $produitsRepository;
        $this->requestStack = $requestStack;
        $this->entityManager = $entityManager;
        $this->panierController = $panierController;
        $this->paniersService = $paniersService;
    }


    #[Route('/cgv', name: 'cgv')]
    public function cgvMenu(): Response
    {
        return $this->render('commun/cgv.html.twig');
    }

    #[Route('/confidentialite', name: 'confidentialite')]
    public function confidentialiteMenu(): Response
    {
        return $this->render('commun/confidentialite.html.twig');
    }

    #[Route('/contact', name: 'contact')]
    public function contactMenu(Request $request, MailerInterface $mailer): Response
    {
        $form = $this->createForm(ContactFormType::class, null);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

            $data = $form->getData();

            $email = (new TemplatedEmail())
                ->from($data["email"])
                ->to($this->getParameter('admin_mail'))
                ->subject("Marché du Web: un client vous a envoyé un message")
                ->priority(Email::PRIORITY_HIGH)
                ->htmlTemplate("email/contact.html.twig")
                    ->context([
                        "prenom" => $data["prenom"],
                        "nom" => $data["nom"],
                        "adresse_mail" => $data["email"],
                        "message" => $data["message"]
                ]);

            $mailer->send($email);

            $this->addFlash(
                'confirmation_contact',
                'Votre message a bien été envoyé. Nous vous répondrons dans les plus brefs délais'
            );

            return $this->redirectToRoute('contact');
        }

        return $this->render('commun/contact.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/menu_lateral', name: 'menu_lateral')]
    public function menuLateral(): Response
    {
        return $this->render('commun/menu_lateral.html.twig', [
            'categories' => $this->categoriesRepository->findAll(),
        ]);
    }

    #[Route('/menu_navbar', name: 'menu_navbar')]
    public function menuNavbar(): Response
    {
        return $this->render('commun/menu_navbar.html.twig', [
            'categories' => $this->categoriesRepository->findAll(),
        ]);
    }


    #[Route('/cookies_acceptes', name: "cookies_acceptes", methods: "POST")]
    public function accepteCookies() {

        $session = $this->requestStack->getSession();
        $session->set('cookies_acceptes', true);
        $response = new JsonResponse("");
        return $response;
    }

    #[Route('/recherche', name: "recherche_produits", methods: "POST")]
    public function rechercheProduits(Request $request) {

        $debut = htmlspecialchars(trim($request->request->get("debut")), ENT_QUOTES, "UTF-8");

        if($debut == '') {
            $resultats = [];
        } else {
            $resultats = $this->produitsRepository->findByBegin($debut);

            if($resultats == []) {
                $resultats = $this->categoriesRepository->findByBegin($debut);
            }
        }

        $resultats = json_encode($resultats);
        $response = new JsonResponse($resultats);
        return $response;
    }

    #[Route('/adresse_livraison_custom', name: "adresse_livraison_custom")]
    public function formulaireLivraisonCustom(Request $request) {

        //$panier = $this->panierController->getPanier();
        $panier = $this->paniersService->getPanier();
        $adresse = $panier->getAdresseLivraison();

        $form = $this->createForm(AdresseLivraisonType::class, $adresse, [
            'action' => $this->generateUrl('adresse_livraison_custom') //par defaut, route utilisee est celle de la page qui fait l'include
        ]);
        $form->handleRequest($request);

        if($form->isSubmitted()) {
            if($form->isValid()) {
                if($adresse === null) {
                    $adresse = new MDWAdressesLivraison();
                }

                $adresse->setNom($form->get('nom')->getData());
                $adresse->setPrenom($form->get('prenom')->getData());
                $adresse->setAdresse($form->get('adresse')->getData());
                $adresse->setVille($form->get('ville')->getData());
                $adresse->setCodePostal($form->get('code_postal')->getData());
                $adresse->setPays($form->get('Pays')->getData());
                $adresse->setTelephone($form->get('telephone')->getData());
                $adresse->setActif(true);
                $adresse->setDerniereModification(new DateTime());
                $this->entityManager->persist($adresse);
                $this->entityManager->flush(); //on ne peux pas associer une entite qui n'a pas encore d'id
                $panier->setAdresseLivraison($adresse);
                $this->entityManager->persist($panier);
                $this->entityManager->flush();
                return new JsonResponse(null);
            }

            $response = new JsonResponse([
                'output' => $this->renderView('form/adresse_livraison_custom.html.twig', [
                    'form' => $form->createView(),
                ])
            ]
            , 200);
           
            return $response;
        }

        return $this->render('form/adresse_livraison_custom.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/message_livraison', name: "message_livraison")]
    public function formulaireMessageLivraison(Request $request) {
        //$panier = $this->panierController->getPanier();
        $panier = $this->paniersService->getPanier();
        $form = $this->createForm(MessageLivraisonType::class, $panier, [
            'action' => $this->generateUrl('message_livraison')
        ]);
        $form->handleRequest($request);

        if($form->isSubmitted()) {
            if($form->isValid()) {
                $panier->setMessage($form->get('message')->getData());
                $this->entityManager->persist($panier);
                $this->entityManager->flush();
                return new JsonResponse(null);
            }

            $response = new JsonResponse([
                'output' => $this->renderView('form/message_livraison.html.twig', [
                    'form' => $form->createView(),
                ])
            ]
            , 200);
           
            return $response;
        }

        return $this->render('form/message_livraison.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
