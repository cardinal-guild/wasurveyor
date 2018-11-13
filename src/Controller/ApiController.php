<?php


namespace App\Controller;


use App\Entity\Island;
use App\Entity\IslandImage;
use App\Repository\IslandRepository;
use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\Controller\FOSRestController;
use GeoJson\Feature\Feature;
use GeoJson\Feature\FeatureCollection;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
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
use Vich\UploaderBundle\Templating\Helper\UploaderHelper;

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
     * @Cache(public=true, expires="2 hours")
     */
    public function getIslandMarkersAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var CacheManager */
        $imagineCacheManager = $this->get('liip_imagine.cache.manager');

        /** @var UploaderHelper */
        $uploadHelper = $this->get('vich_uploader.templating.helper.uploader_helper');

        /**
         * @var IslandRepository $islandRepo
         */
        $islandRepo = $em->getRepository('App:Island');
        $islands = $islandRepo->getPublishedIslands();

        $markers = [];
        /**
         * @var $island Island
         */
        foreach($islands as $island) {
            $point = new \GeoJson\Geometry\Point([round($island->getLat(),2), round($island->getLng(),2)]);

            $data = [
                'name'=>$island->getName(),
                'nickName'=>$island->getNickname(),
                'fullName'=>$island->__toString(),
                'slug'=>$island->getSlug(),
                'databanks'=>(integer)$island->getDatabanks(),
                'altitude'=>(integer)$island->getAltitude(),
                'creator'=>$island->getCreator()->getName(),
                'creatorWorkshopUrl'=>$island->getCreator()->getWorkshopUrl(),
                'surveyCreatedBy'=>$island->getSurveyCreatedBy()->__toString(),
                'surveyUpdatedBy'=>$island->getSurveyCreatedBy()->__toString(),
                'respawners'=>(bool)$island->hasRespawners(),
                'dangerous'=>(bool)$island->isDangerous(),
                'turrets'=>(bool)$island->hasTurrets(),
                'spikes'=>(bool)$island->hasSpikes(),
                'nonGrappleWalls'=>(bool)$island->hasNonGrappleWalls(),
                'workshopUrl'=>$island->getWorkshopUrl()
            ];


            $pveMetals = [];
            foreach($island->getPveMetals() as $pveMetal) {
                $pveMetals[] = $pveMetal->__toString();
            }
            $pveTrees = [];
            foreach($island->getPveTrees() as $pveTree) {
                $pveTrees[] = $pveTree->__toString();
            }
            $data['pveMaterials'] = array_merge($pveMetals, $pveTrees);

            $pvpMetals = [];
            foreach($island->getPvpMetals() as $pvpMetal) {
                $pvpMetals[] = $pvpMetal->__toString();
            }
            $pvpTrees = [];
            foreach($island->getPvpTrees() as $pvpTree) {
                $pvpTrees[] = $pvpTree->__toString();
            }

            $data['pvpMaterials'] = array_merge($pvpMetals, $pvpTrees);

            /**
             * @var IslandImage $firstImage
             */
            $firstImage = $island->getImages()->first();

            if($firstImage) {
                $imagePath = $uploadHelper->asset($firstImage, 'imageFile');

                $data['imageIcon'] = $imagineCacheManager->getBrowserPath($imagePath, 'island_tile_small');
                $data['imageMedium'] = $imagineCacheManager->getBrowserPath($imagePath, 'island_tile_4by3');
                $data['imageLarge'] = $imagineCacheManager->getBrowserPath($imagePath, 'island_tile_16by9');
                $data['imageOriginal'] = $request->getSchemeAndHttpHost().$imagePath;
            }


            $markers[] = new Feature($point, $data, $island->getId());

        }
        $collection = new FeatureCollection($markers);
        return $collection;

    }
}
