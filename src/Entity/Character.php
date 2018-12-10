<?php


namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\EntityManager;
use phpDocumentor\Reflection\Types\Array_;
use phpDocumentor\Reflection\Types\Integer;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use JMS\Serializer\Annotation as JMS;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Table(name="user_character")
 * @ORM\Entity(repositoryClass="App\Repository\CharacterRepository")
 * @ORM\HasLifecycleCallbacks()
 * @JMS\ExclusionPolicy("all")
 */
class Character
{

    /**
     *
     * @var string
     * @ORM\Column(type="guid")
     * @ORM\Id
     * @JMS\Expose()
     * @ORM\GeneratedValue(strategy="UUID")
     */
    protected $guid;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="characters")
     * @ORM\JoinColumn(name="owner_id", referencedColumnName="id", nullable=false)
     * @Assert\NotBlank()
     */
    protected $owner;

    /**
     * @var string
     * @JMS\Expose
     * @ORM\Column(type="string")
     */
    protected $name;

    /**
     * @var array
     * @JMS\Expose()
     * @JMS\SerializedName("visited_islands")
     * @ORM\Column(type="simple_array", nullable=true)
     */
    protected $visitedIslands;

    public function __construct()
    {
        $this->visitedIslands = [];
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
     * @return User
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * @param User $owner
     */
    public function setOwner($owner)
    {
        $this->owner = $owner;
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
     * @return array
     */
    public function getVisitedIslands()
    {
        return $this->visitedIslands;
    }

    /**
     * @param array $visitedIslands
     * @return array
     */
    public function setVisitedIslands(array $visitedIslands)
    {
        $this->visitedIslands = $visitedIslands;
        return $this->visitedIslands;
    }

    /**
     * @param int $id
     * @return array
     */
    public function addVisitedIsland(int $id)
    {
        if(!in_array($id, $this->visitedIslands)) {
            $this->visitedIslands[] = (integer)$id;
        }
        return $this->visitedIslands;
    }

    /**
     * @param int $id
     * @return array
     */
    public function removeVisitedIsland(int $id)
    {
        if (($key = array_search((integer)$id, $this->visitedIslands)) !== false) {
            unset($this->visitedIslands[$key]);
        }
        return $this->visitedIslands;
    }

    public function __toString()
    {
        if($this->getName()) {
            return $this->getName();
        }
        return 'New character';
    }

}
