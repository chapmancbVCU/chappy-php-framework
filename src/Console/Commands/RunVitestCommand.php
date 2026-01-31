<?php
namespace Console\Commands;

use Console\Helpers\Testing\TestRunner;
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
            ->addOption('view', null, InputOption::VALUE_NONE, 'Run unit view.')
            
            ->addOption('bail', null, InputOption::VALUE_REQUIRED, 'Stop after N failure.', false)
            ->addOption('clearCache', null, InputOption::VALUE_NONE, 'Clears the cache.')
            ->addOption('coverage', null, InputOption::VALUE_NONE, 'Display code coverage summary.')
            ->addOption('pass-with-no-tests', null, InputOption::VALUE_NONE, 'Pass with no tests.')
            ->addOption('retry', null, InputOption::VALUE_REQUIRED, 'Retry failing tests.', false)
            ->addOption('update', null, InputOption::VALUE_NONE, 'Update snapshots.');
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
        $testSuites = [VitestTestRunner::COMPONENT_PATH, VitestTestRunner::UNIT_PATH, VitestTestRunner::VIEW_PATH];
        $extensions = [VitestTestRunner::REACT_TEST_FILE_EXTENSION, VitestTestRunner::UNIT_TEST_FILE_EXTENSION];

        if(!$testArg && !$component && !$unit && !$view) {
            return $test->allTests($testSuites, $extensions, VitestTestRunner::TEST_COMMAND);
        }
        
        // Select test based on file name or function name.
        if($testArg && !$component && !$unit && !$view) {
            if(Str::contains($testArg, '::')) {
                return $test->testByFilter($testArg, $testSuites, $extensions);
            }
            return $test->selectByTestName($testArg, $testSuites, $extensions, VitestTestRunner::TEST_COMMAND);
        }

        $runBySuiteStatus = [];
        // Run tests based on flag provided (--component, --unit, --view).
        if(!$testArg && $component) {
            $runBySuiteStatus[] = $test->testSuite(
                TestRunner::getAllTestsInSuite(VitestTestRunner::COMPONENT_PATH, VitestTestRunner::REACT_TEST_FILE_EXTENSION),
                VitestTestRunner::TEST_COMMAND
            );
        }
        if(!$testArg && $unit) {
            $runBySuiteStatus[] = $test->testSuite(
                TestRunner::getAllTestsInSuite(VitestTestRunner::UNIT_PATH, VitestTestRunner::UNIT_TEST_FILE_EXTENSION),
                VitestTestRunner::TEST_COMMAND
            );
        }
        if(!$testArg && $view) {
            $runBySuiteStatus[] = $test->testSuite(
                TestRunner::getAllTestsInSuite(VitestTestRunner::VIEW_PATH, VitestTestRunner::REACT_TEST_FILE_EXTENSION),
                VitestTestRunner::TEST_COMMAND
            );
        }
        if(!$testArg && VitestTestRunner::testSuiteStatus($runBySuiteStatus)) {
            console_info("Completed tests by suite(s)");
            return Command::SUCCESS;
        }

        // Run individual test file based on flag provided.
        $testNameByFlagStatus = [];
        if($testArg && $component) {
            $testNameByFlagStatus[] = $test->singleFileWithinSuite(
                $testArg, 
                VitestTestRunner::COMPONENT_PATH, 
                VitestTestRunner::REACT_TEST_FILE_EXTENSION, 
                VitestTestRunner::TEST_COMMAND
            );
        }
        if($testArg && $unit) {
            $testNameByFlagStatus[] = $test->singleFileWithinSuite(
                $testArg, 
                VitestTestRunner::UNIT_PATH, 
                VitestTestRunner::UNIT_TEST_FILE_EXTENSION, 
                VitestTestRunner::TEST_COMMAND
            );
        }
        if($testArg && $view) {
            $testNameByFlagStatus[] = $test->singleFileWithinSuite(
                $testArg, VitestTestRunner::VIEW_PATH, 
                VitestTestRunner::REACT_TEST_FILE_EXTENSION, 
                VitestTestRunner::TEST_COMMAND
            );
        }
        if($testArg && VitestTestRunner::testSuiteStatus($testNameByFlagStatus)) {
            console_info("Completed tests by name and suite(s)");
            return Command::SUCCESS;
        }

        console_error("There was an issue running unit tests.  Check your command line input.");
        return Command::FAILURE;
    }
}
