<?php


namespace App\Controller;


use App\Entity\Island;
use App\Entity\IslandImage;
use App\Entity\Report;
use App\Repository\IslandRepository;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\Controller\FOSRestController;
use GeoJson\Feature\Feature;
use GeoJson\Feature\FeatureCollection;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use function MongoDB\BSON\toJSON;
use Nelmio\ApiDocBundle\Annotation\Model;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Vich\UploaderBundle\Templating\Helper\UploaderHelper;

/**
 * Class ApiController
 * @package App\Controller
 * @Route("/api/account")
 */
class AccountApiController extends FOSRestController
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }
    /**
     * Validate an account with api tokens
     *
     * @Route("/validate.{_format}", methods={"GET","OPTIONS"}, defaults={ "_format": "json" })
     * @SWG\Response(
     *     response=200,
     *     description="Validate an account with api tokens"
     * )
     * @SWG\Tag(name="Types")
     * @View()
     */
    public function validateApiKey(Request $request)
    {

        return true;
    }

    /**
     * Logout current authenticated user
     *
     * @Route("/logout.{_format}", methods={"GET","OPTIONS"}, defaults={ "_format": "json" })
     * @SWG\Response(
     *     response=200,
     *     description="Validate an account with api tokens"
     * )
     * @SWG\Tag(name="Types")
     * @View()
     */
    public function logout(Request $request)
    {
        $user = $this->getUser();
        if($user) {
            $user->setApiToken(Uuid::uuid4()->toString());
            $this->em->persist($user);
        }
        return true;
    }


//    /**
//     * Returns all metaltypes
//     *
//     * @Route("/metaltypes.{_format}", methods={"GET","OPTIONS"}, defaults={ "_format": "json" })
//     * @SWG\Response(
//     *     response=200,
//     *     description="Returns all metaltypes"
//     * )
//     * @SWG\Tag(name="Types")
//     * @View()
//     */
//    public function getAllMetalTypes(Request $request)
//    {
//        $em = $this->getDoctrine()->getManager();
//        return $em->getRepository('App:MetalType')->findAll();
//    }
}
