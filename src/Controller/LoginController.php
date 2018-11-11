<?php declare(strict_types=1);

namespace App\Controller;
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
}
