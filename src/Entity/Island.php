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
 * @ORM\Entity(repositoryClass="App\Repository\ProjectRepository")
 * @ORM\HasLifecycleCallbacks
 * @Gedmo\SoftDeleteable(fieldName="deletedAt")
 */
class Island
{
    /**
     * @ORM\Id
     * @ORM\Column(name="id", type="guid")
     * @ORM\GeneratedValue(strategy="UUID")
     */
    protected $id;

    /**
     * @ORM\Column(length=128)
     */
    protected $name;

    /**
     * @ORM\Column(type="decimal", precision=2)
     */
    protected $lat;

    /**
     * @ORM\Column(type="decimal", precision=2)
     */
    protected $lng;

    /**
     * @ORM\Column(length=128, nullable=true)
     */
    protected $nickName;

    /**
     * @ORM\Column(length=128)
     */
    protected $slug;

    /**
     * @var boolean
     * @ORM\Column(type="boolean")
     */
    protected $hasRevivors = false;

    /**
     * @var boolean
     * @ORM\Column(type="boolean")
     */
    protected $hasCannons = false;

    /**
     * @var boolean
     * @ORM\Column(type="boolean")
     */
    private $published = true;

    use TimestampableEntity;
    use SoftDeleteableEntity;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }


    public function __toString()
    {


        return "New island";
    }

}
