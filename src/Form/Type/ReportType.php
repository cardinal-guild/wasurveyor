<?php
// src/Form/Type/TaskType.php
namespace App\Form\Type;

use App\Entity\Report;
use App\Entity\ReportMetal;
use App\Form\DataTransformer\IslandToNumberTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class ReportType extends AbstractType
{

    private $islandTransformer;

    public function __construct(IslandToNumberTransformer $islandTransformer)
    {
        $this->islandTransformer = $islandTransformer;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('island', IslandSelectorType::class);
        $builder->add('mode');
        $builder->add('ipAddress');

        $builder->add('metals', CollectionType::class, array(
            'entry_type' => ReportMetalType::class,
            'entry_options' => array('label' => false),
            'allow_add' => true,
            'delete_empty' => true,
            'prototype' => true,
            'by_reference' => false,
        ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'csrf_protection' => false,
            'data_class' => Report::class,
        ));
    }
}
