<?php declare(strict_types=1);

namespace App\Controller;
use App\Providers\SteamProvider;
use phpDocumentor\Reflection\Types\This;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

Class LoginController extends Controller
{
    /**
     * @return Response
     * @Route("/login", name="steam_login")
     */
    public function login(): Response
    {
        return $this->render(
            'login.html.twig'
        );
    }
    /**
     * @return Response
     * @Route("/connect", name="steam_connect")
     */
    public function connect(Request $request):Response
    {
        return new Response("Login system is being worked on");
    }
}
