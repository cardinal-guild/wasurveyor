<?php


namespace App\Traits;


use App\Entity\IslandPVEMetal;
use App\Entity\IslandPVPMetal;
use Doctrine\Common\Collections\ArrayCollection;

trait IslandMetalCollections
{
    /**
     * @return IslandPVEMetal[]|\Doctrine\Common\Collections\Collection
     */
    public function getPveMetals()
    {
	    $iterator = $this->pveMetals->getIterator();
	    $iterator->uasort(function ($a, $b) {
		    return strcmp($a->getType()->getName(), $b->getType()->getName());
	    });
	    return new ArrayCollection(iterator_to_array($iterator));
    }

    /**
     * @param IslandPVEMetal[]|\Doctrine\Common\Collections\Collection $metals
     */
    public function setPveMetals($metals)
    {
        $this->pveMetals = new ArrayCollection();
        foreach($metals as $metal) {
            $this->addPveMetal($metal);
        }
    }

    /**
     * @param IslandPVEMetal $metal
     * @return \Doctrine\Common\Collections\Collection|IslandPVEMetal[]
     */
    public function addPveMetal($metal)
    {
        if(!$this->pveMetals->contains($metal)) {
            $this->pveMetals->add($metal);
        }
        return $this->pveMetals;
    }

    /**
     * @param IslandPVEMetal $metal
     * @return \Doctrine\Common\Collections\Collection|IslandPVEMetal[]
     */
    public function removePveMetal($metal)
    {
        if($this->pveMetals->contains($metal)) {
            $this->pveMetals->removeElement($metal);
        }
        return $this->pveMetals;
    }

    /**
     * @return IslandPVPMetal[]|\Doctrine\Common\Collections\Collection
     */
    public function getPvpMetals()
    {
	    $iterator = $this->pvpMetals->getIterator();
	    $iterator->uasort(function ($a, $b) {
		    return strcmp($a->getType()->getName(), $b->getType()->getName());
	    });
	    return new ArrayCollection(iterator_to_array($iterator));
    }

    /**
     * @param IslandPVPMetal[]|\Doctrine\Common\Collections\Collection $metals
     */
    public function setPvpMetals($metals)
    {
        $this->pvpMetals = new ArrayCollection();
        foreach($metals as $metal) {
            $this->addPvpMetal($metal);
        }
    }

    /**
     * @param IslandPVPMetal $metal
     * @return \Doctrine\Common\Collections\Collection|IslandPVPMetal[]
     */
    public function addPvpMetal($metal)
    {
        if(!$this->pvpMetals->contains($metal)) {
            $this->pvpMetals->add($metal);
        }
        return $this->pvpMetals;
    }

    /**
     * @param IslandPVPMetal $metal
     * @return \Doctrine\Common\Collections\Collection|IslandPVPMetal[]
     */
    public function removePvpMetal($metal)
    {
        if($this->pvpMetals->contains($metal)) {
            $this->pvpMetals->removeElement($metal);
        }
        return $this->pvpMetals;
    }
}
