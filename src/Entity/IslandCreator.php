<?php

namespace App\Entity;

use Cocur\Slugify\Slugify;
use Doctrine\Common\Collections\Collection;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection as ArrayCollection;
use Gedmo\Mapping\Annotation as Gedmo;
use Knp\DoctrineBehaviors\Model\Translatable\Translatable;

/**
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="App\Repository\IslandCreatorRepository")
 * @ORM\Table()
 * @ORM\HasLifecycleCallbacks
 * @Gedmo\SoftDeleteable(fieldName="deletedAt")
 */
class IslandCreator
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     * @ORM\Column(length=128)
     */
    protected $name;

    /**
     * @var string
     * @ORM\Column(nullable=true)
     */
    protected $workshopUrl;

    /**
     * @var \Doctrine\Common\Collections\Collection|Island[]
     * @ORM\OneToMany(targetEntity="App\Entity\Island", mappedBy="creator", fetch="EXTRA_LAZY")
     */
    protected $islands;

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
    public function setId($id): void
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getWorkshopUrl(): ?string
    {
        return $this->workshopUrl;
    }

    /**
     * @param string $workshopUrl
     */
    public function setWorkshopUrl(?string $workshopUrl): void
    {
        $this->workshopUrl = $workshopUrl;
    }

    /**
     * @return \Doctrine\Common\Collections\Collection|Island[]
     */
    public function getIslands()
    {
        return $this->islands;
    }

    /**
     * @param \Doctrine\Common\Collections\Collection $islands
     * @return \Doctrine\Common\Collections\Collection|Island[]
     */
    public function setIslands(\Doctrine\Common\Collections\Collection $islands)
    {
        $this->islands = new ArrayCollection();
        foreach($islands as $island) {
            $this->addIsland($island);
        }
        return $this->islands;
    }

    /**
     * @param Island $island
     * @return \Doctrine\Common\Collections\Collection|Island[]
     */
    public function addIsland(Island $island)
    {
        if(!$this->islands->contains($island)) {
            $this->islands[] = $island;
        }
        return $this->islands;
    }

    /**
     * @param Project $project
     * @return \Doctrine\Common\Collections\Collection|Island[]
     */
    public function removeIsland(Island $island)
    {
        $this->islands->removeElement($island);
        return $this->islands;
    }

    public function __toString()
    {
        if($this->getName()) {
            return $this->getName();
        }
        return "New island author";
    }

}
