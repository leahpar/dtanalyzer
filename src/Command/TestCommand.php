<?php

namespace App\Command;

use App\Entity\Metric;
use App\Service\PhpLocService;
use App\Service\PhpStanService;
use App\Service\SecurityCheckService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:test',
    description: 'Add a short description for your command',
)]
class TestCommand extends Command
{
    public function __construct(
        private readonly PhpLocService $phpLoc,
        private readonly PhpStanService $phpStan,
        private readonly SecurityCheckService $securityCheck,
        private readonly EntityManagerInterface $em,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('project',     InputArgument::REQUIRED, 'Project name')
            ->addArgument('projectPath', InputArgument::REQUIRED, 'Project Path')
            ->addArgument('srcPath',     InputArgument::REQUIRED, 'Source Path (relative to project path)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $project = $input->getArgument('project');
        $projectPath = $input->getArgument('projectPath');
        $srcPath = $projectPath."/".$input->getArgument('srcPath');
        $today = new \DateTime();

        $io->writeln("Project Path: $projectPath");
        $io->writeln("Source Path: $srcPath");

        $io->writeln("Running PhpLoc...");

        $this->phpLoc->getPhpLoc($srcPath);

        $keys = [
            "Classes",
            "Lines of Code",
            "Average Class Length",
            "Maximum Class Length",
            "Average Method Length",
            "Maximum Method Length",
            "Average Methods Per Class",
            "Maximum Methods Per Class",

            "Average Complexity per LLOC",
            "Average Complexity per Class",
            "Maximum Class Complexity",
            "Average Complexity per Method",
            "Maximum Method Complexity",
        ];

        foreach ($keys as $key) {
            $io->writeln("$key: {$this->phpLoc->getData($key)}");
            $this->em->persist(new Metric(
                date: $today,
                project: $project,
                key: $key,
                value: $this->phpLoc->getData($key))
            );
        }
        $io->writeln("PhpLoc done");


        $io->writeln("Running PhpStan...");
        $this->phpStan->getPhpStan($srcPath);
        $io->writeln("PhpStan errors: {$this->phpStan->getErrors()}");
        $this->em->persist(new Metric(
            date: $today,
            project: $project,
            key: "PhpStan errors",
            value: $this->phpStan->getErrors())
        );
        $io->writeln("PhpStan done");

        $io->writeln("Running Security Check...");
        $this->securityCheck->getSecurityCheck($projectPath);
        $io->writeln("Security Check count: {$this->securityCheck->getSecurityCheckCount()}");
        $this->em->persist(new Metric(
            date: $today,
            project: $project,
            key: "Security Check count",
            value: $this->securityCheck->getSecurityCheckCount())
        );
        $io->writeln("Security Check done");

        $this->em->flush();
        $io->success('Done');

        return Command::SUCCESS;
    }
}
