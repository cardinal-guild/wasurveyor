<?php


namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\EntityManager;
use Sonata\UserBundle\Entity\BaseUser as BaseUser;

/**
 * @ORM\Entity
 * @ORM\Table(name="user",indexes={@ORM\Index(name="username_idx", columns={"username"})})
 */
class User extends BaseUser
{
    public function __construct()
    {
        parent::__construct();
        // your code here
    }

    /**
     *
     * @var integer
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;



    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    protected $steamIdentifier;

    /**
     * @var string
     * @ORM\Column(type="text", nullable=true)
     */
    protected $steamData;

    /**
     * @return string
     */
    public function getSteamIdentifier()
    {
        return $this->steamIdentifier;
    }

    /**
     * @param string $steamIdentifier
     */
    public function setSteamIdentifier($steamIdentifier)
    {
        $this->steamIdentifier = $steamIdentifier;
    }

    /**
     * @return string
     */
    public function getSteamData()
    {
        return $this->steamData;
    }

    /**
     * @param string $steamData
     */
    public function setSteamData($steamData)
    {
        $this->steamData = $steamData;
    }


}
