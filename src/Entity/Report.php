<?php


namespace App\Entity;

use App\Traits\IslandMetalTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use JMS\Serializer\Annotation as JMS;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity
 * @ORM\Table
 * @ORM\Entity(repositoryClass="App\Repository\ReportRepository")
 * @JMS\ExclusionPolicy("all")
 */
class Report
{
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
     * @var string
     * @ORM\Column(type="json", nullable=true)
     */
    protected $metals;

    /**
     * @var string
     * @ORM\Column(type="json", nullable=true)
     */
    protected $trees;

    use TimestampableEntity;

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
     * @return array
     */
    public function getMetals(): ?array
    {
        if($this->metals) {
            return json_decode($this->metals);
        }
        return null;
    }

    /**
     * @param array $metals
     */
    public function setMetals(array $metals)
    {
        $this->metals = json_encode($metals);
    }

    /**
     * @return array
     */
    public function getTrees(): ?array
    {

        if($this->trees) {
            return json_decode($this->trees);
        }
        return null;
    }

    /**
     * @param string $trees
     */
    public function setTrees(string $trees)
    {
        $this->trees = json_encode($trees);
    }

    public function __toString()
    {
        if($this->getIsland()) {
            return "Report for ".$this->getIsland()->__toString();
        }
        return "New report";
    }


}
