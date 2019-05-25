<?php


namespace App\Entity;

use App\Traits\IslandMetalCollections;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\EntityManager;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as JMS;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Sonata\UserBundle\Entity\BaseGroup;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="App\Repository\AllianceRepository")
 * @ORM\HasLifecycleCallbacks
 * @Gedmo\SoftDeleteable(fieldName="deletedAt")
 * @JMS\ExclusionPolicy("all")
 */
class Alliance
{
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
	 * @ORM\Column(type="text", nullable=true)
	 * @JMS\Expose()
	 */
	protected $description;

	/**
	 * @ORM\OneToMany(targetEntity="App\Entity\IslandTerritoryControl", mappedBy="alliance")
	 */
	protected $territories;

	use TimestampableEntity;
	use SoftDeleteableEntity;

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



	public function __toString()
	{
		if ($this->getName()) {
			return $this->getName();
		}
		return "New Alliance";
	}
}
