<?php declare(strict_types=1);

namespace App\Controller;
use App\Entity\User;
use phpDocumentor\Reflection\Types\This;
use App\Repository\UserRepository;
use Azine\HybridAuthBundle\Services\AzineHybridAuth;
use Cocur\Slugify\Slugify;
use FOS\UserBundle\Model\UserManagerInterface;
use Hybridauth\Exception\UnexpectedApiResponseException;
use Hybridauth\Hybridauth;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use App\Providers\Steam as SteamProvider;

Class LoginController extends Controller
{


    /**
     * @var string Uniquely identifies the secured area
     */
    protected $providerKey = 'admin';


    /**
     * @var \Hybrid_Providers_Google
     */
    protected $googleProvider;


    /**
     * @return Response
     * @Route("/login", name="login")
     */
    public function login(Session $session, Request $request, AuthenticationManagerInterface $authManager, TokenStorageInterface $tokenStorage, UserManagerInterface $userManager): Response
    {
        if($this->container->get('kernel')->getEnvironment() == 'dev') {
            /**
             * @var $userRepo UserRepository
             */
            $userRepo = $this->getDoctrine()->getRepository('App:User');
            /**
             * @var $user User
             */
            $user = $userRepo->findOneByRole('ROLE_SUPER_ADMIN');
            $userManager->updateUser($user);

            $unauthToken = new UsernamePasswordToken($user, $user->getPassword(), $this->providerKey, $user->getRoles());
            $authToken = $authManager->authenticate($unauthToken);
            $tokenStorage->setToken($authToken);

            return new RedirectResponse($this->generateUrl('sonata_admin_dashboard'));
        }


        return $this->render(
            'login.html.twig'
        );
    }

    /**
     * @return Response
     * @Route("/logout", name="sonata_user_admin_security_logout")
     * @Route("/logout", name="steam_logout")
     */
    public function logout(RequestStack $requestStack, AuthenticationManagerInterface $authManager, TokenStorageInterface $tokenStorage, UserManagerInterface $userManager, EventDispatcherInterface $eventDispatcher):Response
    {
        $request = $requestStack->getCurrentRequest();
        $session = $request->getSession(); ;
        try {
            $cookieName = $this->getAzineHybridAuthService()->getCookieName('steam');
            $providerAdapter = $this->getAzineHybridAuthService()->getProvider($request->cookies->get($cookieName), 'steam');
            $providerAdapter->clearTokens();
            $providerAdapter->setUserUnconnected();
        } catch (\Exception $e) {}
        $session->clear();
        return new RedirectResponse($this->generateUrl('login'));
    }

    /**
     * @return Response
     * @Route("/callback/steam", name="steam_callback")
     */
    public function callback(
        RequestStack $requestStack,
        AuthenticationManagerInterface $authManager,
        TokenStorageInterface $tokenStorage,
        UserManagerInterface $userManager
    ):Response
    {
        $request = $requestStack->getCurrentRequest();

        $cookieName = $this->getAzineHybridAuthService()->getCookieName('steam');

        try {
            $providerAdapter = $this->getAzineHybridAuthService()->getProvider($request->cookies->get($cookieName), 'steam');
        } catch (\Exception $e) {
            return new RedirectResponse($this->generateUrl('login'));
        }

        $slugify = new Slugify();

        $userProfile = $providerAdapter->getUserProfile();
        if (empty($userProfile->displayName)) {
            return new RedirectResponse($this->generateUrl('login'));
        }

        $identifier = base64_encode(md5($userProfile->identifier));
        $password = $slugify->slugify($userProfile->displayName);
        $user = $userManager->findUserByConfirmationToken($identifier);
        if (!$user) {
            $user = $userManager->createUser();
            $user->setConfirmationToken($identifier);
            $user->setUsername($userProfile->displayName);
            $user->setFirstname($userProfile->firstName);
            $user->setLastname($userProfile->lastName);
            $user->setEmail($slugify->slugify($userProfile->displayName) . '@cardinalguild.com');
            $user->setPlainPassword($password);
            $user->setSteamData(serialize($userProfile));
            $user->addRole('ROLE_USER');
            $user->setEnabled(true);
        }
        $userManager->updateUser($user);

        $unauthToken = new UsernamePasswordToken($user, $password, $this->providerKey, $user->getRoles());
        $authToken = $authManager->authenticate($unauthToken);
        $tokenStorage->setToken($authToken);
        return new RedirectResponse($this->generateUrl('sonata_admin_dashboard'));
    }

    /**
     * @return AzineHybridAuth
     */
    private function getAzineHybridAuthService()
    {
        return $this->get('azine_hybrid_auth_service');
    }
}
