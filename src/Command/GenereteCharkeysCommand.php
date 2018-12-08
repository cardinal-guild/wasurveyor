<?php


namespace App\Command;

use App\Entity\Island;
use App\Entity\IslandPVETree;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\Exception\UnsatisfiedDependencyException;

class GenereteCharkeysCommand extends Command
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
            ->setName('generate:charkeys')

            // the short description shown while running "php bin/console list"
            ->setDescription('Generate character keys')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $users = $this->em->getRepository('App:User')->findAll();
        $userCount = count($users);
        $progressBar = new ProgressBar($output, $userCount);

        $output->writeln('Generating character keys for '.$userCount.' users ...');

        $totalCount = 0;
        foreach($users as $user) {
            $user->setCharacterKey(Uuid::uuid4());
            $this->em->persist($user);
            if ($totalCount % 10 === 0) {
                $this->em->flush();
            }
            $progressBar->advance();
            $totalCount++;
        }
        $progressBar->finish();
        $output->writeln('');
        $output->writeln($totalCount.' users have character keys now.');
    }
}
