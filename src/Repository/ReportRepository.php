<?php


namespace App\Repository;

use Doctrine\ORM\EntityRepository;

class ReportRepository extends EntityRepository
{




    public function hasSpamReported($islandId, $ip)
    {
        $lastEightHours = new \DateTime();
        $lastEightHours->modify('-8 hour');

        $qb = $this->createQueryBuilder('report');
        $qb->select(
                'report'
            )
            ->leftJoin('report.metals', 'rm')
            ->where($qb->expr()->eq('report.island', ":ISLAND_ID"))
            ->andWhere($qb->expr()->eq('report.ipAddress', ":IP_ADDRESS"))
            ->andWhere('report.createdAt > :LAST_EIGHT_HOURS')
            ->setParameter('ISLAND_ID', (integer)$islandId)
            ->setParameter('IP_ADDRESS', $ip)
            ->setParameter(':LAST_EIGHT_HOURS', $lastEightHours)
            ->setMaxResults(1);
        $qry = $qb->getQuery();
        return $qry->getOneOrNullResult();
    }
}
