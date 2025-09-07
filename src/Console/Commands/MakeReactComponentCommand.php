<?php
namespace Console\Commands;

use Console\Helpers\View;
use Console\Helpers\Tools;
use Chappy\Console\Helpers\React;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

/**
 * Implements command for making a new view file.
 */
class MakeReactComponentCommand extends Command {
    /**
     * Configures the command.
     *
     * @return void
     */
    protected function configure(): void
    {
        $this->setName('react:component')
            ->setDescription('Generates a new React.js component')
            ->setHelp('php console react:component <directory_name>.<component_name>')
            ->addArgument('component-name', InputArgument::REQUIRED, 'Pass the name for the new React.js component')
            ->addOption('named', null, InputOption::VALUE_NONE, 'Creates as a named export');
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
        $componentName = $input->getArgument('component-name');
        return React::makeComponent($componentName);
    }
}
