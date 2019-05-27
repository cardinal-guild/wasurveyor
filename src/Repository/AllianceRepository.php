<?php


namespace App\Repository;


use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

class AllianceRepository extends ServiceEntityRepository
{
    public function getAlliancesByQuery(array $params)
    {
        $qb = $this->createQueryBuilder('alliance');
        $qb->select('alliance')->orderBy('alliance.id', 'DESC');

        if (!empty($params['id'])) {
            $qb->andWhere($qb->expr()->eq('alliance.id', ':ID'));
            $qb->setParameter('ID', intval($params['id']));
        }

        if (!empty($params['name'])) {
            $qb->andWhere($qb->expr()->like('alliance.name', ':NAME'));
            $qb->setParameter('NAME', '%'.$params['name'].'%');
        }

        $qry = $qb->getQuery();
        return $qry->getResult();
    }
}
