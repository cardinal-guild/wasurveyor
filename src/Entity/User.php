<?php


namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\EntityManager;
use Sonata\UserBundle\Entity\BaseUser as BaseUser;
use Symfony\Component\Validator\Constraints as Assert;
// Include Library Namespaces
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\Exception\UnsatisfiedDependencyException;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @ORM\Table(name="user",indexes={@ORM\Index(name="username_idx", columns={"username"})})
 */
class User extends BaseUser
{

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
     * @var \Doctrine\Common\Collections\Collection|Character[]
     * @ORM\OneToMany(targetEntity="App\Entity\Character", mappedBy="owner", fetch="EXTRA_LAZY")
     */
    protected $characters;

    /**
     * @var string
     * @ORM\Column(type="string")
     * @Assert\Length(min=36, minMessage="UUID must have at least 36 characters")
     * @Assert\NotBlank()
     * @Assert\Regex(
     *    pattern= "/[0-9A-F]{8}-[0-9A-F]{4}-4[0-9A-F]{3}-[89AB][0-9A-F]{3}-[0-9A-F]{12}/",
     *    match=   false,
     *    message= "Not a valid UUID"
     * )
     */
    protected $apiToken;

    public function __construct()
    {
        parent::__construct();
        $this->apiToken = Uuid::uuid4()->toString();
    }

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

    /**
     * @return string
     */
    public function getApiToken()
    {
        return $this->apiToken;
    }

    /**
     * @param string $apiToken
     */
    public function setApiToken($apiToken)
    {
        $this->apiToken = $apiToken;
    }



}
