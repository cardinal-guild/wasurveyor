<?php


namespace App\Admin;


use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Route\RouteCollection;

class IslandRepositionAdmin extends AbstractAdmin
{
    protected $baseRoutePattern = 'reposition';
    protected $baseRouteName = 'reposition';

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->clearExcept(['list']);
    }

}
