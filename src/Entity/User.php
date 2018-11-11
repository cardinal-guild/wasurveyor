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
     * @ORM\Column(type="string")
     */
    protected $steamUid;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    protected $steamData;

    /**
     * @return string
     */
    public function getSteamUid()
    {
        return $this->steamUid;
    }

    /**
     * @param string $steamUid
     */
    public function setSteamUid($steamUid)
    {
        $this->steamUid = $steamUid;
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
