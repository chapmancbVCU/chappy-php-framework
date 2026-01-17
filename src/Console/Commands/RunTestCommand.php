<?php
namespace Console\Commands;

use Console\Helpers\Testing\FilterService;
use Console\Helpers\Testing\PHPUnitRunner;
use Console\Helpers\Testing\TestRunner;
use Console\Helpers\Tools;
use Core\Lib\Logging\Logger;
use Core\Lib\Utilities\Str;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Supports ability to run a phpunit test with only the name of the test file is accepted as a required input.
 * More information can be found <a href="https://chapmancbvcu.github.io/chappy-php-starter/php_unit#running-tests">here</a>.
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

            // Flags
            ->addOption('coverage', null, InputOption::VALUE_NONE, 'Display code coverage summary.')
            ->addOption('debug', null, InputOption::VALUE_NONE, 'Enable debug output.')
            ->addOption('display-depreciations', null, InputOption::VALUE_NONE, 'Show deprecated method warnings.')
            ->addOption('display-errors', null, InputOption::VALUE_NONE, 'Show errors (on by default).')
            ->addOption('display-incomplete', null, InputOption::VALUE_NONE, 'Show incomplete tests in summary .')
            ->addOption('display-skipped', null, InputOption::VALUE_NONE, 'Show skipped tests in summary.')
            ->addOption('fail-on-incomplete', null, InputOption::VALUE_NONE, 'Mark incomplete tests as failed.')
            ->addOption('fail-on-risky', null, InputOption::VALUE_NONE, 'Fail if risky tests are detected.')
            ->addOption('feature', null, InputOption::VALUE_NONE, 'Run feature tests.')
            ->addOption('random-order', null, InputOption::VALUE_NONE, 'Perform tests in random order.')
            ->addOption('reverse-order', null, InputOption::VALUE_NONE, 'Perform tests in reverse order.')
            ->addOption('stop-on-error', null, InputOption::VALUE_NONE, 'Stop on error.')
            ->addOption('stop-on-failure', null, InputOption::VALUE_NONE, 'Stop on first failure.')
            ->addOption('stop-on-incomplete', null, InputOption::VALUE_NONE, 'Stop on incomplete test.')
            ->addOption('stop-on-risky', null, InputOption::VALUE_NONE, 'Stop on risky test.')
            ->addOption('stop-on-skipped', null, InputOption::VALUE_NONE, 'Stop on skipped test.')
            ->addOption('stop-on-warning', null, InputOption::VALUE_NONE, 'Stop on warning.')
            ->addOption('testdox', null, InputOption::VALUE_NONE, 'Use TestDox output.')
            ->addOption('unit', null, InputOption::VALUE_NONE, 'Run unit tests.');
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
        
        $test = new PHPUnitRunner($input, $output);
        $testSuites = [PHPUnitRunner::FEATURE_PATH, PHPUnitRunner::UNIT_PATH];


        if(!$feature && !$unit && !$testArg) {
            return $test->allTests($testSuites, PHPUnitRunner::TEST_FILE_EXTENSION, PHPUnitRunner::TEST_COMMAND);
        }
        
        // Select test based on file name or function name.
        if($testArg && !$unit && !$feature) {
            
            if(Str::contains($testArg, '::')) {
               return $test->testByFilter($testArg, $testSuites, PHPUnitRunner::TEST_FILE_EXTENSION); 
            }
            return $test->selectTests($testArg, $testSuites, PHPUnitRunner::TEST_FILE_EXTENSION, PHPUnitRunner::TEST_COMMAND);
        }
        
        $featureStatus = null;
        $unitStatus = null;
        // Run tests based on --unit and --feature flags
        if(!$testArg && $unit) {
            $unitStatus = $test->testSuite(
                TestRunner::getAllTestsInSuite(PHPUnitRunner::UNIT_PATH, PHPUnitRunner::TEST_FILE_EXTENSION), 
                PHPUnitRunner::TEST_COMMAND
            );
        }
        if(!$testArg && $feature) {
            $featureStatus = $test->testSuite(
                TestRunner::getAllTestsInSuite(PHPUnitRunner::FEATURE_PATH, PHPUnitRunner::TEST_FILE_EXTENSION), 
                PHPUnitRunner::TEST_COMMAND
            );
        }
        if(!$testArg && PHPUnitRunner::testSuiteStatus([$featureStatus, $unitStatus])) {
            return Command::SUCCESS;
        }

        // Run individual test file based on --unit and --feature flags
        if($testArg && $unit) {
            $unitStatus = $test->singleFileWithinSuite(
                $testArg, 
                PHPUnitRunner::UNIT_PATH, 
                PHPUnitRunner::TEST_FILE_EXTENSION, 
                PHPUnitRunner::TEST_COMMAND
            );
        }
        if($testArg && $feature) {
            $featureStatus = $test->singleFileWithinSuite(
                $testArg, 
                PHPUnitRunner::FEATURE_PATH, 
                PHPUnitRunner::TEST_FILE_EXTENSION, 
                PHPUnitRunner::TEST_COMMAND
            );
        }
        if($testArg && PHPUnitRunner::testSuiteStatus([$featureStatus, $unitStatus])) {
            return Command::SUCCESS;
        }

        Tools::info("There was an issue running unit tests.  Check your command line input.", Logger::ERROR, Tools::BG_RED);
        return Command::FAILURE;
    }
}
