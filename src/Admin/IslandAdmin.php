<?php

namespace App\Admin;

use App\Entity\Island;
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
            ->addIdentifier('mainImage', null , array('template' => 'admin/list_image.html.twig', 'label'=>'Image'))
            ->addIdentifier('name')
            ->add('respawners', null, ['editable'=>true])
            ->add('cannons', null, ['editable'=>true])
            ->add('dangerous', null, ['editable'=>true])
            ->add('turrets', null, ['editable'=>true])
            ->add('surveyUpdatedBy', null, ['label'=>"Survey by"])
            ->add('published')
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
                    ->add('creator', ModelListType::class, array(),array('sd'=>false))
                ->end()
                ->with('Options', ['class'=>'col-sm-12 col-md-4'])
                    ->add('respawners', null, ['help'=>'Does this island have respawners?'])
                    ->add('cannons', null, ['help'=>'Does this island have auto turrets/swivel guns?'])
                    ->add('dangerous', null, ['help'=>'Is the island dangerous to land on? (Cannons on top, swivels on top)'])
                    ->add('turrets', null, ['help'=>'Has this island turrets?'])
                    ->add('spikes', null, ['help'=>'Are there spikes on this island?'])
                    ->add('nonGrappleWalls', null, ['help'=>'Are there non grappling walls on this island?'])
                ->end()
            ->end()
            ->tab('Media')
                ->with('Island image list', array('class' => 'col-md-12', 'help'=> 'The top image will be used as main image'))
                    ->add('images', CollectionType::class, array(
                        'label' => false,
                        'by_reference' => false,
                        'help'=> 'The top image will be used as main image'
                    ), array(
                        'edit'              => 'inline',
                        'inline'            => 'table',
                        'sortable'          => 'position',
                        'admin_code'        => 'admin.island_image',
                    ))
                ->end()
            ->end()
            ->tab('PVE Materials')
                ->with('PVE Tree list', array('class' => 'col-md-6', 'help'=> 'These are the PVE trees and quality that can be found on this island'))
                    ->add('pveTrees', CollectionType::class, array(
                        'label' => false,
                        'by_reference' => false
                    ), array(
                        'edit'              => 'inline',
                        'inline'            => 'table',
                        'admin_code'        => 'admin.island_tree',
                    ))
                ->end()

                ->with('PVE Metal list', array('class' => 'col-md-6', 'help'=> 'These are the PVE metals and quality that can be found on this island'))
                    ->add('pveMetals', CollectionType::class, array(
                        'label' => false,
                        'by_reference' => false
                    ), array(
                        'edit'              => 'inline',
                        'inline'            => 'table',
                        'admin_code'        => 'admin.island_metal',
                    ))
                ->end()
            ->end()
            ->tab('PVP Materials')
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

    public function getNewInstance()
    {
        $island = parent::getNewInstance();
        $user = $this->getUser();
        if ($user) {
            if (!$island->getSurveyCreatedBy()) {
                $island->setSurveyCreatedBy($user);
            }
            $island->setSurveyUpdatedBy($user);
        }
        return $island;
    }

    /**
     * @param $object Island
     */
    public function prePersist($object)
    {
        parent::preUpdate($object); // TODO: Change the autogenerated stub
    }


    /**
     * @param $object Island
     */
    public function preUpdate($object)
    {
        $user = $this->getUser();
        if($user) {
            if (!$object->getSurveyCreatedBy()) {
                $object->setSurveyCreatedBy($user);
            }
            $object->setSurveyUpdatedBy($user);
        }
    }

    protected function getUser()
    {
        $container = $this->getConfigurationPool()->getContainer();
        if (!$container->has('security.token_storage')) {
            throw new \LogicException('The SecurityBundle is not registered in your application. Try running "composer require symfony/security-bundle".');
        }

        if (null === $token = $container->get('security.token_storage')->getToken()) {
            return;
        }

        if (!\is_object($user = $token->getUser())) {
            // e.g. anonymous authentication
            return;
        }

        return $user;
    }
}
