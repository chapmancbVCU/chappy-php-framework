<?php
namespace Console\Commands;
use Core\Helper;
use Console\Helpers\Test;
use Console\Helpers\Tools;
use Core\Lib\Utilities\Str;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Supports ability to run a phpunit test with only the name of the test 
 * file is accepted as a required input.
 */
class RunTestCommand extends Command
{
    /**
     * Configures the command.
     *
     * @return void
     */
    protected function configure(): void
    {
        $this->setName('test')
            ->setDescription('Performs the phpunit test.')
            ->setHelp('php console test <test_file_name> without the .php extension.')
            ->addArgument('testname', InputArgument::OPTIONAL, 'Pass the test file\'s name.')
            ->addOption('unit', null, InputOption::VALUE_NONE, 'Run unit tests.')
            ->addOption('feature', null, InputOption::VALUE_NONE, 'Run feature tests.')
            ->addOption('filter', null, InputOption::VALUE_REQUIRED, 'Filter by test method or class name.')
            ->addOption('coverage', null, InputOption::VALUE_NONE, 'Display code coverage summary.')
            ->addOption('testdox', null, InputOption::VALUE_NONE, 'Use TestDox output.')
            ->addOption('debug', null, InputOption::VALUE_NONE, 'Enable debug output.')
            ->addOption('stop-on-failure', null, InputOption::VALUE_NONE, 'Stop on first failure.');
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
        $unit = $input->getOption('unit');
        $feature = $input->getOption('feature');
        
        $test = new Test($input, $output);
        
        if(!$feature && !$unit && !$testArg) {
            return $test->allTests();
        }
        
        // Select test based on file name or function name.
        if($testArg && !$unit && !$feature) {
             return $test->selectTests($testArg);
        }
        
        $featureStatus = null;
        $unitStatus = null;
        // Run tests based on --unit and --feature flags
        if(!$testArg && $unit) {
            $unitStatus = $test->testSuite(Test::unitTests());
        }
        if(!$testArg && $feature) {
            $featureStatus = $test->testSuite(Test::featureTests());
        }
        if(!$testArg && Test::testSuiteStatus($featureStatus, $unitStatus)) {
            return Command::SUCCESS;
        }

        // Run individual test file based on --unit and --feature flags
        if($testArg && $unit) {
            $unitStatus = $test->singleFileWithinSuite(Test::UNIT_PATH, $testArg);
        }
        if($testArg && $feature) {
            $featureStatus = $test->singleFileWithinSuite(Test::FEATURE_PATH, $testArg);
        }
        if($testArg && Test::testSuiteStatus($featureStatus, $unitStatus)) {
            return Command::SUCCESS;
        }

        Tools::info("There was an issue running unit tests.  Check your command line input.", 'error', 'red');
        return Command::FAILURE;
    }
}
