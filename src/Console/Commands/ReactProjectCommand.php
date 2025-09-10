<?php
namespace Console\Commands;


use Console\Console\Helpers\React;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ReactProjectCommand extends Command {
    protected function configure(): void {
        $this->setName('react')
            ->setDescription('Enables React.js frontend features')
            ->setHelp('php console react');
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
        return React::react();
    }
}