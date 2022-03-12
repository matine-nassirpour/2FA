<?php

namespace App\Command;

use Exception;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\LogicException;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'drop-recreate:database',
    description: 'Drop and recreate the database with its structure and fake datasets',
)]
class DeleteAndRecreateDatabaseCommand extends Command
{
    protected function configure(): void
    {
        $this->setDescription('Drop and recreate the database with its structure and fake datasets.');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->section('Drop and recreate the database with its structure and fake datasets.');

        $this->runSymfonyCommand($input, $output, 'doctrine:database:drop', true);
        $this->runSymfonyCommand($input, $output, 'doctrine:database:create');
        $this->runSymfonyCommand($input, $output, 'doctrine:migrations:migrate');
        $this->runSymfonyCommand($input, $output, 'doctrine:fixtures:load');

        $io->success('Well done');

        return Command::SUCCESS;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param string $command
     * @param bool $forceOption
     * @return void
     * @throws Exception
     */
    private function runSymfonyCommand(
        InputInterface $input,
        OutputInterface $output,
        string $command,
        bool $forceOption = false
    ): void
    {
        $application = $this->getApplication();

        if (!$application) {
            throw new LogicException('App not found 🙁');
        }

        $command = $application->find($command);

        if ($forceOption) {
            $input = new ArrayInput([
                '--force' => true
            ]);
        }

        $input->setInteractive(false);

        $command->run($input, $output);
    }
}
