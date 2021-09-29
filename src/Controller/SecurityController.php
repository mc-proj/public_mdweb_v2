<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use App\Entity\MDWUsers;
use DateTime;

class SecurityController extends AbstractController
{
    private $entityManager;

    public function __construct(SessionInterface $session,
                                EntityManagerInterface $entityManager) {
        $this->session = $session;
        $this->entityManager = $entityManager;
    }
    /**
     * @Route("/connexion", name="app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // if ($this->getUser()) {
        //     return $this->redirectToRoute('target_path');
        // }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    /**
     * @Route("/deconnexion", name="app_logout")
     */
    public function logout()
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    public function guestCreator() {
        $now = new DateTime();
        $guest = new MDWUsers();
        $guest->setEmail($now->getTimestamp()."@guest.fr");
        $guest->setRoles(["ROLE_GUEST"]);
        $guest->setPassword("");
        $guest->setIsVerified(true);
        $guest->setNom("");
        $guest->setPrenom("");
        $guest->setAdresse("");
        $guest->setCodePostal("");
        $guest->setVille("");
        $guest->setTelephone("");
        $guest->setPays("");
        $guest->setDateCreation($now);
        $guest->setDateModification($now);
        $this->entityManager->persist($guest);
        $this->entityManger->flush();
        $this->session->set("guest", $guest);
        return $guest;
    }
}
