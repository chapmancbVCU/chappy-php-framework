<?php
namespace Console\Commands;

use Console\Helpers\Tools;
use Core\Lib\Logging\Logger;
use Console\Helpers\Testing\VitestTestRunner;
use Core\Lib\Utilities\Str;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Supports operations related to running Jest unit tests.
 */
class RunVitestCommand extends Command
{
    /**
     * Configures the command.
     *
     * @return void
     */
    protected function configure(): void
    {
        $this->setName('react:test')
            ->setDescription('Runs Vitest unit tests')
            ->setHelp('php console react:test')
            ->addArgument('testname', InputArgument::OPTIONAL, 'Pass the test file\'s name.')
            
            // Suite flags
            ->addOption('component', null, InputOption::VALUE_NONE, 'Run component tests.')
            ->addOption('unit', null, InputOption::VALUE_NONE, 'Run unit tests.')
            ->addOption('view', null, InputOption::VALUE_NONE, 'Run unit view.');
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
        // Get options and arguments
        $testArg = $input->getArgument('testname');
        $component = $input->getOption('component');
        $unit = $input->getOption('unit');
        $view = $input->getOption('view');

        $test = new VitestTestRunner($input, $output);

        if(!$testArg && !$component && !$unit && !$view) {
            return $test->allTests();
        }
        
        
        Tools::info("There was an issue running unit tests.  Check your command line input.", Logger::ERROR, Tools::BG_RED);
        return Command::FAILURE;
    }
}
