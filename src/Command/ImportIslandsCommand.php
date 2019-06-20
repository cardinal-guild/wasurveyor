<?php


namespace App\Command;

use App\Entity\Island;
use App\Entity\IslandCreator;
use App\Entity\IslandImage;
use App\Entity\IslandPVETree;
use App\Entity\IslandTree;
use App\Repository\IslandCreatorRepository;
use App\Repository\IslandRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Psr7\Stream;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ImportIslandsCommand extends Command
{
	/**
	 * @var EntityManagerInterface
	 */
	private $em;

	/**
	 * @var ContainerInterface
	 */
	private $container;

	public function __construct(EntityManagerInterface $entityManager, ContainerInterface $container)
	{
		$this->em = $entityManager;
		$this->container = $container;

		parent::__construct();
	}


	protected function configure()
	{
		$this
			// the name of the command (the part after "bin/console")
			->setName('import:islandjson')

			// the short description shown while running "php bin/console list"
			->setDescription('Import wamap.json in data folder')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{

		/**
		 * @var $userRepo UserRepository
		 */
		$userRepo = $this->em->getRepository('App:User');

		/**
		 * @var $islandRepo IslandRepository
		 */
		$islandRepo = $this->em->getRepository('App:Island');

		/**
		 * @var $creatorRepo IslandCreatorRepository
		 */
		$creatorRepo = $this->em->getRepository('App:IslandCreator');

		$mapData = (array) json_decode(file_get_contents($this->getProjectDir().'/data/wamap.json'));
		$dataIslands = $mapData['Islands'];
		$islandCount = count($dataIslands);
		$progressBar = new ProgressBar($output, $islandCount);

		$output->writeln('Importing data from json file, '.$islandCount.' islands ...');

		$firstUser = $userRepo->find(1);
		$totalCount = 0;
		$failedCount = 0;
		$i = 0;
		foreach($dataIslands as $dataIsland) {
			$guid = (int) basename($dataIsland->Island, '.json');
			/**
			 * @var $island Island
			 */
			$island = $islandRepo->findOneBy(['guid'=>$guid]);
			if($island) {
				$island->setLng($this->convertLng($dataIsland->x));
				$island->setLat($this->convertLat($dataIsland->z));
				$island->setAltitude($this->convertHeight($dataIsland->y));
				$island->setPublished(true);
				$island->setGuid($guid);
				$this->em->persist($island);
				$totalCount++;
			} else {
				$steamUrl = 'https://steamcommunity.com/sharedfiles/filedetails/?id='.$guid;
				$client = new Client();
				try {
					$resWorkshopIsland = $client->get($steamUrl);
					$islandCrawler = new Crawler();
					$islandCrawler->addHtmlContent($resWorkshopIsland->getBody());
					$islandName = $islandCrawler->filterXPath("//div[contains(@class, 'workshopItemTitle')]")->text();
					$islandImageUrl = $islandCrawler->filterXPath("//link[contains(@rel, 'image_src')]")->evaluate('@href')->text();
					$creatorLink = $islandCrawler->filterXPath("//div[contains(@class, 'creatorsBlock')]//a[contains(@class, 'friendBlockLinkOverlay')]")->evaluate('@href')->text();
					if ($creatorLink) {
						sleep(random_int(0, 10)/10);
						$resCreator = $client->get($creatorLink);
						$creatorCrawler = new Crawler();
						$creatorCrawler->addHtmlContent($resCreator->getBody());
						$creatorName = $creatorCrawler->filterXPath("//span[contains(@class, 'actual_persona_name')]")->text();

						//Create new island
						if($islandName && $creatorName && $islandImageUrl) {
							$imageFilePath = $this->downloadImage($islandImageUrl);
							if($imageFilePath) {
								$imagefile = new UploadedFile($imageFilePath, basename($imageFilePath), 'image/jpeg', null, null, true);

								$islandImage = new IslandImage();
								$islandImage->setImageFile($imagefile);
								$this->em->persist($islandImage);

								$island = new Island();
								$island->setLng($this->convertLng($dataIsland->x));
								$island->setLat($this->convertLat($dataIsland->z));
								$island->setPublished(true);
								$island->setName($islandName);
								$island->setWorkshopUrl($steamUrl);
								$island->setDatabanks(5);
								$island->setGuid($guid);
								$island->setSurveyUpdatedBy($firstUser);
								$island->setSurveyCreatedBy($firstUser);

								$creator = $creatorRepo->findOneBy(['name'=>$creatorName]);
								if(!$creator) {
									$creator = new IslandCreator();
									$creator->setName($creatorName);
									$creator->setWorkshopUrl($creatorLink);
									$this->em->persist($creator);
								}
								$island->setCreator($creator);
								$island->addImage($islandImage);

								$totalCount++;
								$this->em->persist($island);

							} else {
								$output->writeln('');
								$output->writeln('Could not retrieve island image for guid: '.$guid);
								$failedCount++;
							}

						}
					}
				} catch (\Exception $e) {
					$output->writeln('');
					$output->writeln('Could not retrieve island data for guid: '.$guid);
					$failedCount++;
				}
				sleep(random_int(10, 20)/10);
			}

			if ($i % 10 === 0) {
				$this->em->flush();
			}
			$i++;
			$progressBar->advance();
		}
		$progressBar->finish();
		$output->writeln('');
		$output->writeln($totalCount.' island GUIDs found and coordinates set.');
		$output->writeln($failedCount.' island GUIDs failed to retrieve data for.');
	}
	private function convertHeight($coord) {
		return (int) 2150+($coord/1.625);
	}
	private function convertLng($coord) {
		return 4750+($coord/3.85);
	}
	private function convertLat($coord) {
		return (-4750)+($coord/3.85);
	}
	private function getProjectDir() {
		return $this->container->get('kernel')->getProjectDir();
	}
	private function downloadImage($fromUrl) {
		$path = $this->getProjectDir() . '/var/downloads/' . uniqid(rand(), true) . '.jpg';
		$imageResource = fopen($path, 'w+');
		try {
			$client = new Client();
			$client->get($fromUrl, ['sink'=>$imageResource]);
			return $path;
		} catch (Exception $e) {
			return null;
		}
	}
}
