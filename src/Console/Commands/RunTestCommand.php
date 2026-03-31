<?php
namespace Console\Commands;

use Console\ConsoleCommand;
use Console\Helpers\Testing\PHPUnitRunner;
use Console\Helpers\Testing\TestRunner;
use Core\Lib\Utilities\Str;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Supports ability to run a phpunit test with only the name of the test file is accepted as a required input.
 * More information can be found <a href="https://chapmancbvcu.github.io/chappy-php-starter/php_unit#running-tests">here</a>.
 */
class RunTestCommand extends ConsoleCommand
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
     * @return int A value that indicates success, invalid, or failure.
     */
    protected function handle(): int
    {
        // Get options and arguments
        $testArg = $this->getArgument('testname');
        if($testArg && PHPUnitRunner::testIfSame($testArg)) return self::FAILURE;

        $unit = $this->getOption('unit');
        $feature = $this->getOption('feature');
        
        $test = new PHPUnitRunner($this->input, $this->output);

        // Run all tests.
        if(!$feature && !$unit && !$testArg) {
            return $test->allTests();
        }
        
        // Select test based on file name or function name.
        if($testArg && !$unit && !$feature) {
            if(Str::contains($testArg, ':')) {
               return $test->testByFilter($testArg); 
            }
            return $test->selectByTestName($testArg);
        }
        
        /* 
         * Run tests based on --unit and --feature flags and verify successful 
         * completion.
         */
        $runBySuiteStatus = [];
        if(!$testArg && $unit) {
            $runBySuiteStatus[] = $test->testSuite(
                TestRunner::getAllTestsInSuite(PHPUnitRunner::UNIT_PATH, PHPUnitRunner::TEST_FILE_EXTENSION)
            );
        }
        if(!$testArg && $feature) {
            $runBySuiteStatus[] = $test->testSuite(
                TestRunner::getAllTestsInSuite(PHPUnitRunner::FEATURE_PATH, PHPUnitRunner::TEST_FILE_EXTENSION)
            );
        }
        if(!$testArg && PHPUnitRunner::testSuiteStatus($runBySuiteStatus)) {
            console_info("Completed tests by suite(s)");
            return self::SUCCESS;
        }

        /* 
         * Run individual test file based on --unit and --feature flags and 
         * verify successful completion.
         */
        $testNameByFlagStatus = [];
        if($testArg && $unit) {
            $testNameByFlagStatus[] = $test->singleFileWithinSuite(
                $testArg, 
                PHPUnitRunner::UNIT_PATH, 
                PHPUnitRunner::TEST_FILE_EXTENSION
            );
        }
        if($testArg && $feature) {
            $testNameByFlagStatus[] = $test->singleFileWithinSuite(
                $testArg, 
                PHPUnitRunner::FEATURE_PATH, 
                PHPUnitRunner::TEST_FILE_EXTENSION
            );
        }
        if($testArg && PHPUnitRunner::testSuiteStatus($testNameByFlagStatus)) {
            console_info("Completed tests by name and suite(s)");
            return self::SUCCESS;
        }

        console_error("There was an issue running unit tests.  Check your command line input.");
        return self::FAILURE;
    }
}
