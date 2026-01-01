<?php
namespace Console\Commands;

use Console\Helpers\React;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Implements command for making a new JavaScript utility by running react:util.
 * More information can be found <a href="https://chapmancbvcu.github.io/chappy-php-starter/react_utils#overview">here</a>.
 */
class MakeReactUtilCommand extends Command {
    /**
     * Configures the command.
     *
     * @return void
     */
    protected function configure(): void
    {
        $this->setName('react:util')
            ->setDescription('Generates a new React.js supporting utility')
            ->setHelp('php console react:util <component_name>')
            ->addArgument('utility-name', InputArgument::REQUIRED, 'Pass the name for the new React.js utility');
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
        $utilityName = $input->getArgument('utility-name');
        return React::makeUtility($utilityName);
    }
}
