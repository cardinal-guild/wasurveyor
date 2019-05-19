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

class ExtractIslandGuidCommand extends Command
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
            ->setName('extract:islandguid')

            // the short description shown while running "php bin/console list"
            ->setDescription('Set island GUID by workshop url')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $islands = $this->em->getRepository('App:Island')->findAll();
        $islandCount = count($islands);
        $progressBar = new ProgressBar($output, $islands);

        $output->writeln('Trying to set island GUID (ID) by workshop url, '.$islandCount.' islands ...');
        $totalCount = 0;
        $i = 0;
        foreach($islands as $island) {
            /**
             * @var $island Island
             */
            if ($island->getWorkshopUrl() && !$island->getGuid()) {
                if(preg_match("/id=([^&]*)/", $island->getWorkshopUrl(), $match)) {
                    $island->setGuid(explode("=", $match[0])[1]);
                    $totalCount++;
                }
            }
            if ($i % 10 === 0) {
                $this->em->flush();
            }
            $i++;
            $progressBar->advance();
        }
        $progressBar->finish();
        $output->writeln('');
        $output->writeln($totalCount.' island GUIDs found and set as guid for island.');
    }
}
