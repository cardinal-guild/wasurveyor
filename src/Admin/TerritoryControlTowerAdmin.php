<?php

namespace App\Admin;

use App\Entity\Island;
use App\Form\Type\LeafletType;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelAutocompleteType;
use Sonata\AdminBundle\Form\Type\ModelListType;
use Sonata\AdminBundle\Form\Type\ModelType;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\CoreBundle\Form\Type\BooleanType;
use Sonata\CoreBundle\Form\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\RangeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class TerritoryControlTowerAdmin extends AbstractAdmin
{

	protected $baseRouteName = 'cg_tower_admin';
	protected $baseRoutePattern = 'tower';
	/**
	 * @param DatagridMapper $datagridMapper
	 */
	protected function configureDatagridFilters(DatagridMapper $datagridMapper)
	{
		$datagridMapper
			->add('id')
			->add('guid')
			->add('name')
			->add('alliance')
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
			->addIdentifier('guid')
			->addIdentifier('name')
			->add('alliance')
			->add('island')
			->add('mode')
			->add('createdAt')
			->add('updatedAt')
		;
	}

	/**
	 * @param FormMapper $formMapper
	 */
	protected function configureFormFields(FormMapper $formMapper)
	{
		$formMapper
			->add('name')
			->add('guid')
			->add('alliance', ModelType::class, ['property'=>'name','btn_add'=>'Add new alliance', 'btn_catalogue'=>true,'help'=>'Please select an alliance from the list or create a new one'])
		;
	}

	/**
	 * @param ShowMapper $showMapper
	 */
	protected function configureShowFields(ShowMapper $showMapper)
	{
		$showMapper
			->add('id')
			->add('guid')
			->add('name')
			->add('mode')
			->add('createdAt')
			->add('updatedAt')
		;
	}
}
