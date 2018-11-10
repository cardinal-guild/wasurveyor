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
    protected $published = true;

    /**
     * @var \Doctrine\Common\Collections\Collection|IslandImage[]
     * @ORM\OneToMany(targetEntity="App\Entity\IslandImage", mappedBy="island", cascade={"persist","remove"}, orphanRemoval=true)
     * @ORM\OrderBy({"position" = "ASC"})
     * @Assert\Count(min=1, minMessage="At least one picture is required.  Upload images in the media tab.")
     */
    protected $images;

    /**
     * @var Author
     * @JMS\Expose
     * @ORM\ManyToOne(targetEntity="App\Entity\Author", cascade={"persist"}, inversedBy="islands")
     * @ORM\JoinColumn(name="author_id", referencedColumnName="id", nullable=false)
     * @Assert\NotBlank()
     */
    protected $author;

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
     * @return Author
     */
    public function getAuthor(): ?Author
    {
        return $this->author;
    }

    /**
     * @param Author $author
     */
    public function setAuthor(Author $author): void
    {
        $this->author = $author;
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
