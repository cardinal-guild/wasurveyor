<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TCHistoryRepository")
 */
class TCHistory
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    protected $history = [];

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getHistory(): ?array
    {
        return $this->history;
    }

    public function addToHistory($newEvent)
    {
        array_push($this->history, $newEvent);
    }
}