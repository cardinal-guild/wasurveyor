<?php

namespace App\Admin;

use App\Entity\Island;
use App\Form\Type\LeafletType;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\AdminType;
use Sonata\AdminBundle\Form\Type\ModelAutocompleteType;
use Sonata\AdminBundle\Form\Type\ModelListType;
use Sonata\AdminBundle\Form\Type\ModelType;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\CoreBundle\Form\Type\BooleanType;
use Sonata\CoreBundle\Form\Type\CollectionType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\RangeType;

class IslandAdmin extends AbstractAdmin
{
    protected $baseRouteName = 'cg_island_admin';
    protected $baseRoutePattern = 'islands';

    protected $datagridValues = array(

        // display the first page (default = 1)
        '_page' => 1,

        // reverse order (default = 'ASC')
        '_sort_order' => 'DESC',

        // name of the ordered field (default = the model's id field, if any)
        '_sort_by' => 'id',
    );
    public function configure() {
        $this->setTemplate('edit', 'admin/CRUD/edit_island.html.twig');
    }
    /**
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('id')
            ->add('guid')
            ->add('name')
            ->add('nickname')
            ->add('lat')
            ->add('lng')
            ->add('altitude')
            ->add('creator')
            ->add('databanks')
            ->add('tier')
            ->add('revivalChambers')
            ->add('turrets')
            ->add('dangerous')
            ->add('surveyCreatedBy')
            ->add('surveyUpdatedBy')
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
//            ->add('altitude', null, ['editable'=>true])
            ->add('databanks', null, ['editable'=>true])
            ->add('type', 'choice', ['choices'=>[0=>'Saborian',1=>'Kioki'],'editable'=>true])
            ->add('revivalChambers', null, ['editable'=>true])
            ->add('dangerous', null, ['editable'=>true])
            ->add('turrets', null, ['editable'=>true])
            ->add('tier', null, ['editable'=>true])
//            ->add('surveyUpdatedBy', null, ['label'=>"Survey by"])
            ->add('published', null, ['editable'=>true])
            ->add('createdAt')
            ->add('updatedAt')
            ->add('guid', null, ['editable'=>true, 'label'=>'Workshop ID'])
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
//                ->with('Coordinates', ['class'=>'col-sm-12 col-md-8'])
//                    ->add('leaflet', LeafletType::class, ['required'=>false,'label'=>false,'help'=>'Click on the map to get lat and lng numbers'])
//                    ->add('lat')
//                    ->add('lng')
//                ->end()
                ->with('Island details', ['class'=>'col-sm-12 col-md-6'])
                    ->add('name')
                    ->add('nickname')
                    ->add('databanks', IntegerType::class, ['attr' => ['min' => 0,'max' => 5]])
//                    ->add('altitude')
                    ->add('tier', IntegerType::class, ['attr' => ['min' => 1,'max' => 4]])
                    ->add('type', ChoiceType::class, ['choices'=>['Saborian'=>0,'Kioki'=>1]])
                    ->add('workshopUrl')
                    ->add('creator', ModelType::class, ['property'=>'name','btn_add'=>'Add new island creator', 'btn_catalogue'=>true,'help'=>'Please select a creator from the list or create a new one'])
                ->end()
                ->with('Options', ['class'=>'col-sm-12 col-md-6'])
                    ->add('guid')
                    ->add('revivalChambers', null, ['help'=>'Does this island have revival chambers?'])
                    ->add('turrets', null, ['help'=>'Does this island have auto turrets/swivel guns?'])
                    ->add('dangerous', null, ['help'=>'Is the island dangerous to land on? (Cannons on top, swivels on top)'])
                    ->add('published')
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
            ->tab('Trees')
                ->with('Tree list', array('class' => 'col-md-12'))
                    ->add('trees', CollectionType::class, array(
                        'label' => false,
                        'by_reference' => false,
                        'help'=> 'Tree types that can be found on the island'
                    ), array(
                        'edit'              => 'inline',
                        'inline'            => 'table',
                        'admin_code'        => 'admin.island_trees',
                    ))
                ->end()

            ->end()
            ->tab('Metals (PVE and PVP)')
                ->with('PVE Metal list', array('class' => 'col-md-6', 'help'=> 'These are the PVE metals and quality that can be found on this island'))
                    ->add('pveMetals', CollectionType::class, array(
                        'label' => false,
                        'by_reference' => false
                    ), array(
                        'edit'              => 'inline',
                        'inline'            => 'table',
                        'admin_code'        => 'admin.island_pve_metals',
                    ))
                ->end()
                ->with('PVP Metal list', array('class' => 'col-md-6', 'help'=> 'These are the PVP metals and quality that can be found on this island'))
                    ->add('pvpMetals', CollectionType::class, array(
                        'label' => false,
                        'by_reference' => false
                    ), array(
                        'edit'              => 'inline',
                        'inline'            => 'table',
                        'admin_code'        => 'admin.island_pvp_metals',
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
            ->add('guid', null, ['label' => 'Workshop ID'])
            ->add('name')
            ->add('nickname')
            ->add('slug')
            ->add('lat')
            ->add('lng')
            ->add('databanks')
            ->add('revivalChambers')
            ->add('turrets')
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
     * @param $island Island
     */
    public function preUpdate($island)
    {
        $user = $this->getUser();
        if($user) {
            if (!$island->getSurveyCreatedBy()) {
                $island->setSurveyCreatedBy($user);
            }
            $island->setUpdatedAt(new \DateTime());
            $island->setSurveyUpdatedBy($user);
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
