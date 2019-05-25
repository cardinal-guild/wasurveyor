<?php

namespace App\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\Routing\Annotation\Route;
use Swagger\Annotations as SWG;
use FOS\RestBundle\Controller\Annotations\View;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\TCData;
use App\Entity\Alliance;
use Vich\UploaderBundle\Templating\Helper\UploaderHelper;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;

/**
 * @Route("/api")
 */
class BossaController extends FOSRestController
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
            $island = $islands->findOneBy(["guid" => $islandId]);
            if (!$island) {
                $responses[] = $islandId." does not match an island with a guid";
                $uLogger->warning($islandId." is an UNKNOWN ID");
                continue;
            }
            if ($island->getTier() <= 2) {
                $responses[] = $islandId." is not in tier 3 or tier 4";
                continue;
            }

            // add server checks when we know what they are called
            $tcData = $island->getPtsTC();

            if (!$tcData) {
                $tcData = new TCData();
                $em->persist($tcData);
                $island->setPtsTc($tcData);
            }

            if (
                $tcData->getAllianceName() === $params->get('alliance_name') &&
                $tcData->getTowerName() === $params->get('island_name')
            ) {
                $responses[] = "Request for ".$island->getName()." was a duplicate";
                continue;
            }

            $tcData->addToHistory(json_encode($params->all()));

            $prevOwner = $tcData->getAllianceName(); //needed for webhook

            if ($params->get('alliance_name') == "Unclaimed") { //remove alliance
                $tcData->setAllianceName("Unclaimed");
                $tcData->setTowerName("None");
                $tcData->setAlliance(null);
                $uLogger->info('Removed alliance from '.$island->getName());
                $responses[] = 'Removed alliance from '.$island->getName();
            }
            else if (
                $tcData->getTowerName() != $params->get('island_name') &&
                $tcData->getAllianceName() == $params->get('alliance_name')
            ) { // only name change
                $tcData->setTowerName($params->get('island_name'));
                $uLogger->info("Renamed tower for ".$params->get('alliance_name')." to ".$params->get('island_name'));
                $responses[] = "Renamed tower for ".$params->get('alliance_name')." to ".$params->get('island_name');
                $em->flush();
                continue;
            }
            else { // new alliance on island
                $alliance = $tcData->getAlliance();

                if (!$alliance || $alliance->getName() !== $params->get('alliance_name')) {
                    $alliance = $alliances->findOneBy(array("name"=>$params->get('alliance_name')));
                    if (!$alliance) {
                        $alliance = new Alliance();
                        $alliance->setName($params->get('alliance_name'));
                        $em->persist($alliance);
                        $uLogger->info("Added the alliance ".$params->get('alliance_name'));
                    }
                    $tcData->setAlliance($alliance);
                }

                $tcData->setAllianceName($params->get('alliance_name'));
                $tcData->setTowerName($params->get('island_name'));

                $uLogger->info("Updated alliance ".$params->get('alliance_name')." for ".$island->getName());
                $responses[] = "Updated alliance ".$params->get('alliance_name')." for ".$island->getName();
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
                        "color" => $island->getTier() === 4 ? hexdec('f7c38f') : hexdec('e3c9f9'),
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
            $em->flush();
        }
        
	    return $this->view($responses);
    }
}
