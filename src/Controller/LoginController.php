<?php declare(strict_types=1);

namespace App\Controller;
use App\Entity\User;
use App\Providers\SteamProvider;
use phpDocumentor\Reflection\Types\This;
use Cocur\Slugify\Slugify;
use FOS\UserBundle\Model\UserManagerInterface;
use Hybridauth\Exception\UnexpectedApiResponseException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\RedirectResponse;
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
     * @Route("/connect", name="steam_connect")
     */
    public function connect(Session $session, Request $request, AuthenticationManagerInterface $authManager, TokenStorageInterface $tokenStorage, UserManagerInterface $userManager, EventDispatcherInterface $eventDispatcher):Response
    {

        $session->start();
        $slugify = new Slugify();

        $config = [
            'callback' => $request->getUri(),
            'keys' => [ 'secret' => getenv('STEAM_API_KEY') ],
        ];

        /**
         * Step 3: Instantiate Github Adapter
         *
         * This example instantiates a GitHub adapter using the array $config we just built.
         */

        $steam = new \Hybridauth\Provider\Steam($config);

        try {
            $authenticate = $steam->authenticate();
        } catch (UnexpectedApiResponseException $exception) { }

        if($steam->isConnected()) {
            $userProfile = $steam->getUserProfile();
            $identifier = $userProfile->identifier;

            $user = $userManager->findUserByConfirmationToken($userProfile->identifier);
            if(!$user) {
                $user = $userManager->createUser();
                $user->setConfirmationToken($userProfile->identifier);
                $user->setUsername($userProfile->displayName);
                $user->setEmail($slugify->slugify($userProfile->displayName).'@cardinalguild.com');
                $user->setPlainPassword($slugify->slugify($userProfile->displayName));
                $user->setSteamData(serialize($userProfile));
                $user->setRoles(['ROLE_USER','ROLE_SURVEYOR']);
                $user->setEnabled(true);
            }
            $userManager->updateUser($user);

            $unauthToken = new UsernamePasswordToken($user, $user->getPassword(), $this->providerKey, $user->getRoles());
            $authToken = $authManager->authenticate($unauthToken);
            $tokenStorage->setToken($authToken);

            $session->set('_security_'.$this->providerKey, serialize($authToken));
            $session->save();

            /** Fire the login event manually */
            $event = new InteractiveLoginEvent($request, $unauthToken);
            $eventDispatcher->dispatch('security.interactive_login', $event);

            $response = new RedirectResponse($this->generateUrl('sonata_admin_dashboard'));
//            $response->headers->setCookie(new Cookie('_security.'.$this->providerKey, serialize($authToken)));
            return $response;
        }
        //return new RedirectResponse($this->generateUrl('steam_login'));
    }
}
