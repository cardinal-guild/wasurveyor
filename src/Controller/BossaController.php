<?php


namespace App\Controller;


use App\Entity\Alliance;
use App\Entity\Island;
use App\Repository\AllianceRepository;
use App\Repository\IslandRepository;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\Controller\FOSRestController;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Vich\UploaderBundle\Templating\Helper\UploaderHelper;

/**
 * Class ApiController
 * @package App\Controller
 * @Route("/api/bossa")
 */
class BossaController extends FOSRestController
{

	/**
	 * @var CacheManager
	 */
	protected $cacheManager;

	/**
	 * @var UploaderHelper
	 */
	protected $uploadHelper;

	/**
	 * @var IslandRepository
	 */
	protected $islandRepo;

	/**
	 * @var AllianceRepository
	 */
	protected $allianceRepo;

	/**
	 * @var EntityManagerInterface
	 */
	protected $entityManager;

	public function __construct(CacheManager $cacheManager, UploaderHelper $uploaderHelper, IslandRepository $islandRepo, AllianceRepository $allianceRepo, EntityManagerInterface $entityManager)
	{
		$this->cacheManager = $cacheManager;
		$this->uploadHelper = $uploaderHelper;
		$this->islandRepo = $islandRepo;
		$this->allianceRepo = $allianceRepo;
		$this->entityManager = $entityManager;
	}

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
		$logger = $this->get('monolog.logger.bossa');
		$logger->info(json_encode($request->request->all()));

		if (!$request->request->has('Region')) {
			return $this->view('no region provided', 400);
		}
		if (!$request->request->has('IslandDatas')) {
			return $this->view('no island data given', 400);
		}

		$zeroedNames = ["unclaimed", "unnamed", "none"];
		$islandDatas = $request->request->get('IslandDatas');

		// Loop over all islands that came back in api call
		foreach ($islandDatas as $key => $islandData) {
			// Get the id of the steam workshop id of an island and integer format
			$islandId = (int)explode("@", $key)[0];
			// Double check if it has the required fields
			if ($islandData['AllianceName'] && $islandData['TctName']) {

				// Set variables
				$allianceName = $islandData['AllianceName'];
				$towerName = $islandData['TctName'];

				/**
				 * @var Island $island
				 */
				$island = $this->islandRepo->findOneBy(["guid" => $islandId]);

				// Check if island has the correct tier
				if ($island && $island->getTier() > 2) {
					// Store previous tower and alliance name for discord channel updates, even if nulled
					$prevAllianceName = $island->getAllianceName();
					// Get the tower name, if null returned, you get 'Unnamed' as string back
					$prevTowerName = $island->getTowerNameUnnamed();

					// Check if the island became unnamed or unclaimed
					if (in_array(strtolower($towerName), $zeroedNames)) {
						$island->setTowerName(null);
					} else {
						// Check if previous island name is not the same
						if ($island->getTowerName() !== $towerName) {
							$island->setTowerName($towerName);
						}
					}
					// Check if the alliance became unnamed or unclaimed
					if (in_array(strtolower($allianceName), $zeroedNames)) {
						$island->setAlliance(null);
					} else {
						// Check if previous alliance is not the same
						if (!$island->getAlliance() || $island->getAllianceName() !== $allianceName) {
							/**
							 * @var $alliance Alliance
							 */
							$alliance = $this->allianceRepo->findOneBy(['name' => trim($allianceName)]);
							if (!$alliance) {
								$alliance = new Alliance();
							}
							$alliance->setName(trim($allianceName));
							$island->setAlliance($alliance);
						}
					}
					$this->entityManager->persist($island);
					// Flush in every looped item because most times the api call does not contain a lot of changes
					$this->entityManager->flush();

					// Send island update to discord
					$this->sendDiscordUpdate($island, $prevTowerName, $prevAllianceName);
				}
			}
		}
		return $this->view('ok');
	}

	private function sendDiscordUpdate(Island $island, $prevTowerName = '', $prevAllianceName = '')
	{
		$bossaTcChannel = $this->getParameter('bossa_tc_channel');
		$uLogger = $this->get('monolog.logger.tc_updates');

		$image = $island->getImages()->first();

		$url = $this->cacheManager->getBrowserPath($this->uploadHelper->asset($image, 'imageFile'), 'island_popup');

		$fields = [];

		// Check if towername is changed
		if($island->getTowerNameUnnamed() !== $prevTowerName) {
			// Instead of using the empty string, rename to unclaimed for the tc channel
			$fields[] = [ "name" => "Previous tower name", "value" => $prevTowerName, "inline" => true ];
			$fields[] = [ "name" => "New tower name", "value" => $island->getTowerNameUnnamed(), "inline" => true ];
			$uLogger->info("Island '".$island->getName()."' changed from tower name '$prevTowerName' to '".$island->getTowerNameUnnamed()."'");
		}

		// Check if alliance is changed
		if($island->getAllianceName() !== $prevAllianceName) {
			$fields[] = [ "name" => "Previous alliance owner", "value" => $prevAllianceName, "inline" => true ];
			$fields[] = [ "name" => "New alliance owner", "value" => $island->getAllianceName(), "inline" => true ];
			$uLogger->info("Island '".$island->getName()."' changed from alliance '$prevAllianceName' to '".$island->getAllianceName()."'");
		}

		// Only send discord api call when there are fields changed
		if(count($fields)) {
			$postBody = [
				"embeds" => [
					[
						"title" => $island->getName(),
						"url" => "https://map.cardinalguild.com/pvp/" . $island->getId(), // change pvp to server or make pts link to one of the modes
						"type" => "rich",
						"author" => [
							"name" => strtoupper('pts') //TODO: replace with $mode var
						],
						"thumbnail" => [
							"url" => $url
						],
						"timestamp" => date('c'),
						"color" => $island->getTier() === 4 ? hexdec('f7c38f') : hexdec('e3c9f9'),
						"fields" => $fields
					]
				]
			];

			try {
				$client = new \GuzzleHttp\Client(['headers'=>['Content-Type'=>'application/json']]);
				$client->request('POST', $bossaTcChannel, ['json' => $postBody]);
			} catch (\Exception $e) { }
		}
	}
}
