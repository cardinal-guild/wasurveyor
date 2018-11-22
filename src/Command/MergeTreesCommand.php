<?php


namespace App\Command;

use App\Entity\Island;
use App\Entity\IslandPVETree;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MergeTreesCommand extends Command
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
            ->setName('merge:trees')

            // the short description shown while running "php bin/console list"
            ->setDescription('Merge PVP trees into the PVE trees structure')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $pvpTrees = $this->em->getRepository('App:IslandPVPTree')->findAll();
        $pvpTreeCount = count($pvpTrees);
        $progressBar = new ProgressBar($output, $pvpTreeCount);

        $output->writeln('Merging '.$pvpTreeCount.' PVP trees into the PVE trees of islands ...');
        $i = 0;
        $totalCount = 0;
        foreach($pvpTrees as $pvpTree) {
            /**
             * @var $island Island
             */
            foreach($pvpTree->getIslands() as $island) {
                $existOnIsland = false;
                foreach($island->getPveTrees() as $pveTree) {
                    if($pveTree->getType() == $pvpTree->getType()) {
                        $existOnIsland = true;
                    }
                }
                if(!$existOnIsland) {
                    $pveTree = new IslandPVETree();
                    $pveTree->setType($pvpTree->getType());

                    $island->addPveTree($pveTree);
                    $this->em->persist($island);
                    $totalCount++;
                }
            }
            if ($i % 10 === 0) {
                $this->em->flush();
            }
            $progressBar->advance();
        }
        $progressBar->finish();
        $output->writeln('');
        $output->writeln($totalCount.' PVP trees merged into the PVE trees for islands.');
        $output->writeln('All pvp trees merged into the pve tree database!');
    }
}
