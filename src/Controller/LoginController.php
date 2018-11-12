<?php declare(strict_types=1);

namespace App\Controller;
use App\Entity\User;
use phpDocumentor\Reflection\Types\This;
use Cocur\Slugify\Slugify;
use FOS\UserBundle\Model\UserManagerInterface;
use Hybridauth\Exception\UnexpectedApiResponseException;
use Hybridauth\Hybridauth;
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
use \Hybridauth\Provider\Steam as SteamProvider;

Class LoginController extends Controller
{


    /**
     * @var string Uniquely identifies the secured area
     */
    private $providerKey = 'admin';

    /**
     * @return Response
     * @Route("/login", name="steam_login")
     */
    public function login(Session $session, Request $request): Response
    {
        return $this->render(
            'login.html.twig'
        );
    }
    /**
     * @return Response
     * @Route("/logout", name="steam_logout")
     */
    public function logout(RequestStack $requestStack, AuthenticationManagerInterface $authManager, TokenStorageInterface $tokenStorage, UserManagerInterface $userManager, EventDispatcherInterface $eventDispatcher):Response
    {
        $request = $requestStack->getCurrentRequest();
        $session = $request->getSession();
//        $steam->disconnect();

        $config = [
            'callback' => getenv('STEAM_BASE_URL').$request->getRequestUri(),
            'keys' => [ 'secret' => getenv('STEAM_API_KEY') ],
        ];
        $steam = new SteamProvider($config);
        $steam->disconnect();
        $steam->storage->clear();
        $session->clear();
        return new RedirectResponse($this->generateUrl('steam_login'));
    }

    /**
     * @return Response
     * @Route("/connect", name="steam_connect")
     */
    public function connect(RequestStack $requestStack, AuthenticationManagerInterface $authManager, TokenStorageInterface $tokenStorage, UserManagerInterface $userManager, EventDispatcherInterface $eventDispatcher):Response
    {
        $request = $requestStack->getCurrentRequest();
        $session = $request->getSession();
        $slugify = new Slugify();

        $config = [
            'callback' => getenv('STEAM_BASE_URL').$request->getRequestUri(),
            'keys' => [ 'secret' => getenv('STEAM_API_KEY') ],
        ];

        /**
         * Step 3: Instantiate Github Adapter
         *
         * This example instantiates a GitHub adapter using the array $config we just built.
         */

        $steam = new SteamProvider($config);
//        $steam->disconnect();
//        exit();
        $authenticated = $steam->authenticate();


        if($authenticated && $steam->getUserProfile() && !empty($steam->getUserProfile()->displayName)) {
            $userProfile = $steam->getUserProfile();

            $identifier = $userProfile->identifier;

            $user = $userManager->findUserByConfirmationToken(base64_encode(md5($userProfile->identifier)));
            if(!$user) {
                $user = $userManager->createUser();
                $user->setConfirmationToken($userProfile->identifier);
                $user->setUsername($userProfile->displayName);
                $user->setFirstname($userProfile->firstName);
                $user->setLastname($userProfile->lastName);
                $user->setEmail($slugify->slugify($userProfile->displayName).'@cardinalguild.com');
                $user->setPlainPassword($slugify->slugify($userProfile->displayName));
                $user->setSteamData(serialize($userProfile));
                $user->setRoles(['ROLE_USER']);
                $user->setEnabled(true);
            }
            $userManager->updateUser($user);

            $unauthToken = new UsernamePasswordToken($user, null, $this->providerKey, $user->getRoles());
            $authToken = $authManager->authenticate($unauthToken);
            $tokenStorage->setToken($authToken);


            return new RedirectResponse($this->generateUrl('sonata_admin_dashboard'));
        }
        $session->getFlashBag()->add('error', 'Could not login due to an error');
        return new RedirectResponse($this->generateUrl('steam_login'));
    }
}
