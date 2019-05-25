<?php

namespace App\Entity;

use App\Traits\IslandMetalCollections;
use Cocur\Slugify\Slugify;
use Doctrine\Common\Collections\ArrayCollection as ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="App\Repository\IslandTerritoryControlRepository")
 * @ORM\HasLifecycleCallbacks()
 * @JMS\ExclusionPolicy("all")
 * @Gedmo\Loggable
 */
class IslandTerritoryControl
{
	const PVE = 'pve';
	const PVP = 'pvp';
	const PTS = 'pts';

	/**
	 * @var integer
	 * @ORM\Id
	 * @ORM\Column(type="integer")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 * @JMS\Expose()
	 */
	protected $id;

	/**
	 * @var Island
	 * @Assert\NotBlank()
	 * @ORM\ManyToOne(targetEntity="App\Entity\Island", inversedBy="territories")
	 * @ORM\JoinColumn(name="island_id", referencedColumnName="id")
	 */
	protected $island;

	/**
	 * @var string
	 * @Assert\NotBlank()
	 * @ORM\Column(length=3)
	 */
	protected $server = self::PTS;

	/**
	 * @var string
	 * @JMS\Expose
	 * @Gedmo\Versioned
	 * @ORM\Column(nullable=true)
	 */
	protected $towerName;

	/**
	 * @var Alliance|null
	 * @JMS\Expose
	 * @Gedmo\Versioned
	 * @ORM\ManyToOne(targetEntity="App\Entity\Alliance", inversedBy="territories", cascade={"persist"})
	 * @ORM\JoinColumn(referencedColumnName="id", nullable=true)
	 */
	protected $alliance;

	/**
	 * @return int
	 */
	public function getId(): int
	{
		return $this->id;
	}

	/**
	 * @param int $id
	 */
	public function setId(int $id): void
	{
		$this->id = $id;
	}

	/**
	 * @return string
	 */
	public function getServer()
	{
		return $this->server;
	}

	/**
	 * @param string $server
	 */
	public function setServer(string $server)
	{
		$this->server = $server;
	}

	/**
	 * @return string
	 */
	public function getTowerName(): ?string
	{
		return $this->towerName;
	}

	/**
	 * @return string
	 * Get tower name, if nullified, return Unnamed
	 */
	public function getTowerNameUnnamed(): string
	{
		if(!$this->towerName) {
			return "Unnamed";
		}
		return $this->towerName;
	}

	/**
	 * @param string $towerName
	 */
	public function setTowerName(?string $towerName)
	{
		$this->towerName = $towerName;
	}

	/**
	 * @return Alliance|null
	 */
	public function getAlliance():?string
	{

		return $this->alliance;
	}

	/**
	 * @return Alliance|null
	 * Get alliance name, if nullified, return Unclaimed
	 */
	public function getAllianceName():string
	{
		if(!$this->alliance) {
			return "Unclaimed";
		}
		return $this->alliance->getName();
	}

	/**
	 * @param Alliance|null $alliance
	 */
	public function setAlliance($alliance)
	{
		$this->alliance = $alliance;
	}

	/**
	 * @return Island
	 */
	public function getIsland(): Island
	{
		return $this->island;
	}

	/**
	 * @param Island $island
	 */
	public function setIsland(Island $island)
	{
		$this->island = $island;
	}


	public function __toString()
	{
		if($this->getTowerName()) {
			return $this->getTowerName();
		}
		// TODO: Implement __toString() method.
		return "New territory";
	}
}
