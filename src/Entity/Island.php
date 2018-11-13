<?php

namespace App\Entity;

use Cocur\Slugify\Slugify;
use Facebook\GraphNodes\Collection;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection as ArrayCollection;
use Gedmo\Mapping\Annotation as Gedmo;
use Knp\DoctrineBehaviors\Model\Translatable\Translatable;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="App\Repository\IslandRepository")
 * @ORM\HasLifecycleCallbacks
 * @Gedmo\SoftDeleteable(fieldName="deletedAt")
 * @JMS\ExclusionPolicy("all")
 */
class Island
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
     * @ORM\Column(length=128)
     * @JMS\Expose()
     * @Assert\NotBlank()
     */
    protected $name;

    /**
     * @var string
     * @ORM\Column(length=128, nullable=true)
     * @JMS\Expose()
     */
    protected $nickname;

    /**
     * @var string
     * @ORM\Column(length=128)
     * @JMS\Expose()
     */
    protected $slug;

    /**
     * @var float
     * @ORM\Column(type="decimal", scale=2)
     * @Assert\NotBlank()
     * @Assert\NotEqualTo(value="0")
     */
    protected $lat;

    /**
     * @var float
     * @ORM\Column(type="decimal", scale=2)
     * @Assert\NotBlank()
     * @Assert\NotEqualTo(value="0")
     */
    protected $lng;

    /**
     * @var boolean
     * @ORM\Column(type="boolean")
     */
    protected $respawners = false;

    /**
     * @var boolean
     * @ORM\Column(type="boolean")
     */
    protected $cannons = false;

    /**
     * @var boolean
     * @ORM\Column(type="boolean")
     */
    protected $dangerous = false;

    /**
     * @var boolean
     * @ORM\Column(type="boolean")
     */
    protected $turrets = false;

    /**
     * @var boolean
     * @ORM\Column(type="boolean")
     */
    protected $spikes = false;

    /**
     * @var boolean
     * @ORM\Column(type="boolean")
     */
    protected $nonGrappleWalls = false;

    /**
     * @var boolean
     * @ORM\Column(type="boolean")
     */
    protected $published = true;

    /**
     * @var \Doctrine\Common\Collections\Collection|IslandImage[]
     * @ORM\OneToMany(targetEntity="App\Entity\IslandImage", mappedBy="island", cascade={"persist","remove"}, orphanRemoval=true)
     * @ORM\OrderBy({"position" = "ASC"})
     * @Assert\Count(min=1, minMessage="At least one picture is required.  Upload images in the media tab.")
     */
    protected $images;

    /**
     * @var \Doctrine\Common\Collections\Collection|IslandMetal[]
     * @ORM\ManyToMany(targetEntity="App\Entity\IslandMetal", cascade={"persist","remove"}, orphanRemoval=true, inversedBy="pveIslands")
     * @ORM\JoinTable(name="pve_island_metals",
     *      joinColumns={@ORM\JoinColumn(name="island_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="metal_id", referencedColumnName="id")}
     * )
     * @JMS\Expose()
     */
    protected $pveMetals;

    /**
     * @var \Doctrine\Common\Collections\Collection|IslandTree[]
     * @ORM\ManyToMany(targetEntity="App\Entity\IslandTree", cascade={"persist","remove"}, orphanRemoval=true, inversedBy="pveIslands")
     * @ORM\JoinTable(name="pve_island_trees",
     *      joinColumns={@ORM\JoinColumn(name="island_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="tree_id", referencedColumnName="id")}
     * )
     * @JMS\Expose()
     */
    protected $pveTrees;


    /**
     * @var \Doctrine\Common\Collections\Collection|IslandMetal[]
     * @ORM\ManyToMany(targetEntity="App\Entity\IslandMetal", cascade={"persist","remove"}, orphanRemoval=true, inversedBy="pvpIslands")
     * @ORM\JoinTable(name="pvp_island_metals",
     *      joinColumns={@ORM\JoinColumn(name="island_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="metal_id", referencedColumnName="id")}
     * )
     * @JMS\Expose()
     */
    protected $pvpMetals;

    /**
     * @var \Doctrine\Common\Collections\Collection|IslandTree[]
     * @ORM\ManyToMany(targetEntity="App\Entity\IslandTree", cascade={"persist","remove"}, orphanRemoval=true, inversedBy="pvpIslands")
     * @ORM\JoinTable(name="pvp_island_trees",
     *      joinColumns={@ORM\JoinColumn(name="island_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="tree_id", referencedColumnName="id")}
     * )
     * @JMS\Expose()
     */
    protected $pvpTrees;

    /**
     * @var IslandCreator
     * @JMS\Expose
     * @ORM\ManyToOne(targetEntity="IslandCreator", cascade={"persist"}, inversedBy="islands")
     * @ORM\JoinColumn(name="creator_id", referencedColumnName="id", nullable=false)
     * @Assert\NotBlank()
     */
    protected $creator;

    /**
     * @var User
     * @JMS\Expose
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(referencedColumnName="id", nullable=true)
     */
    protected $surveyCreatedBy;

    /**
     * @var User
     * @JMS\Expose
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(referencedColumnName="id", nullable=true)
     */
    protected $surveyUpdatedBy;

    /**
     * @var string
     * @ORM\Column(nullable=true)
     */
    protected $workshopUrl;

    use TimestampableEntity;
    use SoftDeleteableEntity;

    public function __construct()
    {

        $this->images = new ArrayCollection();
        $this->pveTrees = new ArrayCollection();
        $this->pveMetals = new ArrayCollection();
        $this->pveTrees = new ArrayCollection();
        $this->pvpMetals = new ArrayCollection();
        $this->lat = 0;
        $this->lng = 0;
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
        $this->setSlug($this->__toString());
    }

    /**
     * @return string
     */
    public function getNickname()
    {
        return $this->nickname;
    }

    /**
     * @param string $nickname
     */
    public function setNickname($nickname)
    {
        $this->nickname = $nickname;
        $this->setSlug($this->__toString());
    }

    /**
     * @return string
     */
    public function getSlug(): ?string
    {
        return $this->slug;
    }

    /**
     * @param string $slug
     */
    public function setSlug(string $slug): void
    {
        $this->slug = Slugify::create()->slugify($slug);
    }

    /**
     * @return float
     */
    public function getLat()
    {
        return $this->lat;
    }

    /**
     * @param float $lat
     */
    public function setLat($lat): void
    {
        $this->lat = $lat;
    }

    /**
     * @return float
     */
    public function getLng()
    {
        return $this->lng;
    }

    /**
     * @param float $lng
     */
    public function setLng($lng)
    {
        $this->lng = $lng;
    }

    /**
     * @return bool
     */
    public function isRespawners(): bool
    {
        return $this->respawners;
    }

    /**
     * @param bool $respawners
     */
    public function setRespawners(bool $respawners): void
    {
        $this->respawners = $respawners;
    }

    /**
     * @return bool
     */
    public function isCannons(): bool
    {
        return $this->cannons;
    }

    /**
     * @param bool $cannons
     */
    public function setCannons(bool $cannons): void
    {
        $this->cannons = $cannons;
    }

    /**
     * @return bool
     */
    public function isDangerous(): bool
    {
        return $this->dangerous;
    }

    /**
     * @param bool $dangerous
     */
    public function setDangerous(bool $dangerous): void
    {
        $this->dangerous = $dangerous;
    }

    /**
     * @return bool
     */
    public function isPublished(): bool
    {
        return $this->published;
    }

    /**
     * @param bool $published
     */
    public function setPublished(bool $published): void
    {
        $this->published = $published;
    }

    /**
     * @return IslandImage[]|\Doctrine\Common\Collections\Collection
     */
    public function getImages()
    {
        return $this->images;
    }

    /**
     * @param IslandImage[]|\Doctrine\Common\Collections\Collection $images
     * @return \Doctrine\Common\Collections\Collection|IslandImage[]
     */
    public function setImages($images)
    {
        $this->images = new ArrayCollection();
        foreach($images as $image) {
            $this->addImage($image);
        }
        return $this->images;
    }

    /**
     * @param IslandImage $image
     * @return \Doctrine\Common\Collections\Collection|IslandImage[]
     */
    public function addImage(IslandImage $image)
    {
        $image->setIsland($this);
        $this->images->add($image);
        return $this->images;
    }

    /**
     * @param IslandImage $image
     * @return \Doctrine\Common\Collections\Collection|IslandImage[]
     */
    public function removeImage(IslandImage $image)
    {
        $this->images->removeElement($image);
        return $this->images;
    }

    /**
     * @return IslandTree[]|\Doctrine\Common\Collections\Collection
     */
    public function getPveTrees()
    {
        return $this->pveTrees;
    }

    /**
     * @param IslandTree[]|\Doctrine\Common\Collections\Collection $trees
     */
    public function setPveTrees($trees)
    {
        $this->pveTrees = new ArrayCollection();
        foreach($trees as $tree) {
            $this->addPveTree($tree);
        }
    }

    /**
     * @param IslandTree $tree
     * @return \Doctrine\Common\Collections\Collection|IslandTree[]
     */
    public function addPveTree($tree)
    {
        $tree->addPveIsland($this);
        if(!$this->pveTrees->contains($tree)) {
            $this->pveTrees->add($tree);
        }
        return $this->pveTrees;
    }

    /**
     * @param IslandTree $tree
     * @return \Doctrine\Common\Collections\Collection|IslandTree[]
     */
    public function removePveTree($tree)
    {
        if($this->pveTrees->contains($tree)) {
            $this->pveTrees->removeElement($tree);
        }
        return $this->pveTrees;
    }

    /**
     * @return IslandMetal[]|\Doctrine\Common\Collections\Collection
     */
    public function getPveMetals()
    {
        return $this->pveMetals;
    }

    /**
     * @param IslandMetal[]|\Doctrine\Common\Collections\Collection $metals
     */
    public function setPveMetals($metals)
    {
        $this->pveMetals = new ArrayCollection();
        foreach($metals as $metal) {
            $this->addPveMetal($metal);
        }
    }

    /**
     * @param IslandMetal $metal
     * @return \Doctrine\Common\Collections\Collection|IslandMetal[]
     */
    public function addPveMetal($metal)
    {
        $metal->addPveIsland($this);
        if(!$this->pveMetals->contains($metal)) {
            $this->pveMetals->add($metal);
        }
        return $this->pveMetals;
    }

    /**
     * @param IslandMetal $metal
     * @return \Doctrine\Common\Collections\Collection|IslandMetal[]
     */
    public function removePveMetal($metal)
    {
        if($this->pveMetals->contains($metal)) {
            $this->pveMetals->removeElement($metal);
        }
        return $this->pveMetals;
    }


    /**
     * @return IslandTree[]|\Doctrine\Common\Collections\Collection
     */
    public function getPvpTrees()
    {
        return $this->pvpTrees;
    }

    /**
     * @param IslandTree[]|\Doctrine\Common\Collections\Collection $trees
     */
    public function setPvpTrees($trees)
    {
        $this->pvpTrees = new ArrayCollection();
        foreach($trees as $tree) {
            $this->addPvpTree($tree);
        }
    }

    /**
     * @param IslandTree $tree
     * @return \Doctrine\Common\Collections\Collection|IslandTree[]
     */
    public function addPvpTree($tree)
    {
        $tree->addPveIsland($this);
        if(!$this->pvpTrees->contains($tree)) {
            $this->pvpTrees->add($tree);
        }
        return $this->pvpTrees;
    }

    /**
     * @param IslandTree $tree
     * @return \Doctrine\Common\Collections\Collection|IslandTree[]
     */
    public function removePvpTree($tree)
    {
        if($this->pvpTrees->contains($tree)) {
            $this->pvpTrees->removeElement($tree);
        }
        return $this->pvpTrees;
    }

    /**
     * @return IslandMetal[]|\Doctrine\Common\Collections\Collection
     */
    public function getPvpMetals()
    {
        return $this->pvpMetals;
    }

    /**
     * @param IslandMetal[]|\Doctrine\Common\Collections\Collection $metals
     */
    public function setPvpMetals($metals)
    {
        $this->pvpMetals = new ArrayCollection();
        foreach($metals as $metal) {
            $this->addPvpMetal($metal);
        }
    }

    /**
     * @param IslandMetal $metal
     * @return \Doctrine\Common\Collections\Collection|IslandMetal[]
     */
    public function addPvpMetal($metal)
    {
        $metal->addPveIsland($this);
        if(!$this->pvpMetals->contains($metal)) {
            $this->pvpMetals->add($metal);
        }
        return $this->pvpMetals;
    }

    /**
     * @param IslandMetal $metal
     * @return \Doctrine\Common\Collections\Collection|IslandMetal[]
     */
    public function removePvpMetal($metal)
    {
        if($this->pvpMetals->contains($metal)) {
            $this->pvpMetals->removeElement($metal);
        }
        return $this->pvpMetals;
    }

    /**
     * @return IslandCreator
     */
    public function getCreator(): ?IslandCreator
    {
        return $this->creator;
    }

    /**
     * @param IslandCreator $creator
     */
    public function setCreator($creator)
    {
        $this->creator = $creator;
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
    public function setWorkshopUrl(string $workshopUrl): void
    {
        $this->workshopUrl = $workshopUrl;
    }

    /**
     * @return User
     */
    public function getSurveyCreatedBy()
    {
        return $this->surveyCreatedBy;
    }

    /**
     * @param User $surveyCreatedBy
     */
    public function setSurveyCreatedBy($surveyCreatedBy)
    {
        $this->surveyCreatedBy = $surveyCreatedBy;
    }

    /**
     * @return User
     */
    public function getSurveyUpdatedBy()
    {
        return $this->surveyUpdatedBy;
    }

    /**
     * @param User $surveyUpdatedBy
     */
    public function setSurveyUpdatedBy($surveyUpdatedBy)
    {
        $this->surveyUpdatedBy = $surveyUpdatedBy;
    }

    /**
     * @return bool
     */
    public function isTurrets()
    {
        return $this->turrets;
    }

    /**
     * @param bool $turrets
     */
    public function setTurrets($turrets)
    {
        $this->turrets = $turrets;
    }

    /**
     * @return bool
     */
    public function isSpikes()
    {
        return $this->spikes;
    }

    /**
     * @param bool $spikes
     */
    public function setSpikes($spikes)
    {
        $this->spikes = $spikes;
    }

    /**
     * @return bool
     */
    public function isNonGrappleWalls()
    {
        return $this->nonGrappleWalls;
    }

    /**
     * @param bool $nonGrappleWalls
     */
    public function setNonGrappleWalls($nonGrappleWalls)
    {
        $this->nonGrappleWalls = $nonGrappleWalls;
    }

    public function getLeaflet(){}
    public function setLeaflet($data){}

    public function __toString()
    {
        if($this->getName() && $this->getNickname()) {
            return $this->getName() . ' ('.$this->getNickname().')';
        }
        if($this->getName()) {
            return $this->getName();
        }
        return "New island";
    }

}
