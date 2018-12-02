<?php
// src/Form/Type/TaskType.php
namespace App\Form\Type;

use App\Entity\Report;
use App\Entity\ReportMetal;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class ReportMetalType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('type', MetalTypeSelectorType::class);
        $builder->add('quality');
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => ReportMetal::class,
        ));
    }
}
