<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\PassportInterface;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

use App\Repository\MDWUsersRepository;
//use App\Repository\MDWPaniersRepository;
//use App\Repository\MDWPaniersProduitsRepository;
use App\Controller\MDWPaniersController;

use App\Services\PaniersService;

class LoginAuthenticator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait;

    public const LOGIN_ROUTE = 'app_login';

    private UrlGeneratorInterface $urlGenerator;
    private MDWPaniersController $panierController;
    private $userRepository;
    private $panierRepository;
    private $panierProduitRepository;

    private $paniersService;

    public function __construct(UrlGeneratorInterface $urlGenerator,
                                MDWUsersRepository $userRepository,
                                //MDWPaniersRepository $panierRepository,
                                MDWPaniersController $panierController,
                                PaniersService $paniersService,
                                /*MDWPaniersProduitsRepository $panierProduitRepository*/)
    {
        $this->urlGenerator = $urlGenerator;
        $this->userRepository = $userRepository;
        //$this->panierRepository = $panierRepository;
        //$this->panierProduitRepository = $panierProduitRepository;
        $this->panierController = $panierController;

        $this->paniersService = $paniersService;
    }

    public function authenticate(Request $request): PassportInterface
    {
        $email = $request->request->get('email', '');

        //secu maison
        //cas visiteur -> creation d'un user en bdd avec ROLE_GUEST
        //secu pour empecher petit malin de bricoler un form pr se connecter via un compte guest
        //if($this->checkIsGuest($request->request->get('email'))) {
        if($this->checkIsGuest($email)) {
            return new RedirectResponse($this->urlGenerator->generate('accueil'));
        }
        //

        $request->getSession()->set(Security::LAST_USERNAME, $email);

        return new Passport(
            new UserBadge($email),
            new PasswordCredentials($request->request->get('password', '')),
            [
                new CsrfTokenBadge('authenticate', $request->get('_csrf_token')),
            ]
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        //dd($request);
        //test zone begin
        //$this->panierController->panierGuestVersPanierConnecte();
        $this->paniersService->panierGuestVersPanierConnecte();
        //test zone end

        if ($targetPath = $this->getTargetPath($request->getSession(), $firewallName)) {
            return new RedirectResponse($targetPath);
        }

        return new RedirectResponse($this->urlGenerator->generate('accueil'));
    }

    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }

    private function checkIsGuest($email) {
        $user = $this->userRepository->findOneBy(['email' => $email]);

        if($user !== null && in_array('ROLE_GUEST', $user->getRoles())) {
            return true;
        } else {
            return false;
        }
    }
}
