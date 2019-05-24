<?php


namespace App\Controller;


use App\Entity\Island;
use App\Entity\IslandImage;
use App\Entity\Report;
use App\Entity\TCData;
use App\Entity\Alliance;
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
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
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
     * Post for Bossa tc info
     *
     * @Route("/bossa/island/info.{_format}", methods={"POST"}, defaults={ "_format": "json" })
     * @SWG\Response(
     *      response=200,
     *      description="Post api for tc updates"
     * )
     * @SWG\Parameter( name="Authorization", in="header", required=true, type="string", default="Bearer TOKEN", description="Bossa Authorization key" )
     * @SWG\Tag(name="TC API")
     * @View()
     */
    public function updateInfo(Request $request)
    {
        # $params = $request->request;

    	$bossaTcChannel = $this->getParameter('bossa_tc_channel');
        $logger = $this->get('monolog.logger.bossa');
        $uLogger = $this->get('monolog.logger.tc_updates');
        $logger->info(json_encode($request->request->all()));

        $em = $this->getDoctrine()->getManager();

        $islands = $em->getRepository('App:Island');
        $alliances = $em->getRepository('App:Alliance');

	    if (!$request->request->has('Region')) {
		    return $this->view('No region provided');
        }

        $responses = array();
        
        foreach(array_keys($request->request->get('IslandDatas')) as $key) {
            $islandId = explode("@", $key)[0];
            $params = new ParameterBag(
                array(
                    "alliance_name"=>$request->request->get('IslandDatas')[$key]['AllianceName'],
                    "island_name"=>$request->request->get('IslandDatas')[$key]['TctName'],
                    "timestamp"=>time()
                    )
            );
            $island = $islands->findOneBy(array("guid" => $islandId));
            if (!$island) {
                array_push($responses, $islandId." is not a valid island id");
                continue;
            }

            // add server checks when we know what they are called
            $tcData = $island->getPtsTC();

            if ($tcData &&
                $tcData->getAlliance() &&
                $tcData->getAllianceName() == "" &&
                $params->get('alliance_name') == "Unclaimed" &&
                $tcData->getTowerName() == "" &&
                $params->get('island_name') == "None") {
                    array_push($responses, "Request for ".$island->getName()." was a duplicate");
                    continue;
                }

            if (!$tcData) {
                $tcData = new TCData();
                $em->persist($tcData);
                $island->setPtsTc($tcData);
            }

            $tcData->addToHistory(json_encode($params->all()));

            $prevOwner = $tcData->getAllianceName() ? $tcData->getAllianceName() : 'Unclaimed';

            if ($params->get('alliance_name') == "Unclaimed") { //remove alliance
                $tcData->setAllianceName("");
                $tcData->setTowerName("");
                $tcData->setAlliance(null);
                $uLogger->info('Removed alliance from '.$island->getName());
                array_push($responses, 'Removed alliance from '.$island->getName());
            }
            else if ( // only change name
                $tcData->getTowerName() != $params->get('island_name') &&
                $tcData->getAllianceName() == $params->get('alliance_name')
            ) {
                $tcData->setTowerName($params->get('island_name'));
                $uLogger->info("Renamed tower for ".$params->get('alliance_name')." to ".$params->get('island_name'));
                array_push($responses, "Renamed tower for ".$params->get('alliance_name')." to ".$params->get('island_name'));
                continue;
            }
            else {
                $alliance = $tcData->getAlliance();

                if (!$alliance || $alliance->getName() != $params->get('alliance_name')) {
                    $alliance = $alliances->findOneBy(array("name"=>$params->get('alliance_name')));
                    if (!$alliance) {
                        $alliance = new Alliance();
                        $alliance->setName($params->get('alliance_name'));
                        $em->persist($alliance);
                    }
                    $uLogger->info("Added the alliance ".$params->get('alliance_name'));
                    $tcData->setAlliance($alliance);
                }

                $tcData->setAllianceName($params->get('alliance_name'));
                $tcData->setTowerName($params->get('island_name'));

                $uLogger->info("Updated alliance (".$params->get('alliance_name').") and tower name (".$params->get('island_name').")");
                array_push($responses, "Updated alliance (".$params->get('alliance_name').") and tower name (".$params->get('island_name').")");
            }

            /** @var CacheManager */
            $imagineCacheManager = $this->get('liip_imagine.cache.manager');

            /** @var UploaderHelper */
            $uploadHelper = $this->get('vich_uploader.templating.helper.uploader_helper');

            $image = $island->getImages()->first();

            $url = $imagineCacheManager->getBrowserPath($uploadHelper->asset($image, 'imageFile'), 'island_popup');


            $post = json_encode([
                "embeds" => [
                    [
                        "title" => $island->getName(),
                        "url" => "https://map.cardinalguild.com/"."pvp"."/".$island->getId(), // change pvp to server or make pts link to one of the modes
                        "type" => "rich",
                        "author" => [
                            "name" => strtoupper('pts') //TODO: replace with $mode var
                        ],
                        "thumbnail" => [
                            "url" => $url //url will be wrong for local development
                        ],
                        "timestamp" => date('c'),
                        "fields" => [
                            [
                                "name" => "Previous Owner",
                                "value" => $prevOwner,
                                "inline" => true
                            ],
                            [
                                "name" => "New Owner",
                                "value" => $params->get('alliance_name'),
                                "inline" => true
                            ]
                        ]
                    ]
                ]
            ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

            $ch = curl_init();

            curl_setopt_array($ch, [
                CURLOPT_URL => $bossaTcChannel,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $post,
                CURLOPT_HTTPHEADER => [
                    "Length" => strlen($post),
                    "Content-Type" => "application/json"
                ]
            ]);
            $response = curl_exec($ch);
            curl_close($ch);

        }

        $em->flush();
        
	    return $this->view($responses);
    }

    /**
     * Returns tc history for a specific island
     *
     * @Route("/islands/{id}/{mode}/history.{_format}", methods={"GET"}, defaults={ "_format": "json"})
     * @SWG\Response(
     *      response=200,
     *      description="Returns the Territory Control history for an island"
     * )
     * @SWG\Tag(name="TC History")
     * @View()
     */
    public function getTCHistory($id, $mode)
    {
        $em = $this->getDoctrine()->getManager();

        /**
         * @var $island Island
         */
        $island = $em->getRepository('App:Island')->findOneBy(array('id' => $id));
        if(!$island) {
            throw new BadRequestHttpException('Island not found!');
        }
        if ($mode !== 'pts') {
            throw new BadRequestHttpException($mode." is not valid mode!");
        }
        if ($island->getPtsTC()) {

            $data = array();
            foreach($island->getPtsTc()->getHistory()->getHistory() as $h) {
                array_push($data, json_decode($h));
            }
            return new JsonResponse($data);
        }
    }

    /**
     * Returns oEmbed json for an island
     *
     * @Route("/islands/{id}/oEmbed.{_format}", methods={"GET", "OPTIONS"}, defaults={ "_format": "json"})
     * @SWG\Response(
     *      response=200,
     *      description="Returns oEmbed.json for an island"
     * )
     * @SWG\Tag(name="Island oEmbed")
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
     * @Route("/creators.{_format}", methods={"GET", "OPTIONS"}, defaults={ "_format": "json"})
     * @SWG\Response(
     *      response=200,
     *      description="Returns all island creators"
     * )
     * @SWG\Tag(name="Creators")
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
     * @Route("/islands.{_format}", methods={"GET","OPTIONS"}, defaults={ "_format": "json" })
     * @SWG\Response(
     *     response=200,
     *     description="Returns all marker data for islands, if query input given, gives islands by search"
     * )
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
            if ($island->getPtsTc() && $island->getPtsTc()->getAlliance()) {
                $tcData = $island->getPtsTc();
                $ptsTCData = [
                    'name'=>$tcData->getTowerName(),
                    'alliance'=>$tcData->getAllianceName()
                ];
                $data['pts_tc_data'] = $ptsTCData;
            }
	        // if($island->getPveTower() && $island->getPveTower()->getAlliance()) {
		    //     $pveTower = $island->getPveTower();
	        // 	$pveTowerData = [];
		    //     $pveTowerData['name'] = $pveTower->getName();
		    //     $pveTowerData['alliance'] = $pveTower->getAlliance()->getName();
		    //     $data['pve_tower'] = $pveTowerData;
	        // }
	        // if($island->getPvpTower() && $island->getPvpTower()->getAlliance()) {
		    //     $pvpTower = $island->getPvpTower();
		    //     $pvpTowerData = [];
		    //     $pvpTowerData['name'] = $pvpTower->getName();
		    //     $pvpTowerData['alliance'] = $pvpTower->getAlliance()->getName();
		    //     $data['pvp_tower'] = $pvpTowerData;
	        // }
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
     * @Route("/metaltypes.{_format}", methods={"GET","OPTIONS"}, defaults={ "_format": "json" })
     * @SWG\Response(
     *     response=200,
     *     description="Returns all metaltypes"
     * )
     * @SWG\Tag(name="Types")
     * @View()
     */
    public function getAllMetalTypes(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        return $em->getRepository('App:MetalType')->findAll();
    }
}
