<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TowerChangeRepository")
 * @ORM\HasLifecycleCallbacks()
 * @JMS\ExclusionPolicy("all")
 */
class TowerChange
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    protected $id;

	/**
	 * @ORM\Column(type="string")
	 */
	protected $oldTowerName;

	/**
	 * @ORM\Column(type="string")
	 */
	protected $newTowerName;

	/**
	 * @JMS\Expose
	 * @ORM\ManyToOne(targetEntity="App\Entity\Alliance")
	 * @ORM\JoinColumn(referencedColumnName="id", nullable=true, cascade={"none"})
	 */
	protected $oldAlliance;

	/**
	 * @JMS\Expose
	 * @ORM\ManyToOne(targetEntity="App\Entity\Alliance")
	 * @ORM\JoinColumn(referencedColumnName="id", nullable=true, cascade={"none"})
	 */
	protected $newAlliance;

	/**
	 * @return mixed
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @param mixed $id
	 */
	public function setId($id): void
	{
		$this->id = $id;
	}

	/**
	 * @return mixed
	 */
	public function getOldTowerName()
	{
		return $this->oldTowerName;
	}

	/**
	 * @param mixed $oldTowerName
	 */
	public function setOldTowerName($oldTowerName): void
	{
		$this->oldTowerName = $oldTowerName;
	}

	/**
	 * @return mixed
	 */
	public function getNewTowerName()
	{
		return $this->newTowerName;
	}

	/**
	 * @param st $newTowerName
	 */
	public function setNewTowerName($newTowerName)
	{
		$this->newTowerName = $newTowerName;
	}

	/**
	 * @return Alliance|null
	 */
	public function getOldAlliance():?Alliance
	{
		return $this->oldAlliance;
	}

	/**
	 * @param Alliance $oldAlliance
	 */
	public function setOldAlliance($oldAlliance)
	{
		$this->oldAlliance = $oldAlliance;
	}

	/**
	 * @return Alliance|null
	 */
	public function getNewAlliance():?Alliance
	{
		return $this->newAlliance;
	}

	/**
	 * @param Alliance $newAlliance
	 */
	public function setNewAlliance($newAlliance)
	{
		$this->newAlliance = $newAlliance;
	}


}
