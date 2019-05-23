<?php

namespace App\Repository;

use App\Entity\TCData;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method TCData|null find($id, $lockMode = null, $lockVersion = null)
 * @method TCData|null findOneBy(array $criteria, array $orderBy = null)
 * @method TCData[]    findAll()
 * @method TCData[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TCDataRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, TCData::class);
    }

    // /**
    //  * @return TCData[] Returns an array of TCData objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?TCData
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
