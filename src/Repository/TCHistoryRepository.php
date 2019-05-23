<?php

namespace App\Repository;

use App\Entity\TCHistory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method TCHistory|null find($id, $lockMode = null, $lockVersion = null)
 * @method TCHistory|null findOneBy(array $criteria, array $orderBy = null)
 * @method TCHistory[]    findAll()
 * @method TCHistory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TCHistoryRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, TCHistory::class);
    }

    // /**
    //  * @return TCHistory[] Returns an array of TCHistory objects
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
    public function findOneBySomeField($value): ?TCHistory
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
