<?php


namespace App\Entity;

use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection as ArrayCollection;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use JMS\Serializer\Annotation as JMS;

/**
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="App\Repository\IslandTreeRepository")
 * @ORM\HasLifecycleCallbacks()
 * @JMS\ExclusionPolicy("all")
 */
class IslandTree
{
    /**
     * @var integer
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @JMS\Expose()
     */
    protected $id;

    /**
     * @var TreeType
     * @JMS\Expose
     * @ORM\ManyToOne(targetEntity="App\Entity\TreeType")
     * @ORM\JoinColumn(name="type_id", referencedColumnName="id", nullable=false)
     * @Assert\NotBlank()
     */
    protected $type;

    /**
     * @var Island
     * @ORM\ManyToOne(targetEntity="App\Entity\Island", inversedBy="trees")
     * @ORM\JoinColumn(name="island_id", referencedColumnName="id", nullable=false)
     */
    protected $island;

    /**
     * ProjectImage constructor.
     * @param int $id
     */
    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    use TimestampableEntity;

    /**
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id)
    {
        $this->id = $id;
    }

    /**
     * @return Island
     */
    public function getIsland(): ?Island
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

    /**
     * @return TreeType
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param TreeType $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    public function __toString()
    {
        if($this->getType()) {
            return $this->getType()->getName();
        }
        return "New island tree";
    }
}
