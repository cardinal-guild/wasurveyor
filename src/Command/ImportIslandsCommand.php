<?php


namespace App\Command;

use App\Entity\Island;
use App\Entity\IslandPVETree;
use App\Entity\IslandTree;
use App\Repository\IslandRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Finder\Finder;

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
		$projectDir = $this->container->get('kernel')->getProjectDir();

		/**
		 * @var $islandRepo IslandRepository
		 */
		$islandRepo = $this->em->getRepository('App:Island');

		$mapData = (array) json_decode(file_get_contents($projectDir.'/data/wamap.json'));
		$dataIslands = $mapData['Islands'];
		$islandCount = count($dataIslands);
		$progressBar = new ProgressBar($output, $islandCount);

		$output->writeln('Importing data from json file, '.$islandCount.' islands ...');

		$totalCount = 0;
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
				$island->setPublished(true);
				$this->em->persist($island);
				$totalCount++;
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
	}
	private function convertLng($coord) {
		return 4750+($coord/3.85);
	}
	private function convertLat($coord) {
		return (-4750)+($coord/3.85);
	}
}
