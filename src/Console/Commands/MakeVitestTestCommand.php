<?php
namespace Console\Commands;

use Console\Helpers\Testing\VitestTestBuilder;
use Core\Lib\Utilities\Str;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Creates test for React.js or JavaScript files.  Use flags to determine which one
 * to generate.
 */
class MakeVitestTestCommand extends Command
{
    /**
     * Configures the command.
     *
     * @return void
     */
    protected function configure(): void
    {
        $this->setName('react:make:test')
            ->setDescription('Generates a new test file!')
            ->setHelp('php console react:make:test <test_name>')
            ->addArgument('testname', InputArgument::REQUIRED, 'Pass the test\'s name.')
            ->addOption('unit', null, InputOption::VALUE_NONE, 'Create unit test')
            ->addOption('component', null, InputOption::VALUE_NONE, 'Create component test')
            ->addOption('view', null, InputOption::VALUE_NONE, 'Create view test');
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
        $testName = $input->getArgument('testname');
        return VitestTestBuilder::makeTest($testName, $input);
    }
}
