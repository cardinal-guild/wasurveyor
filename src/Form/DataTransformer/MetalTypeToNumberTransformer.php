<?php

namespace App\Form\DataTransformer;

use App\Entity\Island;
use App\Entity\MetalType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class MetalTypeToNumberTransformer implements DataTransformerInterface
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Transforms an object (issue) to a integer (number).
     *
     * @param  MetalType|null $metalType
     * @return integer
     */
    public function transform($metalType)
    {
        if (null === $metalType) {
            return 0;
        }

        return $metalType->getId();
    }

    /**
     * Transforms a integer (number) to an object (issue).
     *
     * @param  integer $metalTypeNumber
     * @return MetalType|null
     * @throws TransformationFailedException if object (island) is not found.
     */
    public function reverseTransform($metalTypeNumber)
    {
        // no issue number? It's optional, so that's ok
        if (!$metalTypeNumber) {
            return;
        }

        $metalType = $this->entityManager
            ->getRepository(MetalType::class)
            // query for the issue with this id
            ->find((integer)$metalTypeNumber)
        ;

        if (null === $metalType) {
            // causes a validation error
            // this message is not shown to the user
            // see the invalid_message option
            throw new TransformationFailedException(sprintf(
                'A metal type with number "%s" does not exist!',
                $metalType
            ));
        }

        return $metalType;
    }
}
