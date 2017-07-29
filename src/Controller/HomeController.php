<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

Class HomeController extends Controller
{
    public function welcome()
    {
        return $this->render('home/welcome.html.twig');
    }
}
