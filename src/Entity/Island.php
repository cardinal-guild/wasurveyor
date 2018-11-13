<?php

namespace App\Entity;

use App\Repository\IslandPVETreeRepository;
use App\Traits\IslandMaterialCollections;
use Cocur\Slugify\Slugify;
use Facebook\GraphNodes\Collection;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection as ArrayCollection;
use Gedmo\Mapping\Annotation as Gedmo;
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
     * @var integer
     * @ORM\Column(type="integer")
     * @Assert\Range(min="0", max="5")
     */
    protected $databanks = 0;

    /**
     * @var \Doctrine\Common\Collections\Collection|IslandImage[]
     * @ORM\OneToMany(targetEntity="App\Entity\IslandImage", mappedBy="island", cascade={"persist","remove"}, orphanRemoval=true)
     * @ORM\OrderBy({"position" = "ASC"})
     * @Assert\Count(min=1, minMessage="At least one picture is required.  Upload images in the media tab.")
     */
    protected $images;

    /**
     * @var \Doctrine\Common\Collections\Collection|IslandPVEMetal[]
     * @ORM\ManyToMany(targetEntity="IslandPVEMetal", cascade={"persist","remove"}, orphanRemoval=true, inversedBy="islands")
     * @ORM\JoinTable(name="island_pve_metals",
     *      joinColumns={@ORM\JoinColumn(name="island_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="metal_id", referencedColumnName="id")}
     * )
     * @JMS\Expose()
     */
    protected $pveMetals;

    /**
     * @var \Doctrine\Common\Collections\Collection|IslandPVETree[]
     * @ORM\ManyToMany(targetEntity="App\Entity\IslandPVETree", cascade={"persist","remove"}, orphanRemoval=true, inversedBy="islands")
     * @ORM\JoinTable(name="island_pve_trees",
     *      joinColumns={@ORM\JoinColumn(name="island_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="tree_id", referencedColumnName="id")}
     * )
     * @JMS\Expose()
     */
    protected $pveTrees;

    /**
     * @var \Doctrine\Common\Collections\Collection|IslandPVPMetal[]
     * @ORM\ManyToMany(targetEntity="IslandPVPMetal", cascade={"persist","remove"}, orphanRemoval=true, inversedBy="islands")
     * @ORM\JoinTable(name="island_pvp_metals",
     *      joinColumns={@ORM\JoinColumn(name="island_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="metal_id", referencedColumnName="id")}
     * )
     * @JMS\Expose()
     */
    protected $pvpMetals;

    /**
     * @var \Doctrine\Common\Collections\Collection|IslandPVPTree[]
     * @ORM\ManyToMany(targetEntity="App\Entity\IslandPVPTree", cascade={"persist","remove"}, orphanRemoval=true, inversedBy="islands")
     * @ORM\JoinTable(name="island_pvp_trees",
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
    use IslandMaterialCollections;

    public function __construct()
    {

        $this->images = new ArrayCollection();
        $this->pveMetals = new ArrayCollection();
        $this->pveTrees = new ArrayCollection();
        $this->pvpMetals = new ArrayCollection();
        $this->pvpTrees = new ArrayCollection();
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
        $this->lat = round($lat,2);
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
        $this->lng = round($lng,2);
    }

    /**
     * @return integer
     */
    public function getDatabanks()
    {
        return $this->databanks;
    }

    /**
     * @param integer $databanks
     */
    public function setDatabanks($databanks)
    {
        $this->databanks = $databanks;
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
