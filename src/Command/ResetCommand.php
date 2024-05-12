<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:reset',
    description: 'Drop entire database, create it and run migrations',
)]
class ResetCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->info('Resetting environment');

        $commands = [
            'doctrine:database:drop' => new ArrayInput(['--force'  => true]),
            'doctrine:database:create' => new ArrayInput([]),
            'doctrine:migrations:migrate' => new ArrayInput([]),
        ];

        $app = $this->getApplication();

        foreach($commands as $name => $input) {
            $returnCode = $app->find($name)->run($input, $output);
            if ($returnCode > 0) {
                return Command::FAILURE;
            }
        }

        $io->success('Environment has been reset successfully.');

        return Command::SUCCESS;
    }
}
