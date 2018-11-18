<?php


namespace App\Controller;


use Sonata\AdminBundle\Controller\CRUDController;

class IslandRepositionAdminController extends CRUDController
{

    public function listAction()
    {
        return $this->renderWithExtraParams('admin/islandreposition.html.twig');
    }
}
