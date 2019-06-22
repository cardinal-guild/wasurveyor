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

class GetMinMaxYIslandCommand extends Command
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
			->setName('minmaxh:islandjson')

			// the short description shown while running "php bin/console list"
			->setDescription('Get min and max Y position of all islands')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
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
		$output->writeln('Getting lowest and highest y point data from json file, '.$islandCount.' islands ...');

		$minY = 0;
		$maxY = 0;
		foreach($dataIslands as $dataIsland) {
			$height = $dataIsland->y;
			if($height > $maxY) {
				$maxY = $height;
			} else if($height < $minY) {
				$minY = $height;
			}

		}
		$output->writeln('');
		$output->writeln('The lowest height number: '.$minY);
		$output->writeln('The highest height number: '.$maxY);
	}
	private function getProjectDir() {
		return $this->container->get('kernel')->getProjectDir();
	}

}
