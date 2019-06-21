<?php


namespace App\Repository;

use Doctrine\ORM\EntityRepository;

class MetalTypeRepository extends EntityRepository
{
	public function findAll()
	{
		return $this->findBy(array(), array('type' => 'ASC'));
	}
}
