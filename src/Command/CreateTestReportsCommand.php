<?php


namespace App\Command;

use App\Entity\Island;
use App\Entity\IslandPVETree;
use App\Entity\Report;
use App\Entity\ReportMetal;
use App\Entity\ReportTree;
use Doctrine\ORM\EntityManagerInterface;
use Faker\Factory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateTestReportsCommand extends Command
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;

        parent::__construct();
    }


    protected function configure()
    {
        $this
            // the name of the command (the part after "bin/console")
            ->setName('create:testreports')

            // the short description shown while running "php bin/console list"
            ->setDescription('Create test reports')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        // use the factory to create a Faker\Generator instance
        $faker = Factory::create();

        $islands = $this->em->getRepository('App:Island')->getRandomIslands(1);
        $metalTypes = $this->em->getRepository('App:MetalType')->findAll();
        $treeTypes = $this->em->getRepository('App:TreeType')->findAll();
        foreach($islands as $island) {
            $report = new Report();
            $report->setIsland($island);
            $report->setDangerous((bool)rand(0,1));
            $report->setRevivalChambers((bool)rand(0,1));
            $report->setTurrets((bool)rand(0,1));
            $report->setDatabanks((integer)rand(1,10));
            $report->setName($faker->name);
            $report->setIpAddress($faker->ipv4);
            for($i = 0; $i < 10;$i++) {
                shuffle($metalTypes);
                $reportMetal = new ReportMetal();
                $reportMetal->setType($metalTypes[0]);
                $reportMetal->setQuality((integer)rand(1, 10));
                $report->addMetal($reportMetal);
            }
            for($i = 0; $i < 4;$i++) {
                shuffle($treeTypes);
                $reportTree = new ReportTree();
                $reportTree->setType($treeTypes[0]);
                $report->addTree($reportTree);
            }
            $this->em->persist($report);
        }
        $this->em->flush();
        $output->writeln('10 random reports generated');
    }
}
