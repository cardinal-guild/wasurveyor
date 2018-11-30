<?php


namespace App\Entity;

use App\Traits\IslandMetalTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use JMS\Serializer\Annotation as JMS;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="App\Repository\ReportMetalRepository")
 * @ORM\HasLifecycleCallbacks()
 * @JMS\ExclusionPolicy("all")
 */
class ReportMetal
{

    /**
     * @var integer
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @JMS\Expose()
     */
    public $id;

    /**
     * @var MetalType
     * @JMS\Expose
     * @ORM\ManyToOne(targetEntity="App\Entity\MetalType")
     * @ORM\JoinColumn(name="type_id", referencedColumnName="id", nullable=false)
     * @Assert\NotBlank()
     */
    public $type;

    /**
     * @var integer
     * @ORM\Column(type="integer")
     * @Assert\Range(min="1", max="10")
     */
    public $quality = 1;

    /**
     * @var Report
     * @ORM\ManyToOne(targetEntity="App\Entity\Report", inversedBy="metals")
     * @ORM\JoinColumn(name="report_id", referencedColumnName="id")
     */
    protected $report;

    /**
     * @return Island
     */
    public function getReport(): ?Report
    {
        return $this->report;
    }

    /**
     * @param Report $report
     * @return ReportMetal
     */
    public function setReport(Report $report): ?ReportMetal
    {
        $this->report = $report;
        return $this;
    }

    /**
     * @return MetalType
     */
    public function getType(): ?MetalType
    {
        return $this->type;
    }

    /**
     * @param MetalType $type
     */
    public function setType(MetalType $type)
    {
        $this->type = $type;
    }

    /**
     * @return int
     */
    public function getQuality(): ?int
    {
        return $this->quality;
    }

    /**
     * @param int $quality
     */
    public function setQuality(int $quality)
    {
        $this->quality = $quality;
    }

    use TimestampableEntity;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    public function __toString()
    {
        if($this->getType() && $this->getQuality()) {
            return $this->getType()->__toString().' Q'.$this->getQuality();
        }
        // TODO: Implement __toString() method.
        return "New Report Metal";
    }
}
