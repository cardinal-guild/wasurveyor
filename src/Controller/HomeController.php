<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

Class HomeController extends Controller
{
    public function welcome(Request $request)
    {
        $session = $request->getSession();
        $number = $session->get('random_number', null);

        if ($number === null) {
            $number = random_int(0, PHP_INT_MAX);
            $session->set('random_number', $number);
        }

        return $this->render('home/welcome.html.twig', [
            'hostname'      => $_SERVER['HOSTNAME'],
            'random_number' => $number
        ]);
    }
}
