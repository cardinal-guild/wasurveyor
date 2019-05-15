<?php


namespace App\Entity;

use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection as ArrayCollection;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use JMS\Serializer\Annotation as JMS;

/**
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="App\Repository\IslandImageRepository")
 * @ORM\HasLifecycleCallbacks()
 * @Vich\Uploadable()
 * @JMS\ExclusionPolicy("all")
 */
class IslandImage
{
    /**
     * @var integer
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @JMS\Expose()
     */
    protected $id;

    /**
     * @var File
     * @Vich\UploadableField(mapping="island_image", fileNameProperty="imageName", size="imageSize")
     */
    protected $imageFile;

    /**
     * @var string
     * @ORM\Column(type="string", length=255)
     * @JMS\Expose()
     */
    protected $imageName;

    /**
     * @var integer
     * @ORM\Column(type="integer")
     */
    protected $imageSize;

    /**
     * @var integer
     * @ORM\Column(type="integer")
     */
    protected $imageHeight = 0;

    /**
     * @var integer
     * @ORM\Column(type="integer")
     */
    protected $imageWidth = 0;

    /**
     * @var Island
     * @Gedmo\SortableGroup
     * @ORM\ManyToOne(targetEntity="App\Entity\Island", inversedBy="images")
     * @ORM\JoinColumn(name="island_id", referencedColumnName="id")
     */
    protected $island;

    /**
     * @var integer
     * @ORM\Column(type="smallint")
     * @Gedmo\SortablePosition
     */
    protected $position = 1;

    protected $preview = true;

    /**
     * ProjectImage constructor.
     * @param int $id
     */
    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    use TimestampableEntity;


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
     * @param File|\Symfony\Component\HttpFoundation\File\UploadedFile $image
     */
    public function setImageFile(?File $image = null)
    {
        $this->imageFile = $image;

        if ($image !== null && $image instanceof File) {
            try {
                list($width, $height) = getimagesize($image);
                $this->setImageWidth((int)$width);
                $this->setImageHeight((int)$height);
            } catch (\Exception $e) { };
            $this->updatedAt = new \DateTimeImmutable();
        }
    }

    public function getImageFile(): ?File
    {
        return $this->imageFile;
    }

    public function setImageName(?string $imageName)
    {
        $this->imageName = $imageName;
    }

    public function getImageName(): ?string
    {
        return $this->imageName;
    }

    public function setImageSize(?int $imageSize)
    {
        $this->imageSize = $imageSize;
    }

    public function getImageSize(): ?int
    {
        return $this->imageSize;
    }

    /**
     * @return Island
     */
    public function getIsland(): ?Island
    {
        return $this->island;
    }

    /**
     * @param Island $island
     */
    public function setIsland(Island $island)
    {
        $this->island = $island;
    }

    /**
     * @return int
     */
    public function getPosition(): ?int
    {
        return $this->position;
    }

    /**
     * @param int $position
     */
    public function setPosition($position)
    {
        $this->position = (int)$position;
    }

    /**
     * @return int
     */
    public function getImageHeight(): int
    {
        return $this->imageHeight;
    }

    /**
     * @param int $imageHeight
     */
    public function setImageHeight(int $imageHeight): void
    {
        $this->imageHeight = $imageHeight;
    }

    /**
     * @return int
     */
    public function getImageWidth(): int
    {
        return $this->imageWidth;
    }

    /**
     * @param int $imageWidth
     */
    public function setImageWidth(int $imageWidth): void
    {
        $this->imageWidth = $imageWidth;
    }
}
