<?php


namespace App\Entity;

use App\Traits\IslandMetalTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use JMS\Serializer\Annotation as JMS;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="App\Repository\IslandPVPMetalRepository")
 * @ORM\HasLifecycleCallbacks()
 * @JMS\ExclusionPolicy("all")
 */
class IslandPVPMetal
{

    /**
     * @var \Doctrine\Common\Collections\Collection|Island[}
     * @ORM\ManyToMany(targetEntity="App\Entity\Island", mappedBy="pvpMetals")
     */
    protected $islands;

    use IslandMetalTrait;
    use TimestampableEntity;


    public function __construct()
    {
        $this->islands = new ArrayCollection();
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    public function __toString()
    {
        if($this->getType() && $this->getQuality()) {
            return $this->getType()->__toString().' Q'.$this->getQuality();
        }
        // TODO: Implement __toString() method.
        return "New Island Metal";
    }
}
