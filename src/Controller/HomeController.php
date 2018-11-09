<?php declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

Class HomeController extends Controller
{
    public function welcome(): Response
    {
        return new Response('Hello,  test world!');
    }
}
