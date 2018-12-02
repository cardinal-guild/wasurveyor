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
     * @Assert\NotBlank()
     * @ORM\ManyToOne(targetEntity="App\Entity\Island", inversedBy="reports")
     * @ORM\JoinColumn(name="island_id", referencedColumnName="id")
     */
    protected $island;


    /**
     * @Assert\Count(min="1",minMessage="At least one metal is required for the report")
     * @var \Doctrine\Common\Collections\Collection|ReportMetal[]
     * @ORM\OneToMany(targetEntity="App\Entity\ReportMetal", mappedBy="report", cascade={"persist","remove"}, orphanRemoval=true)
     */
    protected $metals;

    /**
     * @var string
     * @Assert\Ip()
     * @ORM\Column(type="string")
     */
    protected $ipAddress;

    /**
     * @var integer
     * @Assert\NotBlank()
     * @ORM\Column(type="smallint")
     */
    protected $mode = self::PVE;

    use TimestampableEntity;

    public function __construct()
    {
        $this->metals = new ArrayCollection();
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
     * @return int
     */
    public function getMode(): ?int
    {
        return $this->mode;
    }

    /**
     * @param int $mode
     */
    public function setMode($mode)
    {
        if(is_string($mode)) {
            if($mode === 'pve') {
                $mode = self::PVE;
            } else {
                $mode = self::PVP;
            }
        }
        $this->mode = $mode;
    }

    public function __toString()
    {
        if($this->getIsland()) {
            return "Report for ".$this->getIsland()->__toString();
        }
        return "New report";
    }


}
