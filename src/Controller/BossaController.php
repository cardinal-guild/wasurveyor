<?php


namespace App\Controller;


use App\Entity\Alliance;
use App\Entity\Island;
use App\Entity\IslandTerritoryControl;
use App\Repository\AllianceRepository;
use App\Repository\IslandRepository;
use App\Repository\IslandTerritoryControlRepository;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\Controller\FOSRestController;
use Gedmo\Loggable\Entity\LogEntry;
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
	 * @var IslandTerritoryControlRepository
	 */
	protected $islandTCRepo;

	/**
	 * @var EntityManagerInterface
	 */
	protected $entityManager;

	public function __construct(
		CacheManager $cacheManager,
		UploaderHelper $uploaderHelper,
		IslandRepository $islandRepo,
		IslandTerritoryControlRepository $islandTCRepo,
		AllianceRepository $allianceRepo,
		EntityManagerInterface $entityManager
	) {
		$this->cacheManager = $cacheManager;
		$this->uploadHelper = $uploaderHelper;
		$this->islandRepo = $islandRepo;
		$this->allianceRepo = $allianceRepo;
		$this->islandTCRepo = $islandTCRepo;
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

        $uLogger = $this->get('monolog.logger.tc_updates');

		if (!$request->request->has('Region')) {
			return $this->view('no region provided', 400);
		}
		if (!$request->request->has('IslandDatas')) {
			return $this->view('no island data given', 400);
		}

		$islandDatas = $request->request->get('IslandDatas');
        $server = $request->request->get('Region');

        $responses = [];
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
					//Get territory control, if it exists
					$territoryControl = $this->islandTCRepo->findOneBy(['server'=>$server, 'island'=>$island]);
					if(!$territoryControl) {
						$territoryControl = new IslandTerritoryControl();
						$territoryControl->setServer($server);
						$territoryControl->setIsland($island);
                    }

					// Store previous tower and alliance name for discord channel updates, even if nulled
					$prevAllianceName = $territoryControl->getAllianceName();
					// Get the tower name, if null returned, you get 'Unnamed' as string back
                    $prevTowerName = $territoryControl->getTowerName();

                    if ($allianceName === "Unclaimed" && $towerName === "None") { // this will ONLY be Unclaimed if there is no alliance, not unnamed, or none or something else. better to be specific
                        $territoryControl->setAlliance(null);
                        $territoryControl->setTowerName("None");
                        $uLogger->info("Island '".$island->getName()."' with id '".$island->getGuid()."' changed from alliance '$prevAllianceName' to Unclaimed'");
                        $responses[] = "Island '".$island->getName()."' with id '".$island->getGuid()."' changed from alliance '$prevAllianceName' to Unclaimed'";
                    }
                    else if ($prevAllianceName !== $allianceName) {
                        /**
                         * @var $alliance Alliance
                         */
                        $alliance = $this->allianceRepo->findOneBy(['name' => trim($allianceName)]);
                        if (!$alliance) {
                            $alliance = new Alliance();
                            $alliance->setName(trim($allianceName));
                        }
                        $territoryControl->setAlliance($alliance);
                        $territoryControl->setTowerName($towerName);
                        $uLogger->info("Island '".$island->getName()."' with id '".$island->getGuid()."' changed from alliance '$prevAllianceName' to '".$territoryControl->getAllianceName()."'");
                        $responses[] = "Island '".$island->getName()."' with id '".$island->getGuid()."' changed from alliance '$prevAllianceName' to '".$territoryControl->getAllianceName()."'";
                        $this->sendDiscordUpdate($territoryControl->getServer(), $island, $this->getPreviousAllianceName($territoryControl), $allianceName);
                    }
                    else if ($prevTowerName !== $towerName) { // If tower name has changed
                        $territoryControl->setTowerName($towerName);
                        $uLogger->info("Island '".$island->getName()."' with id '".$island->getGuid()."' changed from tower name '$prevTowerName' to '".$territoryControl->getTowerName()."'");
                        $responses[] = "Island '".$island->getName()."' with id '".$island->getGuid()."' changed from tower name '$prevTowerName' to '".$territoryControl->getTowerName()."'";
                    }
					$this->entityManager->persist($territoryControl);
					// Flush in every looped item because most times the api call does not contain a lot of changes
					$this->entityManager->flush();
                }
                else if (!$island) {
                    $uLogger->warning($islandId." is an UNKNOWN ID");
                    $responses[] = $islandId." is an UNKNOWN ID";
                }
                else {
                    $responses[] = "Not a t3 or t4 island";
                }
            }
            else {
                $responses[] = "Missing AllianceName or TctName";
            }
		}
		return $this->view($responses);
    }

    private function sendDiscordUpdate($server, $island, $oldAllianceName, $newAllianceName)
    {
        $bossaTcChannel = $this->getParameter('bossa_tc_channel');
		$uLogger = $this->get('monolog.logger.tc_updates');

		$image = $island->getImages()->first();

        $url = $this->cacheManager->getBrowserPath($this->uploadHelper->asset($image, 'imageFile'), 'island_popup');

        $fields = [];
        $fields[] = [ "name" => "Previous Owner", "value" => $oldAllianceName, "inline" => true ];
        $fields[] = [ "name" => "New Owner", "value" => $newAllianceName, "inline" => true ];

        $postBody = [
            "embeds" => [
                [
                    "title" => $island->getName(),
                    "url" => "https://map.cardinalguild.com/".'pvp'."/" . $island->getId(), // change pvp to server or make pts link to one of the modes
                    "type" => "rich",
                    "author" => [
                        "name" => strtoupper($server)." server"
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

    private function getPreviousAllianceName(IslandTerritoryControl $territoryControl) {
		$logRepo = $this->entityManager->getRepository('Gedmo\Loggable\Entity\LogEntry');
		$logEntries = $logRepo->getLogEntries($territoryControl);
		// slice off first one
		$logEntries = array_slice($logEntries, 1);
		/**
		 * @var $logEntry LogEntry
		 */
		foreach($logEntries as $logEntry) {
			$data = $logEntry->getData();
			if(array_key_exists('alliance', $data)) {
				if($data['alliance'] && isset($data['alliance']['id'])) {
					$alliance = $this->allianceRepo->find($data['alliance']['id']);
					if($alliance) {
						return $alliance->getName();
					}
				}
			}
		}
		return "Unclaimed";
	}
}
