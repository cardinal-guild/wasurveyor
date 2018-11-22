<?php


namespace App\Command;

use App\Entity\Island;
use App\Entity\IslandPVETree;
use App\Entity\IslandTree;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ConvertPVETreesCommand extends Command
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
            ->setName('convert:trees')

            // the short description shown while running "php bin/console list"
            ->setDescription('Convert PVE trees to normal trees')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $pveTrees = $this->em->getRepository('App:IslandPVETree')->findAll();
        $pveTreeCount = count($pveTrees);
        $progressBar = new ProgressBar($output, $pveTreeCount);

        $output->writeln('Merging '.$pveTreeCount.' PVE trees into the normal trees ...');
        $i = 0;
        $totalCount = 0;
        foreach($pveTrees as $pveTree) {
            /**
             * @var $island Island
             */
            foreach($pveTree->getIslands() as $island) {
                $existOnIsland = false;
                foreach($island->getTrees() as $tree) {
                    if($pveTree->getType() == $tree->getType()) {
                        $existOnIsland = true;
                    }
                }
                if(!$existOnIsland) {
                    $tree = new IslandTree();
                    $tree->setType($pveTree->getType());

                    $island->addTree($tree);
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
        $output->writeln($totalCount.' PVE trees converted into normal trees for islands.');
        $output->writeln('All PVE trees converted into the tree database!');
    }
}
