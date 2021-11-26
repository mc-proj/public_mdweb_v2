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
                                MDWPaniersController $panierController,
                                PaniersService $paniersService,
                            ) {
        $this->urlGenerator = $urlGenerator;
        $this->userRepository = $userRepository;
        $this->panierController = $panierController;
        $this->paniersService = $paniersService;
    }

    public function authenticate(Request $request): PassportInterface
    {
        $email = $request->request->get('email', '');

        if(!$this->secuLoggin($email)) {
            $email = '';
        }

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
        $this->paniersService->panierGuestVersPanierConnecte();

        if ($targetPath = $this->getTargetPath($request->getSession(), $firewallName)) {
            return new RedirectResponse($targetPath);
        }

        return new RedirectResponse($this->urlGenerator->generate('accueil'));
    }

    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }

    private function secuLoggin($email) {
        $secu_ok = true;
        $user = $this->userRepository->findOneBy(['email' => $email]);

        //empeche de se connecter via un compte avec ROLE_GUEST
        if($user !== null && in_array('ROLE_GUEST', $user->getRoles())) {
            $secu_ok = false;
        } else if($user !== null && !$user->isVerified()) { //empeche la connexion avec un compte non validé
            $secu_ok = false;
        }

        return $secu_ok;
    }
}
