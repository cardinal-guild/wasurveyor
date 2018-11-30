<?php


namespace App\Entity;

use App\Traits\IslandMetalTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use JMS\Serializer\Annotation as JMS;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table
 * @ORM\Entity(repositoryClass="App\Repository\ReportRepository")
 * @JMS\ExclusionPolicy("all")
 */
class Report
{
    const PVE = 0;
    const PVP = 1;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @JMS\Expose()
     */
    protected $id;

    /**
     * @var Island
     * @ORM\ManyToOne(targetEntity="App\Entity\Island", inversedBy="reports")
     * @ORM\JoinColumn(name="island_id", referencedColumnName="id")
     */
    protected $island;
    /**
     * @var boolean
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $revivalChambers = false;

    /**
     * @var boolean
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $dangerous = false;

    /**
     * @var boolean
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $turrets = false;

    /**
     * @var integer
     * @ORM\Column(type="smallint", nullable=true)
     * @Assert\Range(min="0", max="5")
     */
    protected $databanks;

    /**
     * @var \Doctrine\Common\Collections\Collection|ReportMetal[]
     * @ORM\OneToMany(targetEntity="App\Entity\ReportMetal", mappedBy="report", cascade={"persist","remove"}, orphanRemoval=true)
     */
    protected $metals;

    /**
     * @var \Doctrine\Common\Collections\Collection|ReportTree[]
     * @ORM\OneToMany(targetEntity="App\Entity\ReportTree", mappedBy="report", cascade={"persist","remove"}, orphanRemoval=true)
     */
    protected $trees;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    protected $name;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    protected $ipAddress;

    /**
     * @var boolean
     * @ORM\Column(type="boolean")
     */
    protected $override = false;

    /**
     * @var boolean
     * @ORM\Column(type="boolean")
     */
    protected $approved = false;

    /**
     * @var User
     * @JMS\Expose
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(referencedColumnName="id", nullable=true)
     */
    protected $approvedBy;

    /**
     * @var integer
     * @ORM\Column(type="smallint")
     */
    protected $mode = self::PVE;

    use TimestampableEntity;

    public function __construct()
    {
        $this->metals = new ArrayCollection();
        $this->trees = new ArrayCollection();
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
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return Island
     */
    public function getIsland()
    {
        return $this->island;
    }

    /**
     * @param Island $island
     */
    public function setIsland(?Island $island)
    {
        $this->island = $island;
    }

    /**
     * @return ReportMetal[]|\Doctrine\Common\Collections\Collection
     */
    public function getMetals()
    {
        return $this->metals;
    }

    /**
     * @param ReportMetal[]|\Doctrine\Common\Collections\Collection $metals
     * @return \Doctrine\Common\Collections\Collection|ReportMetal[]
     */
    public function setMetals($metals)
    {
        $this->metals = new ArrayCollection();
        foreach($metals as $metal) {
            $this->addMetal($metal);
        }
        return $this->metals;
    }

    /**
     * @param ReportMetal $metal
     * @return \Doctrine\Common\Collections\Collection|ReportMetal[]
     */
    public function addMetal(ReportMetal $metal)
    {
        $metal->setReport($this);
        if(!$this->metals->contains($metal)) {
            $this->metals->add($metal);
        }
        return $this->metals;
    }

    /**
     * @param ReportMetal $metal
     * @return \Doctrine\Common\Collections\Collection|ReportMetal[]
     */
    public function removeMetal(ReportMetal $metal)
    {
        if($this->metals->contains($metal)) {
            $this->metals->removeElement($metal);
        }
        return $this->metals;
    }

    /**
     * @return ReportTree[]|\Doctrine\Common\Collections\Collection
     */
    public function getTrees()
    {
        return $this->trees;
    }

    /**
     * @param ReportTree[]|\Doctrine\Common\Collections\Collection $trees
     * @return \Doctrine\Common\Collections\Collection|ReportTree[]
     */
    public function setTrees($trees)
    {
        $this->trees = new ArrayCollection();
        foreach($trees as $tree) {
            $this->addTree($tree);
        }
        return $this->trees;
    }

    /**
     * @param ReportTree $tree
     * @return \Doctrine\Common\Collections\Collection|ReportTree[]
     */
    public function addTree(ReportTree $tree)
    {
        $tree->setReport($this);
        if(!$this->trees->contains($tree)) {
            $this->trees->add($tree);
        }
        return $this->trees;
    }

    /**
     * @param ReportTree $tree
     * @return \Doctrine\Common\Collections\Collection|ReportTree[]
     */
    public function removeTree(ReportTree $tree)
    {
        if($this->trees->contains($tree)) {
            $this->trees->removeElement($tree);
        }
        return $this->trees;
    }

    /**
     * @return bool
     */
    public function hasRevivalChambers(): ?bool
    {
        return $this->revivalChambers;
    }

    /**
     * @param bool $revivalChambers
     */
    public function setRevivalChambers(bool $revivalChambers)
    {
        $this->revivalChambers = $revivalChambers;
    }

    /**
     * @return bool
     */
    public function isDangerous(): ?bool
    {
        return $this->dangerous;
    }

    /**
     * @param bool $dangerous
     */
    public function setDangerous(bool $dangerous)
    {
        $this->dangerous = $dangerous;
    }

    /**
     * @return bool
     */
    public function hasTurrets(): ?bool
    {
        return $this->turrets;
    }

    /**
     * @param bool $turrets
     */
    public function setTurrets(bool $turrets)
    {
        $this->turrets = $turrets;
    }


    /**
     * @return int
     */
    public function getDatabanks(): ?int
    {
        return $this->databanks;
    }

    /**
     * @param int $databanks
     */
    public function setDatabanks(int $databanks)
    {
        $this->databanks = $databanks;
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
     * @return Report
     */
    public function setName(string $name): ?Report
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getIpAddress(): ?string
    {
        return $this->ipAddress;
    }

    /**
     * @param string $ipAddress
     * @return Report
     */
    public function setIpAddress(string $ipAddress): ?Report
    {
        $this->ipAddress = $ipAddress;
        return $this;
    }

    /**
     * @return bool
     */
    public function isOverride(): ?bool
    {
        return $this->override;
    }

    /**
     * @param bool $override
     * @return Report
     */
    public function setOverride(bool $override): ?Report
    {
        $this->override = $override;
        return $this;
    }

    /**
     * @return bool
     */
    public function isApproved(): ?bool
    {
        return $this->approved;
    }

    /**
     * @param bool $approved
     * @return Report
     */
    public function setApproved(bool $approved): ?Report
    {
        $this->approved = $approved;
        return $this;
    }

    /**
     * @return int
     */
    public function getMode(): ?int
    {
        return $this->mode;
    }

    /**
     * @param int $mode
     */
    public function setMode(int $mode)
    {
        $this->mode = $mode;
    }

    /**
     * @return User
     */
    public function getApprovedBy(): ?User
    {
        return $this->approvedBy;
    }

    /**
     * @param User $approvedBy
     */
    public function setApprovedBy(User $approvedBy)
    {
        $this->approvedBy = $approvedBy;
    }

    public function __toString()
    {
        if($this->getIsland()) {
            return "Report for ".$this->getIsland()->__toString();
        }
        return "New report";
    }


}
