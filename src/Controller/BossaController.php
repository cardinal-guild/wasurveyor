<?php


namespace App\Controller;


use App\Entity\Island;
use App\Entity\IslandImage;
use App\Entity\Report;
use App\Entity\TCData;
use App\Entity\Alliance;
use App\Repository\AllianceRepository;
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
 * @Route("/api/bossa")
 */
class BossaController extends FOSRestController
{
	/**
	 * Post for Bossa tc info
	 *
	 * @Route("/island/info.{_format}", methods={"POST"}, defaults={ "_format": "json" })
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

		/**
		 * @var $islandRepo IslandRepository
		 */
		$islandRepo = $em->getRepository('App:Island');

		/**
		 * @var $allianceRepo AllianceRepository
		 */
		$allianceRepo = $em->getRepository('App:Alliance');

		if (!$request->request->has('Region')) {
			return $this->view('no region provided', 400);
		}
		if (!$request->request->has('IslandDatas')) {
			return $this->view('no island data given', 400);
		}

		$zeroedNames = ["unclaimed", "unnamed", "none"];
		$islandDatas = $request->request->get('IslandDatas');
		foreach ($islandDatas as $key => $islandData) {
			$islandId = (int)explode("@", $key)[0];
			if ($islandData['AllianceName'] && $islandData['TctName']) {
				$allianceName = $islandData['AllianceName'];
				$islandName = $islandData['TctName'];

				/**
				 * @var Island $island
				 */
				$island = $islandRepo->findOneBy(["guid" => $islandId]);
				if ($island && $island->getTier() > 2) {
					if (in_array(strtolower($islandData['TctName']), $zeroedNames) && $island->getTowerName() !== null) {
						$island->setTowerName(null);
					} else {
						$island->setTowerName($islandData['TctName']);
					}
					if (in_array(strtolower($islandData['AllianceName']), $zeroedNames) && $island->getAlliance() !== null) {
						$island->setAlliance(null);
					} else {
						/**
						 * @var $alliance Alliance
						 */
						$alliance = $allianceRepo->findOneBy(['name'=>trim($islandData['AllianceName'])]);
						if(!$alliance) {
							$alliance = new Alliance();
						}
						$alliance->setName(trim($islandData['AllianceName']));
						$island->setAlliance($alliance);
					}
					$em->persist($island);
				}
			}
		}
		$em->flush();
		return $this->view('ok');
	}
//			if ($tcData && $tcData->getAlliance() && in_array(strtolower($params->get('island_name')), $zeroedIslandNames)) {
//				(
//					$tcData->getAllianceName() === "" &&
//					$params->get('alliance_name') === "Unclaimed" &&
//					$tcData->getTowerName() === "" &&
//					($params->get('island_name') === "None" || $params->get('island_name') === "Unnamed"))
//				)) ||
//				(
//					$tcData->getAllianceName() === $params->get('alliance_name') &&
//					$tcData->getTowerName() === $params->get('island_name')
//				)) {
//				$responses[] = "Request for ".$island->getName()." was a duplicate";
//				continue;
//			}
//
//			if (!$tcData) {
//				$tcData = new TCData();
//				$em->persist($tcData);
//				$island->setPtsTc($tcData);
//			}
//
//			$tcData->addToHistory(json_encode($params->all()));
//
//			$prevOwner = $tcData->getAllianceName() ? $tcData->getAllianceName() : 'Unclaimed';
//
//			if ($params->get('alliance_name') == "Unclaimed") { //remove alliance
//				$tcData->setAllianceName("");
//				$tcData->setTowerName("");
//				$tcData->setAlliance(null);
//				$uLogger->info('Removed alliance from '.$island->getName());
//				array_push($responses, 'Removed alliance from '.$island->getName());
//			}
//			else if ( // only change name
//				$tcData->getTowerName() != $params->get('island_name') &&
//				$tcData->getAllianceName() == $params->get('alliance_name')
//			) {
//				$tcData->setTowerName($params->get('island_name'));
//				$uLogger->info("Renamed tower for ".$params->get('alliance_name')." to ".$params->get('island_name'));
//				array_push($responses, "Renamed tower for ".$params->get('alliance_name')." to ".$params->get('island_name'));
//				$em->flush();
//				continue;
//			}
//			else {
//				$alliance = $tcData->getAlliance();
//
//				if (!$alliance || $alliance->getName() !== $params->get('alliance_name')) {
//					$alliance = $alliances->findOneBy(array("name"=>$params->get('alliance_name')));
//					if (!$alliance) {
//						$alliance = new Alliance();
//						$alliance->setName($params->get('alliance_name'));
//						$em->persist($alliance);
//						$uLogger->info("Added the alliance ".$params->get('alliance_name'));
//					}
//					$tcData->setAlliance($alliance);
//				}
//
//				$tcData->setAllianceName($params->get('alliance_name'));
//				$tcData->setTowerName($params->get('island_name'));
//
//				$uLogger->info("Updated alliance (".$params->get('alliance_name').") and tower name (".$params->get('island_name').")");
//				array_push($responses, "Updated alliance (".$params->get('alliance_name').") and tower name (".$params->get('island_name').")");
//			}
//
//			/** @var CacheManager */
//			$imagineCacheManager = $this->get('liip_imagine.cache.manager');
//
//			/** @var UploaderHelper */
//			$uploadHelper = $this->get('vich_uploader.templating.helper.uploader_helper');
//
//			$image = $island->getImages()->first();
//
//			$url = $imagineCacheManager->getBrowserPath($uploadHelper->asset($image, 'imageFile'), 'island_popup');
//
//			$post = json_encode([
//				"embeds" => [
//					[
//						"title" => $island->getName(),
//						"url" => "https://map.cardinalguild.com/"."pvp"."/".$island->getId(), // change pvp to server or make pts link to one of the modes
//						"type" => "rich",
//						"author" => [
//							"name" => strtoupper('pts') //TODO: replace with $mode var
//						],
//						"thumbnail" => [
//							"url" => $url //url will be wrong for local development
//						],
//						"timestamp" => date('c'),
//						"color" => $island->getTier() === 4 ? hexdec('f7c38f') : hexdec('e3c9f9'),
//						"fields" => [
//							[
//								"name" => "Previous Owner",
//								"value" => $prevOwner,
//								"inline" => true
//							],
//							[
//								"name" => "New Owner",
//								"value" => $params->get('alliance_name'),
//								"inline" => true
//							]
//						]
//					]
//				]
//			], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
//
//			$ch = curl_init();
//
//			curl_setopt_array($ch, [
//				CURLOPT_URL => $bossaTcChannel,
//				CURLOPT_POST => true,
//				CURLOPT_POSTFIELDS => $post,
//				CURLOPT_HTTPHEADER => [
//					"Length" => strlen($post),
//					"Content-Type" => "application/json"
//				]
//			]);
//			$response = curl_exec($ch);
//			curl_close($ch);
//			$em->flush();
//		}

//		return $this->view($responses);
//	}
}
