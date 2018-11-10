<?php

namespace App\Entity;

use Cocur\Slugify\Slugify;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection as ArrayCollection;
use Gedmo\Mapping\Annotation as Gedmo;
use Knp\DoctrineBehaviors\Model\Translatable\Translatable;

/**
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="App\Repository\IslandRepository")
 * @ORM\HasLifecycleCallbacks
 * @Gedmo\SoftDeleteable(fieldName="deletedAt")
 */
class Island
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
     * @ORM\Column(length=128, nullable=true)
     */
    protected $nickname;

    /**
     * @var string
     * @ORM\Column(length=128)
     */
    protected $slug;

    /**
     * @var float
     * @ORM\Column(type="decimal", scale=2)
     */
    protected $lat;

    /**
     * @var float
     * @ORM\Column(type="decimal", scale=2)
     */
    protected $lng;

    /**
     * @var boolean
     * @ORM\Column(type="boolean")
     */
    protected $revivors = false;

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
        $this->setSlug($this->__toString());
    }

    /**
     * @return string
     */
    public function getNickname(): ?string
    {
        return $this->nickname;
    }

    /**
     * @param string $nickname
     */
    public function setNickname(string $nickname): void
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
    public function getLat(): ?float
    {
        return $this->lat;
    }

    /**
     * @param float $lat
     */
    public function setLat(float $lat): void
    {
        $this->lat = $lat;
    }

    /**
     * @return float
     */
    public function getLng(): ?float
    {
        return $this->lng;
    }

    /**
     * @param float $lng
     */
    public function setLng(float $lng): void
    {
        $this->lng = $lng;
    }

    /**
     * @return bool
     */
    public function isRevivors(): bool
    {
        return $this->revivors;
    }

    /**
     * @param bool $revivors
     */
    public function setRevivors(bool $revivors): void
    {
        $this->revivors = $revivors;
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
