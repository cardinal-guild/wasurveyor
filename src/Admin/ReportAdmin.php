<?php

namespace App\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelAutocompleteType;
use Sonata\AdminBundle\Form\Type\ModelListType;
use Sonata\AdminBundle\Form\Type\ModelType;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

class ReportAdmin extends AbstractAdmin
{

    protected $baseRouteName = 'cg_report_admin';
    protected $baseRoutePattern = 'report';

    protected $datagridValues = array(

        // display the first page (default = 1)
        '_page' => 1,

        // reverse order (default = 'ASC')
        '_sort_order' => 'DESC',

        // name of the ordered field (default = the model's id field, if any)
        '_sort_by' => 'id',
    );

    /**
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('id')
            ->add('island')
            ->add('metals')
            ->add('createdAt')
            ->add('updatedAt')
        ;
    }
    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->remove('create');
        $collection->remove('edit');
        $collection->remove('delete');

        $collection->add('approve', $this->getRouterIdParameter().'/approve');
        $collection->add('approveOverride', $this->getRouterIdParameter().'/approve-override');
        $collection->add('disapprove', $this->getRouterIdParameter().'/disapprove');
    }
    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('id')
            ->add('island')
            ->add('metals', null, ['label'=>'Reported metals', 'template' => 'admin/list_reported_metals.html.twig'])
            ->add('existingMetals', null, ['label'=>'Current metals', 'template' => 'admin/list_existing_metals.html.twig'])
            ->add('mode', null, ['label'=>'Map Mode', 'template' => 'admin/list_report_mode.html.twig'])
            ->add('island.tier')
            ->add('createdAt')
            ->add('_action', null, array(
                'actions' => array(
                    'approve' => ['template'=>'admin/list_report_approve_action.html.twig'],
                    'approveOverride' => ['template'=>'admin/list_report_approve_override_action.html.twig'],
                    'disapprove' => ['template'=>'admin/list_report_disapprove_action.html.twig'],
                )
            ))
        ;
    }

    /**
     * @param FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('metals')
        ;
    }

    /**
     * @param ShowMapper $showMapper
     */
    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->add('id')
            ->add('island')
            ->add('metals')
            ->add('createdAt')
            ->add('updatedAt')
        ;
    }
}
