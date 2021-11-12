<?php

namespace App\Controller;

use App\Entity\MDWUsers;
use App\Form\RegistrationFormType;
use App\Form\EditeCompteType;
use App\Form\EditeMdpType;
use App\Security\EmailVerifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class RegistrationController extends AbstractController
{
    private $emailVerifier;
    private $entityManager;

    public function __construct(EmailVerifier $emailVerifier,
                                EntityManagerInterface $entityManager)
    {
        $this->emailVerifier = $emailVerifier;
        $this->entityManager = $entityManager;
    }

    #[Route('/inscription', name: 'app_register')]
    public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder): Response
    {
        $user = new MDWUsers();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $passwordEncoder->encodePassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            // generate a signed url and email it to the user
            $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user,
                (new TemplatedEmail())
                    ->from(new Address('symfo.testmail@gmail.com', 'MDW mailbox'))
                    ->to($user->getEmail())
                    ->subject('Marché du web - Confirmation adresse email')
                    ->htmlTemplate('registration/confirmation_email.html.twig')
                    ->context([
                        'prenom' => $user->getPrenom()
                    ])
            );
            
            $this->addFlash('register_success', 'Votre demande de création de compte a bien été prise en compte. Nous vous avons envoyé un message afin de confirmer votre adresse email');
            return $this->redirectToRoute('app_login');
        } else if($form->isSubmitted()) {
            $erreurs = [];
            foreach($form->getErrors(true) as $error) {
                array_push($erreurs, $error->getMessage());
            }

            $this->addFlash('erreur_inscription', $erreurs);
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    #[Route('/verify/email', name: 'app_verify_email')]
    public function verifyUserEmail(Request $request): Response
    {
        $this->addFlash('register_success', 'Votre adresse email a bien été confirmée');
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // validate email confirmation link, sets User::isVerified=true and persists
        try {
            $this->emailVerifier->handleEmailConfirmation($request, $this->getUser());
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('verify_email_error', $exception->getReason());

            return $this->redirectToRoute('app_register');
        }

        return $this->redirectToRoute('app_login');
    }

    #[Route('/compte', name: 'mon_compte')]
    public function monCompte(Request $request, UserPasswordEncoderInterface $passwordEncoder): Response {

        if($this->getUser()) {
            $user = $this->getUser();
            $form_profil = $this->createForm(EditeCompteType::class, $user);
            $form_profil->handleRequest($request);
            $form_mdp = $this->createForm(EditeMdpType::class, $user);
            $form_mdp->handleRequest($request);

            if($form_profil->isSubmitted() && $form_profil->isValid()) {
                $this->entityManager->persist($user);
                $this->entityManager->flush();
            } else if($form_profil->isSubmitted()) {
                $this->addFlash('erreur_edition_profil', 'Erreur: un des champs contient une information incorrecte');
            }

            $edition_mdp = false;

            if($form_mdp->isSubmitted()) {
                $edition_mdp = true;

                if($form_mdp->isValid()) {
                    $user->setPassword(
                        $passwordEncoder->encodePassword(
                            $user,
                            $form_mdp->get('plainPassword')->getData()
                        )
                    );
        
                    $this->entityManager->persist($user);
                    $this->entityManager->flush();
                    $this->addFlash('edition_mdp', 'Votre mot de passe a bien été modifié');
                }
            }

            return $this->render('registration/compte.html.twig', [
                'formulaire_profil' => $form_profil->createView(),
                'formulaire_mdp' => $form_mdp->createView(),
                'factures' => $user->getFactures(),
                'edition_mdp' => $edition_mdp,
            ]);
        } else {
            return $this->redirectToRoute('app_login');
        } 
    }
}
