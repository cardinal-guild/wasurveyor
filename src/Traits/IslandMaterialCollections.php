<?php


namespace App\Traits;


use App\Entity\IslandPVEMetal;
use App\Entity\IslandPVETree;
use App\Entity\IslandPVPMetal;
use App\Entity\IslandPVPTree;
use Doctrine\Common\Collections\ArrayCollection;

trait IslandMaterialCollections
{
    /**
     * @return IslandPVEMetal[]|\Doctrine\Common\Collections\Collection
     */
    public function getPveMetals()
    {
        return $this->pveMetals;
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
     * @return IslandPVETree[]|\Doctrine\Common\Collections\Collection
     */
    public function getPveTrees()
    {
        return $this->pveTrees;
    }

    /**
     * @param IslandPVETree[]|\Doctrine\Common\Collections\Collection $trees
     */
    public function setPveTrees($trees)
    {
        $this->pveTrees = new ArrayCollection();
        foreach($trees as $tree) {
            $this->addPveTree($tree);
        }
    }

    /**
     * @param IslandPVETree $tree
     * @return \Doctrine\Common\Collections\Collection|IslandPVETree[]
     */
    public function addPveTree($tree)
    {
        if(!$this->pveTrees->contains($tree)) {
            $this->pveTrees->add($tree);
        }
        return $this->pveTrees;
    }

    /**
     * @param IslandPVETree $tree
     * @return \Doctrine\Common\Collections\Collection|IslandPVETree[]
     */
    public function removePveTree($tree)
    {
        if($this->pveTrees->contains($tree)) {
            $this->pveTrees->removeElement($tree);
        }
        return $this->pveTrees;
    }


    /**
     * @return IslandPVPMetal[]|\Doctrine\Common\Collections\Collection
     */
    public function getPvpMetals()
    {
        return $this->pvpMetals;
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

    /**
     * @return IslandPVPTree[]|\Doctrine\Common\Collections\Collection
     */
    public function getPvpTrees()
    {
        return $this->pvpTrees;
    }

    /**
     * @param IslandPVPTree[]|\Doctrine\Common\Collections\Collection $trees
     */
    public function setPvpTrees($trees)
    {
        $this->pvpTrees = new ArrayCollection();
        foreach($trees as $tree) {
            $this->addPvpTree($tree);
        }
    }

    /**
     * @param IslandPVPTree $tree
     * @return \Doctrine\Common\Collections\Collection|IslandPVPTree[]
     */
    public function addPvpTree($tree)
    {
        if(!$this->pvpTrees->contains($tree)) {
            $this->pvpTrees->add($tree);
        }
        return $this->pvpTrees;
    }

    /**
     * @param IslandPVPTree $tree
     * @return \Doctrine\Common\Collections\Collection|IslandPVPTree[]
     */
    public function removePvpTree($tree)
    {
        if($this->pvpTrees->contains($tree)) {
            $this->pvpTrees->removeElement($tree);
        }
        return $this->pvpTrees;
    }
}
