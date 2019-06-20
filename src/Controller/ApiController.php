<?php


namespace App\Controller;


use App\Entity\Island;
use App\Entity\IslandImage;
use App\Entity\Alliance;
use App\Repository\AllianceRepository;
use App\Repository\IslandRepository;
use App\Repository\IslandTerritoryControlRepository;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\Controller\FOSRestController;
use GeoJson\Feature\Feature;
use GeoJson\Feature\FeatureCollection;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Vich\UploaderBundle\Templating\Helper\UploaderHelper;

/**
 * Class ApiController
 * @package App\Controller
 * @Route("/api")
 */
class ApiController extends FOSRestController
{
    /**
     * Returns tc history for a specific island
     *
     * @Route("/islands/{id}/{server}/history.{_format}", methods={"GET"}, defaults={ "_format": "json"})
     * @SWG\Response(response=200, description="Returns the Territory Control history for an island")
     * @SWG\Response(response=400, description="Server or ID is not valid")
     * @SWG\Response(response=404, description="No territory control defined for that island")
     * @SWG\Parameter(name="id", in="path", type="string", description="Island ID")
     * @SWG\Parameter(name="server", in="path", type="string", description="Server that the island is on")
     * @SWG\Tag(name="Islands")
     * @View()
     */
    public function getTCHistory($id, $server, IslandRepository $islandRepository, AllianceRepository $allianceRepository, IslandTerritoryControlRepository $territoryControlRepo, EntityManagerInterface $em)
    {
    	$servers = ['pve','pvp','pts'];
        /**
         * @var $island Island
         */
        $island = $islandRepository->findOneBy(array('id' => $id));
        if(!$island) {
            throw new BadRequestHttpException('Island not found!');
        }
        if (!in_array($server, $servers)) {
            throw new BadRequestHttpException($server." is not a valid server!");
        }
	    $logRepo = $em->getRepository('Gedmo\Loggable\Entity\LogEntry');

        $territoryControl = $territoryControlRepo->findOneBy(['server'=>$server, 'island'=>$island]);
	    if (!$territoryControl) {
		    throw new NotFoundHttpException("No territory control defined for island: ".$island->getName());
	    }

	    $logEntries = $logRepo->getLogEntries($territoryControl);

	    $history = [];

	    foreach($logEntries as $logEntry) {
	    	$data = $logEntry->getData();
	    	$timeStr = $logEntry->getLoggedAt()->format('c');
	    	if(array_key_exists('towerName', $data)) {
	    		if($data['towerName']) {
				    $history[] = ['time'=>$timeStr, 'change'=>"Tower name set to: ".$data['towerName']];
			    } else {
	    			$history[] = ['time'=>$timeStr, 'change'=>"Tower name cleared, set to Unnamed"];
			    }
		    }
		    if(array_key_exists('alliance', $data)) {
			    if($data['alliance'] && isset($data['alliance']['id'])) {
			    	$alliance = $allianceRepository->find($data['alliance']['id']);
			    	if($alliance) {
					    $history[] = ['time'=>$timeStr, 'change'=>"Alliance set to: ".$alliance->getName()];
				    }
			    } else {
				    $history[] = ['time'=>$timeStr, 'change'=>"Alliance cleared, set to Unclaimed"];
			    }
		    }
	    }

		return $this->view($history);
    }

    /**
     * Returns alliances
     * 
     * @Route("/alliances.{_format}", methods={"GET"}, defaults={"_format": "json"})
     * @SWG\Response(response=200, description="Returns a list of all alliances" )
     * @SWG\Parameter(name="id", in="query", type="string", description="Finds an alliance by an ID")
     * @SWG\Parameter(name="name", in="query", type="string", description="Finds a match (case insensitive) by alliance name")
     * @SWG\Tag(name="Alliances")
     * @View()
     */
    public function getAlliances(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $allianceRepo = $em->getRepository('App:Alliance');

        if ($request->query->count()) {
            $alliances = $allianceRepo->getAlliancesByQuery($request->query->all());
        }
        else {
            $alliances = $allianceRepo->findAll();
        }

        $data = [];
        foreach($alliances as $a) {
            $add = [
                "id" => $a->getId(),
                "name" => $a->getName(),
                "count" => count($a->getTerritories())
            ];
            $add['tc'] = [];
            foreach($a->getTerritories() as $tc) {
                $add['tc'][] = [
                    "island_name" => $tc->getIsland()->getName(),
                    "tower_name" => $tc->getTowerName()
                ];
            }
            $data[] = $add;
        }

        return $this->view($data);
    }

    /**
     * Returns oEmbed json for an island
     *
     * @Route("/islands/{id}/oEmbed.{_format}", methods={"GET"}, defaults={ "_format": "json"})
     * @SWG\Response(response=200, description="Returns oEmbed.json for an island (used for embeds on websites)")
     * @SWG\Response(response=400, description="Island not found")
     * @SWG\Parameter(name="id", in="path", type="string", description="ID of the island")
     * @SWG\Tag(name="Islands")
     * @View()
     */
    public function getIslandOEmbed($id)
    {
        $em = $this->getDoctrine()->getManager();

        /**
         * @var $island Island
         */
        $island = $em->getRepository('App:Island')->findOneBy(array('id' => $id));
        if(!$island) {
            throw new BadRequestHttpException('Island not found!');
        }
        $data = [
            'author_name'=>$island->getCreator()->getName(),
            'author_url'=>$island->getCreator()->getWorkshopUrl(),
            'type'=>'photo'
        ];
        return $data;
    }

    /**
     * Returns all island creators
     *
     * @Route("/creators.{_format}", methods={"GET"}, defaults={ "_format": "json"})
     * @SWG\Response(response=200, description="Returns all island creators")
     * @SWG\Tag(name="Islands")
     * @View()
     */
    public function getAllIslandCreators()
    {
        $em = $this->getDoctrine()->getManager();

        $creators = $em->getRepository('App:IslandCreator')->findAll();

        foreach($creators as $creator) {
            $data[] = [
                'id'=>$creator->getId(),
                'name'=>$creator->getName(),
                'workshopUrl'=>$creator->getWorkshopUrl()
            ];
        }
        return $data;
    }

    /**
     * Returns all marker data for islands, if query input given, gives islands by search
     *
     * @Route("/islands.{_format}", methods={"GET"}, defaults={ "_format": "json" })
     * @SWG\Response(response=200, description="Returns all marker data for islands, if query input given, gives islands by search")
     * @SWG\Parameter(name="id", in="query", type="string", description="Island ID")
     * @SWG\Parameter(name="tier", in="query", type="string", description="Tier of islands (format: 13, 34, 1234)")
     * @SWG\Parameter(name="quality", in="query", type="integer", description="Quality of metal (used with metal param)")
     * @SWG\Parameter(name="minquality", in="query", type="integer", description="Minimum quality (used with metal param)")
     * @SWG\Parameter(name="maxquality", in="query", type="integer", description="Maximum quality (used with metal param)")
     * @SWG\Parameter(name="tree", in="query", type="string", description="Type of trees")
     * @SWG\Parameter(name="creator", in="query", type="string", description="Made by creator")
     * @SWG\Parameter(name="island", in="query", type="string", description="Island name")
     * @SWG\Tag(name="Islands")
     * @Cache(public=true, expires="now", mustRevalidate=true)
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

        $territoryRepo =$em->getRepository('App:IslandTerritoryControl');

        if($request->query->count()) {
            $islands = $islandRepo->getPublishedIslandsByQuery($request->query->all());
        } else {
            $islands = $islandRepo->getPublishedIslands();
        }

        $intlDateFormatter = new \IntlDateFormatter(
            $request->getPreferredLanguage(),
            \IntlDateFormatter::MEDIUM,
            \IntlDateFormatter::MEDIUM
        );

        $markers = [];
        /**
         * @var $island Island
         */
        foreach($islands as $island) {
            $point = new \GeoJson\Geometry\Point([round($island->getLat(),2), round($island->getLng(),2)]);

            $data = [
                'id'=>$island->getId(),
                'name'=>$island->getName(),
                'nickName'=>$island->getNickname(),
                'fullName'=>$island->__toString(),
                'slug'=>$island->getSlug(),
                'key'=>$island->getId().'-'.$island->getSlug(),
                'type'=>$island->getType()?'kioki':'saborian',
                'tier'=>(integer)$island->getTier(),
                'databanks'=>(integer)$island->getDatabanks(),
                'altitude'=>(integer)$island->getAltitude(),
                'creator'=>$island->getCreator()->getName(),
                'creatorWorkshopUrl'=>$island->getCreator()->getWorkshopUrl(),
                'surveyCreatedBy'=>$island->getSurveyCreatedBy()->__toString(),
                'surveyUpdatedBy'=>$island->getSurveyUpdatedBy()->__toString(),
                'revivalChambers'=>(bool)$island->hasRevivalChambers(),
                'dangerous'=>(bool)$island->isDangerous(),
                'turrets'=>(bool)$island->hasTurrets(),
                'workshopUrl'=>$island->getWorkshopUrl(),
                'workshopId'=>$island->getGuid(),
                'createdAt'=>$intlDateFormatter->format($island->getCreatedAt()),
                'updatedAt'=>$intlDateFormatter->format($island->getUpdatedAt())
            ];
            
            $servers = ['pts', 'pvp', 'pve'];

            $data['tc'] = [];
            foreach($servers as $server) {
                $data['tc'][$server] = [];
                foreach($territoryRepo->findBy(['server' => $server, 'island' => $island]) as $tc) {
                    $data['tc'][$server] = [];
                    $data['tc'][$server]['alliance'] = $tc->getAllianceName();
                    $data['tc'][$server]['tower_name'] = $tc->getTowerName();
                }
            }

            $data['trees'] = [];
            foreach($island->getTrees() as $tree) {
                if($tree->__toString() !== "New Island Tree") {
                    $data['trees'][] = $tree->__toString();
                }
            }

            $data['pveMetals'] = [];
            foreach($island->getPveMetals() as $pveMetal) {
                $metal = [];
                $metal['type_id'] = $pveMetal->getType()->getId();
                $metal['name'] = $pveMetal->getType()->__toString();
                $metal['quality'] = $pveMetal->getQuality();
                $data['pveMetals'][] = $metal;
            }

            $data['pvpMetals'] = [];
            foreach($island->getPvpMetals() as $pvpMetal) {
                $metal = [];
                $metal['type_id'] = $pvpMetal->getType()->getId();
                $metal['name'] = $pvpMetal->getType()->__toString();
                $metal['quality'] = $pvpMetal->getQuality();
                $data['pvpMetals'][] = $metal;
            }

            /**
             * @var IslandImage $firstImage
             */
            $firstImage = $island->getImages()->first();
            $secondImage = $island->getImages()->get(1);


            if($firstImage) {
                $imagePath = $uploadHelper->asset($firstImage, 'imageFile');

                $data['imageIcon'] = $imagineCacheManager->getBrowserPath($imagePath, 'island_tile_small');
                $data['imageIconBig'] = $imagineCacheManager->getBrowserPath($imagePath, 'island_tile_big');
                if($secondImage) {
                    $secondImagePath = $uploadHelper->asset($secondImage, 'imageFile');
                    $data['imagePopup'] = $imagineCacheManager->getBrowserPath($secondImagePath, 'island_popup');
                    $data['imageMedium'] = $imagineCacheManager->getBrowserPath($secondImagePath, 'island_tile_4by3');
                    $data['imageLarge'] = $imagineCacheManager->getBrowserPath($secondImagePath, 'island_tile_16by9');
                    $data['imageOriginal'] = $request->getSchemeAndHttpHost().$secondImagePath;
                } else {
                    $data['imagePopup'] = $imagineCacheManager->getBrowserPath($imagePath, 'island_popup');
                    $data['imageMedium'] = $imagineCacheManager->getBrowserPath($imagePath, 'island_tile_4by3');
                    $data['imageLarge'] = $imagineCacheManager->getBrowserPath($imagePath, 'island_tile_16by9');
                    $data['imageOriginal'] = $request->getSchemeAndHttpHost().$imagePath;
                }
            }


            $markers[] = new Feature($point, $data, $island->getId());

        }
        $collection = new FeatureCollection($markers);
        return new JsonResponse($collection);

    }

    /**
     * Returns all metaltypes
     *
     * @Route("/metaltypes.{_format}", methods={"GET"}, defaults={ "_format": "json" })
     * @SWG\Response(response=200,description="Returns all metaltypes")
     * @SWG\Tag(name="Info")
     * @View()
     */
    public function getAllMetalTypes(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        return $em->getRepository('App:MetalType')->findAll();
    }
}
