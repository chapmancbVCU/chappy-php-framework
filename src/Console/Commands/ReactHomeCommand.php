<?php
namespace Console\Commands;

use Console\Helpers\React;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Implements command for generating the home/Index.jsx page component. 
 */
class ReactHomeCommand extends Command {
    /**
     * Configures the command.
     *
     * @return void
     */
    protected function configure(): void
    {
        $this->setName('react:home')
            ->setDescription('Generates home/Index.jsx page component.')
            ->setHelp('php console react:home');
    }

    /**
     * Executes the command
     *
     * @param InputInterface $input The input.
     * @param OutputInterface $output The output.
     * @return int A value that indicates success, invalid, or failure.
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        return React::homeComponent();
    }
}
