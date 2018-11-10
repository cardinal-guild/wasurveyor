<?php

namespace App\Admin;

use App\Form\Type\LeafletType;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelListType;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\CoreBundle\Form\Type\CollectionType;

class IslandAdmin extends AbstractAdmin
{
    protected $baseRouteName = 'cg_island_admin';
    protected $baseRoutePattern = 'islands';

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
            ->add('respawners')
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
            ->addIdentifier('id')
            ->addIdentifier('name')
            ->addIdentifier('nickname')
            ->addIdentifier('slug')
            ->add('lat', null, ['editable'=>true])
            ->add('lng', null, ['editable'=>true])
            ->add('respawners', null, ['editable'=>true])
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
            ->tab('Main info and coordinates')
                ->with('Coordinates', ['class'=>'col-sm-12 col-md-8'])
                    ->add('leaflet', LeafletType::class, ['required'=>false,'label'=>false,'help'=>'Click on the map to get lat and lng numbers'])
                    ->add('lat')
                    ->add('lng')
                ->end()
                ->with('Island details', ['class'=>'col-sm-12 col-md-4'])
                    ->add('name')
                    ->add('nickname')
                    ->add('workshopUrl')
                    ->add('author', ModelListType::class, array(),array('sd'=>false))
                ->end()
                ->with('Options', ['class'=>'col-sm-12 col-md-4'])
                    ->add('respawners', null, ['help'=>'Does this island have respawners?'])
                    ->add('cannons', null, ['help'=>'Does this island have auto turrets/swivel guns?'])
                    ->add('dangerous', null, ['help'=>'Is the island dangerous to land on? (Cannons on top, swivels on top)'])
                ->end()
            ->end()
            ->tab('Media')
                ->with('Island image list', array('class' => 'col-md-12'))
                    ->add('images', CollectionType::class, array(
                        'label' => false,
                        'by_reference' => false,
                    ), array(
                        'edit'              => 'inline',
                        'inline'            => 'table',
                        'sortable'          => 'position',
                        'admin_code'        => 'admin.island_image',
                    ))
                ->end()
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
            ->add('respawners')
            ->add('cannons')
            ->add('dangerous')
            ->add('createdAt')
            ->add('updatedAt')
        ;
    }
}
