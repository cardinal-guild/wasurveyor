<?php


namespace App\Traits;

use Doctrine\Common\Collections\ArrayCollection;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as JMS;
use Gedmo\Mapping\Annotation as Gedmo;

trait IslandTreeTrait
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
     * @var MetalType
     * @JMS\Expose
     * @ORM\ManyToOne(targetEntity="App\Entity\TreeType")
     * @ORM\JoinColumn(name="type_id", referencedColumnName="id", nullable=false)
     * @Assert\NotBlank()
     */
    protected $type;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return MetalType
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param MetalType $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return Island|\Doctrine\Common\Collections\Collection
     */
    public function getIslands()
    {
        return $this->islands;
    }

    /**
     * @param Island|\Doctrine\Common\Collections\Collection $islands
     */
    public function setIslands($islands)
    {
        $this->islands = new ArrayCollection();
        foreach($islands as $island) {
            $this->addIsland($island);
        }
    }

    /**
     * @param Island $island
     * @return \Doctrine\Common\Collections\Collection|Island[]
     */
    public function addIsland($island)
    {
        if(!$this->islands->contains($island)) {
            $this->islands->add($island);
        }
        return $this->islands;
    }

    /**
     * @param Island $island
     * @return \Doctrine\Common\Collections\Collection|Island[]
     */
    public function removeIsland($island)
    {
        if($this->islands->contains($island)) {
            $this->islands->removeElement($island);
        }
        return $this->islands;
    }
}
