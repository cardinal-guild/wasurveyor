<?php


namespace App\Repository;

use App\Entity\Island;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NativeQuery;
use Doctrine\ORM\Query\ResultSetMapping;


class IslandRepository extends ServiceEntityRepository
{

    /**
     * @param int $amount
     * @return Island[]
     */
    public function getRandomIslands($amount = 7)
    {
        return $this->getRandomIslandsNativeQuery($amount)->getResult();
    }

    /**
     * @param int $amount
     * @return NativeQuery
     */
    public function getRandomIslandsNativeQuery($amount = 7)
    {
        # set entity name
        $table = $this->getClassMetadata()
            ->getTableName();

        # create rsm object
        $rsm = new ResultSetMapping();
        $rsm->addEntityResult($this->getEntityName(), 'i');
        $rsm->addFieldResult('i', 'id', 'id');

        # sql query
        $sql = "
            SELECT * FROM {$table}
            WHERE id >= FLOOR(1 + RAND()*(
                SELECT MAX(id) FROM {$table})
            ) 
            LIMIT ?
        ";

        # make query
        return $this->getEntityManager()
            ->createNativeQuery($sql, $rsm)
            ->setParameter(1, $amount);
    }

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
            ->addOrderBy('ii.position', 'ASC')
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
            ->addOrderBy('ii.position', 'ASC');
        if(!empty($params["id"])) {
            $qb->andWhere($qb->expr()->eq('island.id', ":ID"));
            $qb->setParameter('ID', intval($params['id']));
        }
        if(!empty($params["tier"])) {
            $ors = [];
            foreach(str_split($params["tier"]) as $t) {
                $ors[] = $qb->expr()->orx('island.tier = '.$qb->expr()->literal($t));
            }
            $qb->andWhere(join(' OR ', $ors));
        }
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
            ->addOrderBy('ii.position', 'ASC');
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
        ->leftJoin('island.reports','ir')
        ->leftJoin('ir.metals','irm')
        ->leftJoin('ir.trees','irt')
        ->leftJoin('island.pveMetals', 'ipvem')
        ->leftJoin('it.type', 'itt')
        ->leftJoin('ipvem.type', 'ipvemt')
        ->where('island.published = 1')
        ->orderBy('island.id', 'DESC')
        ->addOrderBy('ii.position', 'ASC')
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
            ->leftJoin('island.reports','ir')
            ->leftJoin('ir.metals','irm')
            ->leftJoin('ir.trees','irt')
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
