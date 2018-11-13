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
     * @var integer
     * @ORM\Column(type="integer")
     * @Assert\Range(min="1", max="10")
     */
    protected $quality = 1;

    /**
     * @var \Doctrine\Common\Collections\Collection|Island[}
     * @ORM\ManyToMany(targetEntity="App\Entity\Island", mappedBy="pveTrees")
     */
    protected $pveIslands;

    /**
     * @var \Doctrine\Common\Collections\Collection|Island[}
     * @ORM\ManyToMany(targetEntity="App\Entity\Island", mappedBy="pvpTrees")
     */
    protected $pvpIslands;

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

    /**
     * @return int
     */
    public function getQuality()
    {
        return $this->quality;
    }

    /**
     * @param int $quality
     */
    public function setQuality($quality)
    {
        $this->quality = $quality;
    }

    /**
     * @return Island|\Doctrine\Common\Collections\Collection
     */
    public function getPveIslands()
    {
        return $this->pveIslands;
    }

    /**
     * @param Island|\Doctrine\Common\Collections\Collection $islands
     */
    public function setPveIslands($islands)
    {
        $this->pveIslands = new ArrayCollection();
        foreach($islands as $island) {
            $this->addPveIsland($island);
        }
    }

    /**
     * @param Island $island
     * @return \Doctrine\Common\Collections\Collection|Island[]
     */
    public function addPveIsland($island)
    {
        $island->addPveMetal($this);
        if(!$this->pveIslands->contains($island)) {
            $this->pveIslands->add($island);
        }
        return $this->pveIslands;
    }

    /**
     * @param Island $island
     * @return \Doctrine\Common\Collections\Collection|Island[]
     */
    public function removePveIsland($island)
    {
        if($this->pveIslands->contains($island)) {
            $this->pveIslands->removeElement($island);
        }
        return $this->pveIslands;
    }


    /**
     * @return Island|\Doctrine\Common\Collections\Collection
     */
    public function getPvpIslands()
    {
        return $this->pvpIslands;
    }

    /**
     * @param Island|\Doctrine\Common\Collections\Collection $islands
     */
    public function setPvpIslands($islands)
    {
        $this->pvpIslands = new ArrayCollection();
        foreach($islands as $island) {
            $this->addPvpIsland($island);
        }
    }

    /**
     * @param Island $island
     * @return \Doctrine\Common\Collections\Collection|Island[]
     */
    public function addPvpIsland($island)
    {
        $island->addPveMetal($this);
        if(!$this->pvpIslands->contains($island)) {
            $this->pvpIslands->add($island);
        }
        return $this->pvpIslands;
    }

    /**
     * @param Island $island
     * @return \Doctrine\Common\Collections\Collection|Island[]
     */
    public function removePvpIsland($island)
    {
        if($this->pvpIslands->contains($island)) {
            $this->pvpIslands->removeElement($island);
        }
        return $this->pvpIslands;
    }
}
