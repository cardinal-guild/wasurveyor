<?php


namespace App\Repository;

use App\Entity\User;
use Doctrine\ORM\EntityRepository;

class CharacterRepository extends EntityRepository
{

    public function getAllCharactersForOwner(User $owner)
    {
        $qb = $this->createQueryBuilder('c');
        $qb->select('c')
            ->where($qb->expr()->eq('c.owner', ":OWNER_ID"))
            ->setParameter('OWNER_ID', $owner->getId());
        $qry = $qb->getQuery();
        return $qry->getResult();
    }

    public function getCharacterCountByOwner(User $owner)
    {
        return $this->getAllCharactersForOwner($owner)->count();
    }
}
