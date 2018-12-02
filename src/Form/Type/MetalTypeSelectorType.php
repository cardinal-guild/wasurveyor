<?php

namespace App\Form\Type;

use App\Form\DataTransformer\IslandToNumberTransformer;
use App\Form\DataTransformer\MetalTypeToNumberTransformer;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MetalTypeSelectorType extends AbstractType
{
    private $transformer;

    public function __construct(MetalTypeToNumberTransformer $transformer)
    {
        $this->transformer = $transformer;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addModelTransformer($this->transformer);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'invalid_message' => 'The selected metal type does not exist',
        ));
    }

    public function getParent()
    {
        return IntegerType::class;
    }
}
