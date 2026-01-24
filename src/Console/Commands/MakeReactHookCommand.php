<?php
namespace Console\Commands;

use Console\Helpers\React;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Implements command for making a new JavaScript utility by running react:hook.
 * More information can be found <a href="https://chapmancbvcu.github.io/chappy-php-starter/react_utils#overview">here</a>.
 */
class MakeReactHookCommand extends Command {
    /**
     * Configures the command.
     *
     * @return void
     */
    protected function configure(): void
    {
        $this->setName('react:hook')
            ->setDescription('Generates a new hook file')
            ->setHelp('php console react:hook <component_name>')
            ->addArgument('hook-name', InputArgument::REQUIRED, 'Pass the name for the new React.js hook');
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
        $hookName = $input->getArgument('hook-name');
        return React::makeUtility($hookName);
    }
}
