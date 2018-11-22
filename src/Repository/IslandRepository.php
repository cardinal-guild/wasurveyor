<?php


namespace App\Repository;

use Doctrine\ORM\EntityRepository;

class IslandRepository extends EntityRepository
{
    public function getPublishedIslands()
    {
        $qb = $this->createQueryBuilder('island');
            $qb->select(
                'island'
           )
            ->leftJoin('island.creator', 'ic')
            ->leftJoin('island.images', 'ii')
            ->leftJoin('island.trees', 'it')
            ->leftJoin('island.pveMetals', 'ipvem')
            ->leftJoin('island.pvpMetals', 'ipvpm')
            ->leftJoin('it.type', 'itt')
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
            'island'
        )
            ->leftJoin('island.creator', 'ic')
            ->leftJoin('island.images', 'ii')
            ->leftJoin('island.trees', 'it')
            ->leftJoin('island.pveMetals', 'ipvem')
            ->leftJoin('island.pvpMetals', 'ipvpm')
            ->leftJoin('it.type', 'itt')
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
            $qb->andWhere($qb->expr()->like('itt.name', ":TREE"));
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
        $qry = $qb->getQuery();
        return $qry->getResult();
    }

    public function getPublishedIslandsByQueryLatLngOnly(array $params)
    {
        $qb = $this->createQueryBuilder('island');
        $qb->select(
            'partial island.{id,lat,lng,name,nickname}'
        )
            ->leftJoin('island.creator', 'ic')
            ->leftJoin('island.images', 'ii')
            ->leftJoin('island.trees', 'it')
            ->leftJoin('island.pveMetals', 'ipvem')
            ->leftJoin('island.pvpMetals', 'ipvpm')
            ->leftJoin('it.type', 'itt')
            ->leftJoin('ipvem.type', 'ipvemt')
            ->leftJoin('ipvpm.type', 'ipvpmt')
            ->where('island.published = 1')
            ->orderBy('island.id', 'DESC')
            ->orderBy('ii.position', 'ASC');
        if(!empty($params['quality'])) {
            $qb->andWhere($qb->expr()->orX(
                $qb->expr()->gte('ipvem.quality', ":MINQUALITY"),
                $qb->expr()->gte('ipvpm.quality', ":MINQUALITY")
            ));
            $qb->setParameter('MINQUALITY', intval($params['quality']));
        }
        if(!empty($params['metal'])) {
            $qb->andWhere($qb->expr()->orX(
                $qb->expr()->like('ipvemt.name', ":METAL"),
                $qb->expr()->like('ipvpmt.name', ":METAL")
            ));
            $qb->setParameter('METAL', '%'.$params['metal'].'%');
        }
        if(!empty($params['tree'])) {
            $qb->andWhere($qb->expr()->like('itt.name', ":TREE"));
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

        return $qry->getArrayResult();
    }

    public function getPublishedPveIslands()
    {
        $qb = $this->createQueryBuilder('island');
        $qb->select(
            'island'
        )
        ->leftJoin('island.creator', 'ic')
        ->leftJoin('island.images', 'ii')
        ->leftJoin('island.trees', 'it')
        ->leftJoin('island.pveMetals', 'ipvem')
        ->leftJoin('it.type', 'itt')
        ->leftJoin('ipvem.type', 'ipvemt')
        ->where('island.published = 1')
        ->orderBy('island.id', 'DESC')
        ->orderBy('ii.position', 'ASC')
        ->groupBy('island.id');
        $qry = $qb->getQuery();
        return $qry->getResult();
    }

    public function getPublishedPvpIslands()
    {
        $qb = $this->createQueryBuilder('island');
        $qb->select(
            'island'
        )
            ->leftJoin('island.creator', 'ic')
            ->leftJoin('island.images', 'ii')
            ->leftJoin('island.trees', 'it')
            ->leftJoin('island.pvpMetals', 'ipvpm')
            ->leftJoin('it.type', 'itt')
            ->leftJoin('ipvpm.type', 'ipvpmt')
            ->where('island.published = 1')
            ->orderBy('island.id', 'DESC')
            ->orderBy('ii.position', 'ASC')
            ->groupBy('island.id');
        $qry = $qb->getQuery();
        return $qry->getResult();
    }
}
