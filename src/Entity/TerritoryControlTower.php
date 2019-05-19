<?php


namespace App\Entity;

use App\Traits\IslandMetalCollections;
use App\Traits\TerritoryControlTowerTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as JMS;
use Doctrine\ORM\EntityManager;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Sonata\UserBundle\Entity\BaseGroup;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="App\Repository\TerritoryControlTowerRepository")
 * @ORM\HasLifecycleCallbacks
 * @JMS\ExclusionPolicy("all")
 */
class TerritoryControlTower
{
	const PVP = "pvp";
	const PVE = "pve";

	/**
	 * @var Island
	 * @ORM\OneToOne(targetEntity="App\Entity\Island", mappedBy="pveTower")
	 */
	protected $pveIsland;

	/**
	 * @var Island
	 * @ORM\OneToOne(targetEntity="App\Entity\Island", mappedBy="pvpTower")
	 */
	protected $pvpIsland;

	/**
	 * @ORM\Id
	 * @ORM\Column(type="integer")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 * @JMS\Expose()
	 */
	protected $id;

	/**
	 * @var string
	 * @ORM\Column(type="string")
	 * @JMS\Expose()
	 * @Assert\NotBlank()
	 */
	protected $name;

	/**
	 * @var string
	 * @ORM\Column(type="string", nullable=true)
	 */
	protected $guid;

	/**
	 * @var Alliance
	 * @JMS\Expose
	 * @ORM\ManyToOne(targetEntity="App\Entity\Alliance", inversedBy="towers")
	 * @ORM\JoinColumn(referencedColumnName="id", nullable=true)
	 */
	protected $alliance;

	/**
	 * @var string
	 * @ORM\Column(type="string", nullable=true, length=3)
	 */
	protected $mode = self::PVE;

	use TimestampableEntity;

	public function __construct()
	{
		$this->createdAt = new \DateTime();
		$this->updatedAt = new \DateTime();
	}
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
	public function setId($id)
	{
		$this->id = $id;
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * @param string $name
	 */
	public function setName($name)
	{
		$this->name = $name;
	}

	/**
	 * @return string
	 */
	public function getDescription()
	{
		return $this->description;
	}

	/**
	 * @param string $description
	 */
	public function setDescription($description)
	{
		$this->description = $description;
	}

	/**
	 * @return string
	 */
	public function getGuid()
	{
		return $this->guid;
	}

	/**
	 * @param string $guid
	 */
	public function setGuid($guid)
	{
		$this->guid = $guid;
	}

	/**
	 * @return Alliance
	 */
	public function getAlliance(): ?Alliance
	{
		return $this->alliance;
	}

	/**
	 * @param Alliance $alliance
	 */
	public function setAlliance(?Alliance $alliance)
	{
		$this->alliance = $alliance;
	}

	/**
	 * @return string
	 */
	public function getMode()
	{
		return $this->mode;
	}

	/**
	 * @param string $mode
	 * @return TerritoryControlTower
	 */
	public function setMode(?string $mode)
	{
		$this->mode = $mode;
		return $this;
	}

	/**
	 * @return Island
	 */
	public function getPveIsland()
	{
		return $this->pveIsland;
	}

	/**
	 * @param Island $pveIsland
	 */
	public function setPveIsland($pveIsland)
	{
		$this->pveIsland = $pveIsland;
	}

	/**
	 * @return Island
	 */
	public function getPvpIsland()
	{
		return $this->pvpIsland;
	}

	/**
	 * @param Island $pvpIsland
	 */
	public function setPvpIsland($pvpIsland)
	{
		$this->pvpIsland = $pvpIsland;
	}

	public function getIsland()
	{
		if($this->mode === self::PVE) {
			return $this->getPveIsland();
		}
		return $this->getPvpIsland();
	}



	public function __toString()
	{
		if($this->getName()) {
			return $this->getName();
		}
		return "New Territory Control Tower";
	}
}
