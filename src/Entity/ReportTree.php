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
 * @ORM\Entity(repositoryClass="App\Repository\ReportTreeRepository")
 * @ORM\HasLifecycleCallbacks()
 * @JMS\ExclusionPolicy("all")
 */
class ReportTree
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
     * @var TreeType
     * @JMS\Expose
     * @ORM\ManyToOne(targetEntity="App\Entity\TreeType")
     * @ORM\JoinColumn(name="type_id", referencedColumnName="id", nullable=false)
     * @Assert\NotBlank()
     */
    public $type;

    /**
     * @var Report
     * @ORM\ManyToOne(targetEntity="App\Entity\Report", inversedBy="trees")
     * @ORM\JoinColumn(name="report_id", referencedColumnName="id")
     */
    protected $report;

    use TimestampableEntity;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    /**
     * @return Report
     */
    public function getReport(): ?Report
    {
        return $this->report;
    }

    /**
     * @param Report $report
     * @return ReportTree
     */
    public function setReport(Report $report): ?ReportTree
    {
        $this->report = $report;
        return $this;
    }

    /**
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id)
    {
        $this->id = $id;
    }

    /**
     * @return TreeType
     */
    public function getType(): ?TreeType
    {
        return $this->type;
    }

    /**
     * @param TreeType $type
     */
    public function setType(TreeType $type)
    {
        $this->type = $type;
    }

    public function __toString()
    {
        if($this->getType()) {
            return $this->getType()->__toString();
        }
        // TODO: Implement __toString() method.
        return "New Report Tree";
    }
}
