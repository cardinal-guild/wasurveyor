<?php

namespace App\Admin;

use App\Form\Type\LeafletType;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;

class IslandAdmin extends AbstractAdmin
{
    /**
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('id')
            ->add('name')
            ->add('nickname')
            ->add('lat')
            ->add('lng')
            ->add('revivors')
            ->add('cannons')
            ->add('dangerous')
            ->add('createdAt')
            ->add('updatedAt')
        ;
    }

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('id')
            ->add('name', null, ['editable'=>true])
            ->add('nickname')
            ->add('slug')
            ->add('lat', null, ['editable'=>true])
            ->add('lng', null, ['editable'=>true])
            ->add('revivors', null, ['editable'=>true])
            ->add('cannons', null, ['editable'=>true])
            ->add('dangerous', null, ['editable'=>true])
            ->add('createdAt')
            ->add('updatedAt')
            ->add('_action', null, array(
                'actions' => array(
                    'show' => array(),
                    'edit' => array(),
                    'delete' => array(),
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
            ->with('Coordinates', ['class'=>'col-md-6'])
            ->add('leaflet', LeafletType::class, ['required'=>false,'label'=>false,'help'=>'Click on the map to get lat and lng numbers'])
            ->add('lat')
            ->add('lng')
            ->end()
            ->with('Name and nickname', ['class'=>'col-md-6'])
                ->add('name')
                ->add('nickname')
            ->end()
            ->with('Options', ['class'=>'col-md-6'])
            ->add('revivors', null, ['help'=>'Does this island have revivors?'])
            ->add('cannons', null, ['help'=>'Does this island have auto turrets/swivel guns?'])
            ->add('dangerous', null, ['help'=>'Is the island dangerous to land on? (Cannons on top, swivels on top)'])
            ->end()
        ;
    }

    /**
     * @param ShowMapper $showMapper
     */
    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->add('id')
            ->add('name')
            ->add('nickname')
            ->add('slug')
            ->add('lat')
            ->add('lng')
            ->add('revivors')
            ->add('cannons')
            ->add('dangerous')
            ->add('createdAt')
            ->add('updatedAt')
        ;
    }
}
