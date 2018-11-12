<?php declare(strict_types=1);

namespace App\Controller;
use App\Entity\User;
use phpDocumentor\Reflection\Types\This;
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
use \Hybridauth\Provider\Steam as SteamProvider;

Class LoginController extends Controller
{



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
}
