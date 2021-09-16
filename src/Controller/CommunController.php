<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\ContactFormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\MailerInterface;
use App\Repository\MDWCategoriesRepository;
use App\Repository\MDWProduitsRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;

#[Route('/commun')]

class CommunController extends AbstractController
{
    private $categoriesRepository;
    private $produitsRepository;
    private $requestStack;

    public function __construct(MDWCategoriesRepository $categoriesRepository,
                                MDWProduitsRepository $produitsRepository,
                                RequestStack $requestStack) {
        $this->categoriesRepository = $categoriesRepository;
        $this->produitsRepository = $produitsRepository;
        $this->requestStack = $requestStack;
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

        /*$resultats = $this->produitsRepository->findByBegin($debut);
        dd($resultats);*/

        if($debut == '') {

            $resultats = [];
        }

        else {

            $resultats = $this->produitsRepository->findByBegin($debut);

            if($resultats == []) {

                $resultats = $this->categoriesRepository->findByBegin($debut);
            }
        }

        $resultats = json_encode($resultats);
        $response = new JsonResponse($resultats);
        return $response;
    }

    //
}
