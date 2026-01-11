<?php
declare(strict_types=1);
namespace Console\Helpers\Testing;

use Core\Lib\Utilities\Arr;
use Console\Helpers\Tools;
use Core\Lib\Logging\Logger;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class that contains functions that can be used by runner child classes.
 */
class TestRunner {
    /**
     * The array of options allowed as input for the test command.
     */
    public const ALLOWED_OPTIONS = [];

    /**
     * A string of input options provided as input when running the 
     * test command.
     *
     * @var string 
     */
    public string $inputOptions;

    /**
     * The Symfony OutputInterface object.
     *
     * @var OutputInterface 
     */
    public OutputInterface $output;

    /**
     * Constructor
     *
     * @param InputInterface $input The Symfony InputInterface object.
     * @param OutputInterface $output The Symfony OutputInterface object.
     */
    public function __construct(OutputInterface $output) {
        $this->output = $output;
    }

    /**
     * Retrieves all files in test suite so they can be run.
     *
     * @param string $path Path to test suite.
     * @param string $ext File extension to specify between php and js related tests.
     * @return array The array of all filenames in a particular directory.
     */
    public static function getAllTestsInSuite(string $path, string $ext): array {
        return glob($path."*".$ext);
    }

    /**
     * Alerts use if there are no available tests to perform.
     *
     * @return void
     */
    protected function noAvailableTestsMessage(): void {
        Tools::info("No test available to perform", Logger::DEBUG, Tools::BG_YELLOW);
    }

    /**
     * Runs the unit test.
     *
     * @param string $tests The test to be performed.
     * @return void
     */
    public function runTest(string $tests, string $testCommand): void {
        $command = $testCommand . $tests . $this->inputOptions;
        Tools::info('File: '.$tests);
        $this->output->writeln(shell_exec($command));
    }

    /**
     * Performs testing against a single class within a test suite.
     *
     * @param string $testArg The name of the test class or test.js file without extension.
     * @param string $suite The name of the test suite.
     * @param string $ext The file extension.  Best practice is to use const provided by child class.
     * @param string $command The test command.  Best practice is to use const provided by child class.
     * @return int A value that indicates success, invalid, or failure.
     */
    public function singleFileWithinSuite(string $testArg, string $suite, string $ext, string $command): int {
        if(file_exists($suite.$testArg.$ext)) {
            $test = ' '.$suite.$testArg.$ext;
            $this->runTest($test, $command);
            return Command::SUCCESS;
        }
        return Command::FAILURE;
    }

    /**
     * Enforces rule that classes/files across test suites should be unique for filtering.
     *
     * @param string $name name of the test class to be executed.
     * @param array $testSuites The array of test suites.  Best practice is to use const provided 
     * by child class.
     * @param string $ext The file extension.  Best practice is to use const provided by child class.
     * @return bool True if the class name exists in multiple test suites.  Otherwise, 
     * we return false.
     */
    public static function testIfSame(string $name, array $testSuites, string $ext): bool {
        $count = 0;
        foreach($testSuites as $testSuite) {
            if(file_exists($testSuite.$name.$ext)) $count++;
            if($count >1) {
                Tools::info(
                    "You have files with the same name across test suites.  Cannot use built in filtering", 
                    Logger::ERROR, 
                    Tools::BG_RED
                );
                
                return true;
            }
        }
        return false;
    }

    /**
     * Run all test files in an individual test suite.
     *
     * @param array $collection All classes in a particular test suite.
     * @return int A value that indicates success, invalid, or failure.
     */
    public function testSuite(array $collection, string $testCommand): int {
        if(Arr::isEmpty($collection)) {
            return Command::FAILURE;
        }

        foreach($collection as $fileName) {
            $this->runTest($fileName, $testCommand);
        }

        return Command::SUCCESS;
    }

    /**
     * Determines if execution of a test suite(s) is successful.  The
     * result is determined by testing if the status value is set and 
     * its integer value is equal to Command::SUCCESS.
     *
     * @param array<int> $suiteStatuses Array of integers that indicates a 
     * test is successful.  
     * @return bool
     */
    public static function testSuiteStatus(array $suiteStatuses): bool {
        foreach($suiteStatuses as $status) {
            if(isset($status) && $status == Command::SUCCESS) return true;
        }
        return false;
    }
}