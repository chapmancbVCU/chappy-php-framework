<?php
declare(strict_types=1);
namespace Console\Helpers\Testing;

use Core\Lib\Utilities\Arr;
use Console\Helpers\Tools;
use Core\Lib\Logging\Logger;
use Core\Lib\Utilities\Str;
use Symfony\Component\Console\Command\Command;
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
     * @param OutputInterface $output The Symfony OutputInterface object.
     */
    public function __construct(OutputInterface $output) {
        $this->output = $output;
    }

    /**
     * Test to ensure there is not an empty test suite.
     *
     * @param array $testSuites The collection of all available test suites.
     * @return bool True if all test suites are empty.  Otherwise, we return 
     * false.
     */
    public function areAllSuitesEmpty(array $testSuites): bool {
        $flattened = Arr::collapse($testSuites);
        if(Arr::isNotEmpty($flattened)) return false; 
        else return true;
    }

    /**
     * Performs all tests.
     *
     * @param array $testSuites An array of test suite paths.
     * @param string|array $extensions An array of supported file extensions.
     * @param string $testCommand The command for running the tests.
     * @return int A value that indicates success, invalid, or failure.
     */
    public function allTests(array $testSuites, string|array $extensions, string $testCommand): int {
        $suites = [];

        if(is_array($extensions)) {
            foreach($testSuites as $testSuite) {
                foreach($extensions as $extension) {
                    $suites[] = self::getAllTestsInSuite($testSuite, $extension);
                }
            }
        } else {
            foreach($testSuites as $testSuite) {
                $suites[] = self::getAllTestsInSuite($testSuite, $extensions);
            }
        }

        if($this->areAllSuitesEmpty($suites)) {
            $this->noAvailableTestsMessage();
            return Command::FAILURE;
        }

        $statuses = [];
        foreach($suites as $suite) {
            $statuses[] = $this->testSuite($suite, $testCommand);
        }

        if($this->testSuiteStatus($statuses)) {
            Tools::info("All available test have been completed");
            return Command::SUCCESS;
        }

        return Command::FAILURE;
    }
    /**
     * Present message to the user if the following conditions are true:
     * - Test case files in multiple suites with the same name
     * - There exists a function in both classes with the same name
     * 
     * This function is called internally by the selectTest function.
     * @return void
     */
    private static function duplicateTestNameMessage(): void {
        Tools::info(
            "You have the same test name in files with the same name.  Cannot use built in filtering", 
            Logger::ERROR, 
            Tools::BG_RED
        );
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
    private function noAvailableTestsMessage(): void {
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
     * Supports ability to run test by class/file name.
     *
     * @param string $testArg The name of the class/file.
     * @param array $testSuites An array of test suite paths.
     * @param string|array $extensions An array of supported file extensions.
     * @param string $testCommand The command for running the tests.
     * @return int A value that indicates success, invalid, or failure.
     */
    public function selectByTestName(
        string $testArg, 
        array $testSuites, 
        string|array $extensions, 
        string $testCommand
    ): int {

        $statuses = [];
        if(is_array($extensions)) {
            foreach($testSuites as $testSuite) {
                foreach($extensions as $ext) {
                    $statuses[] = self::singleFileWithinSuite($testArg, $testSuite, $ext, $testCommand);    
                }
            }
        } else {
            foreach($testSuites as $testSuite) {
                $statuses[] = self::singleFileWithinSuite($testArg, $testSuite, $extensions, $testCommand);
            }
        }

        if($this->testSuiteStatus($statuses)) {
            Tools::info("Selected tests have been completed");
            return Command::SUCCESS;
        }

        // No such test class exists.
        if(!$this->testExists($testArg, $testSuites, $extensions)) {
            Tools::info(
                "The {$testArg} test file does not exist or missing :: syntax error when filtering.", 
                Logger::DEBUG, 
                Tools::BG_YELLOW
            );
            return Command::FAILURE;
        }
        
        return Command::FAILURE;
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
     * Determine if test file exists in any of the available test suites.
     *
     * @param string $name The name of the test we want to confirm if it exists.
     * @param array $testSuites The array of test suites.  Best practice is to use const provided 
     * by child class.
     * @param string|array $extension The file extension or array of file extensions.  
     * Best practice is to use const provided by child class.
     * @return bool True if test does exist.  Otherwise, we return false.
     */
    public static function testExists(string $name, array $testSuites, string|array $extension): bool {
        $count = 0;
        if(is_array($extension)) {    
            foreach($testSuites as $testSuite) {
                foreach($extension as $ext) {
                    if(file_exists($testSuite.$name.$ext)) $count++;
                    if($count > 0) return true;
                }
            }
        } else {
            foreach($testSuites as $testSuite) {
                if(file_exists($testSuite.$name.$extension)) $count++;
                if($count > 0) return true;
            }
        }
        return false;
    }

    /**
     * Enforces rule that classes/files across test suites should be unique for filtering.
     *
     * @param string $name name of the test class to be executed.
     * @param array $testSuites The array of test suites.  Best practice is to use const provided 
     * by child class.
     * @param string $extension The file extension.  Best practice is to use const provided by child class.
     * @return bool True if the class name exists in multiple test suites.  Otherwise, 
     * we return false.
     */
    public static function testIfSame(string $name, array $testSuites, string|array $extension): bool {
        $count = 0;
        if(is_array($extension)) {
            foreach($testSuites as $testSuite) {
                foreach($extension as $ext) {
                    if(file_exists($testSuite.$name.$ext)) $count++;
                    if($count > 1) {
                        self::duplicateTestNameMessage();
                        return true;
                    }
                }
            }
        } else {
            foreach($testSuites as $testSuite) {
                if(file_exists($testSuite.$name.$extension)) $count++;
                if($count > 1) {
                    self::duplicateTestNameMessage();
                    return true;
                }
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

    /**
     * Ensure filter syntax is correct.  Does not test if only one : is in string.
     *
     * @param string $testArg The name of the test file with filter.
     * @return bool True if filter syntax is correct.  Otherwise, we return false.
     */
    public static function verifyFilterSyntax($testArg): bool {
        return (Str::contains($testArg, '::') && !Str::contains($testArg, ':::'));
    }
}