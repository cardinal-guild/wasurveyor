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
            'ipvpm',
            'ipvemt',
            'ipvpmt',
            'ipvett',
            'ipvptt'
       )
            ->leftJoin('island.creator', 'ic')
            ->leftJoin('island.images', 'ii')
            ->leftJoin('island.pveTrees', 'ipvet')
            ->leftJoin('island.pveMetals', 'ipvem')
            ->leftJoin('island.pvpTrees ', 'ipvpt')
            ->leftJoin('island.pvpMetals', 'ipvpm')
            ->leftJoin('ipvet.type', 'ipvett')
            ->leftJoin('ipvpt.type', 'ipvptt')
            ->leftJoin('ipvem.type', 'ipvemt')
            ->leftJoin('ipvpm.type', 'ipvpmt')
            ->where('island.published = 1')
            ->orderBy('island.id', 'DESC')
            ->orderBy('ii.position', 'ASC')
            ->groupBy('island.id');
        $qry = $qb->getQuery();
        return $qry->getResult();
    }
    public function getPublishedIslandsByQuery(array $params)
    {
        $qb = $this->createQueryBuilder('island');
        $qb->select(
            'island',
            'ic',
            'ii',
            'ipvet',
            'ipvem',
            'ipvpt',
            'ipvpm',
            'ipvemt',
            'ipvpmt',
            'ipvett',
            'ipvptt'
        )
            ->leftJoin('island.creator', 'ic')
            ->leftJoin('island.images', 'ii')
            ->leftJoin('island.pveTrees', 'ipvet')
            ->leftJoin('island.pveMetals', 'ipvem')
            ->leftJoin('island.pvpTrees ', 'ipvpt')
            ->leftJoin('island.pvpMetals', 'ipvpm')
            ->leftJoin('ipvet.type', 'ipvett')
            ->leftJoin('ipvpt.type', 'ipvptt')
            ->leftJoin('ipvem.type', 'ipvemt')
            ->leftJoin('ipvpm.type', 'ipvpmt')
            ->where('island.published = 1')
            ->orderBy('island.id', 'DESC')
            ->orderBy('ii.position', 'ASC');
        if(!empty($params['quality'])) {
            $qb->andWhere($qb->expr()->orX(
                $qb->expr()->eq('ipvem.quality', ":QUALITY"),
                $qb->expr()->eq('ipvpm.quality', ":QUALITY")
            ));
            $qb->setParameter('QUALITY', intval($params['quality']));
        }
        if(!empty($params['minquality'])) {
            $qb->andWhere($qb->expr()->orX(
                $qb->expr()->gte('ipvem.quality', ":MINQUALITY"),
                $qb->expr()->gte('ipvpm.quality', ":MINQUALITY")
            ));
            $qb->setParameter('MINQUALITY', intval($params['minquality']));
        }
        if(!empty($params['maxquality'])) {
            $qb->andWhere($qb->expr()->orX(
                $qb->expr()->lte('ipvem.quality', ":MAXQUALITY"),
                $qb->expr()->lte('ipvpm.quality', ":MAXQUALITY")
            ));
            $qb->setParameter('MAXQUALITY', intval($params['maxquality']));
        }
        if(!empty($params['metal'])) {
            $qb->andWhere($qb->expr()->orX(
                $qb->expr()->like('ipvemt.name', ":METAL"),
                $qb->expr()->like('ipvpmt.name', ":METAL")
            ));
            $qb->setParameter('METAL', '%'.$params['metal'].'%');
        }
        if(!empty($params['tree'])) {
            $qb->andWhere($qb->expr()->orX(
                $qb->expr()->like('ipvett.name', ":TREE"),
                $qb->expr()->like('ipvptt.name', ":TREE")
            ));
            $qb->setParameter('TREE', '%'.$params['tree'].'%');
        }
        if(!empty($params['creator'])) {
            $qb->andWhere($qb->expr()->like('ic.name', ":CREATOR"));
            $qb->setParameter('CREATOR', '%'.$params['creator'].'%');
        }
        if(!empty($params['island'])) {
            $qb->andWhere($qb->expr()->orX(
                $qb->expr()->like('island.name', ":ISLANDNAME"),
                $qb->expr()->like('island.nickname', ":ISLANDNAME"),
                $qb->expr()->like('island.slug', ":ISLANDNAME")
            ));
            $qb->setParameter('ISLANDNAME', '%'.$params['island'].'%');
        }
        $qb->groupBy('island.id');
        $qry = $qb->getQuery();
        return $qry->getResult();
    }

    public function getPublishedIslandsIds(array $params)
    {
        $qb = $this->createQueryBuilder('island');
        $qb->select(
            'island.id'
        )
            ->leftJoin('island.creator', 'ic')
            ->leftJoin('island.images', 'ii')
            ->leftJoin('island.pveTrees', 'ipvet')
            ->leftJoin('island.pveMetals', 'ipvem')
            ->leftJoin('island.pvpTrees ', 'ipvpt')
            ->leftJoin('island.pvpMetals', 'ipvpm')
            ->leftJoin('ipvet.type', 'ipvett')
            ->leftJoin('ipvpt.type', 'ipvptt')
            ->leftJoin('ipvem.type', 'ipvemt')
            ->leftJoin('ipvpm.type', 'ipvpmt')
            ->where('island.published = 1')
            ->orderBy('island.id', 'DESC')
            ->orderBy('ii.position', 'ASC');
        if(!empty($params['quality'])) {
            $qb->andWhere($qb->expr()->orX(
                $qb->expr()->eq('ipvem.quality', ":QUALITY"),
                $qb->expr()->eq('ipvpm.quality', ":QUALITY")
            ));
            $qb->setParameter('QUALITY', intval($params['quality']));
        }
        if(!empty($params['minquality'])) {
            $qb->andWhere($qb->expr()->orX(
                $qb->expr()->gte('ipvem.quality', ":MINQUALITY"),
                $qb->expr()->gte('ipvpm.quality', ":MINQUALITY")
            ));
            $qb->setParameter('MINQUALITY', intval($params['minquality']));
        }
        if(!empty($params['maxquality'])) {
            $qb->andWhere($qb->expr()->orX(
                $qb->expr()->lte('ipvem.quality', ":MAXQUALITY"),
                $qb->expr()->lte('ipvpm.quality', ":MAXQUALITY")
            ));
            $qb->setParameter('MAXQUALITY', intval($params['maxquality']));
        }
        if(!empty($params['metal'])) {
            $qb->andWhere($qb->expr()->orX(
                $qb->expr()->like('ipvemt.name', ":METAL"),
                $qb->expr()->like('ipvpmt.name', ":METAL")
            ));
            $qb->setParameter('METAL', '%'.$params['metal'].'%');
        }
        if(!empty($params['tree'])) {
            $qb->andWhere($qb->expr()->orX(
                $qb->expr()->like('ipvett.name', ":TREE"),
                $qb->expr()->like('ipvptt.name', ":TREE")
            ));
            $qb->setParameter('TREE', '%'.$params['tree'].'%');
        }
        if(!empty($params['creator'])) {
            $qb->andWhere($qb->expr()->like('ic.name', ":CREATOR"));
            $qb->setParameter('CREATOR', '%'.$params['creator'].'%');
        }
        if(!empty($params['island'])) {
            $qb->andWhere($qb->expr()->orX(
                $qb->expr()->like('island.name', ":ISLANDNAME"),
                $qb->expr()->like('island.nickname', ":ISLANDNAME"),
                $qb->expr()->like('island.slug', ":ISLANDNAME")
            ));
            $qb->setParameter('ISLANDNAME', '%'.$params['island'].'%');
        }
        $qb->groupBy('island.id');
        $qry = $qb->getQuery();
        $results = $qry->getArrayResult();
        return array_map(function($item) {
            return intval($item['id']);
        }, $results);
    }
}
