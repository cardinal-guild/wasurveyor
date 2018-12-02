<?php

namespace App\Form\DataTransformer;

use App\Entity\Island;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class IslandToNumberTransformer implements DataTransformerInterface
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Transforms an object (issue) to a integer (number).
     *
     * @param  Island|null $island
     * @return integer
     */
    public function transform($island)
    {
        if (null === $island) {
            return 0;
        }

        return $island->getId();
    }

    /**
     * Transforms a integer (number) to an object (issue).
     *
     * @param  integer $islandNumber
     * @return Island|null
     * @throws TransformationFailedException if object (island) is not found.
     */
    public function reverseTransform($islandNumber)
    {
        // no issue number? It's optional, so that's ok
        if (!$islandNumber) {
            return;
        }

        $island = $this->entityManager
            ->getRepository(Island::class)
            // query for the issue with this id
            ->find((integer)$islandNumber)
        ;

        if (null === $island) {
            // causes a validation error
            // this message is not shown to the user
            // see the invalid_message option
            throw new TransformationFailedException(sprintf(
                'An island with number "%s" does not exist!',
                $island
            ));
        }

        return $island;
    }
}
