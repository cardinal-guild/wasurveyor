<?php


namespace App\Repository;

use Doctrine\ORM\EntityRepository;

class IslandRepository extends EntityRepository
{
    public function getPublishedIslands()
    {
        $qb = $this->createQueryBuilder('island');
        $qb->select(
            'island',
            'ic',
            'ii',
            'ipvet',
            'ipvem',
            'ipvpt',
            'ipvpm'
       )
            ->leftJoin('island.creator', 'ic')
            ->leftJoin('island.images', 'ii')
            ->leftJoin('island.pveTrees', 'ipvet')
            ->leftJoin('island.pveMetals', 'ipvem')
            ->leftJoin('island.pvpTrees ', 'ipvpt')
            ->leftJoin('island.pvpMetals', 'ipvpm')
            ->where('island.published = 1')
            ->groupBy('island.id')
            ->orderBy('island.id', 'DESC');
        $qry = $qb->getQuery();
        return $qry->getResult();
    }

}
