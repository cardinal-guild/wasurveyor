<?php


namespace App\Controller;


use App\Entity\Island;
use App\Repository\IslandRepository;
use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\Controller\FOSRestController;
use GeoJson\Feature\Feature;
use GeoJson\Feature\FeatureCollection;
use function MongoDB\BSON\toJSON;
use Nelmio\ApiDocBundle\Annotation\Model;
use Psr\Log\LoggerInterface;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class ApiController
 * @package App\Controller
 * @Route("/api")
 */
class ApiController extends FOSRestController
{
    /**
     * Returns all marker data for islands
     *
     * @Route("/islands.{_format}", methods={"GET"}, defaults={ "_format": "json" })
     * @SWG\Response(
     *     response=200,
     *     description="Returns all marker data for islands"
     * )
     * @SWG\Tag(name="islands")
     * @Cache(public=true, expires="-1 hours")
     */
    public function getIslandMarkersAction()
    {
        $em = $this->getDoctrine()->getManager();

        /**
         * @var IslandRepository $islandRepo
         */
        $islandRepo = $em->getRepository('App:Island');
        $islands = $islandRepo->getPublishedIslands();
        $crs = new \GeoJson\CoordinateReferenceSystem\Named('Simple');
        $box = new \GeoJson\BoundingBox([-9500, 9500, 0, 0]);

        $markers = [];
        /**
         * @var $island Island
         */
        foreach($islands as $island) {
            $point = new \GeoJson\Geometry\Point([round($island->getLat(),2), round($island->getLng(),2)]);



            $markers[] = new Feature($point, [
                'name'=>$island->getName(),
                'nickName'=>$island->getNickname(),
                'fullName'=>$island->__toString(),
                'slug'=>$island->getSlug(),
                'creator'=>$island->getCreator()->getName(),
                'creatorWorkshopUrl'=>$island->getCreator()->getWorkshopUrl(),
                'databanks'=>(integer)$island->getDatabanks(),
                'respawners'=>(bool)$island->isRespawners(),
                'cannons'=>(bool)$island->isCannons(),
                'dangerous'=>(bool)$island->isDangerous(),
                'turrets'=>(bool)$island->isTurrets(),
                'spikes'=>(bool)$island->isSpikes(),
                'nonGrappleWalls'=>(bool)$island->isNonGrappleWalls(),
                'workshopUrl'=>(bool)$island->getWorkshopUrl(),
            ], $island->getId());

        }
        $collection = new FeatureCollection($markers, $crs, $box);
        return $collection;

    }
}
