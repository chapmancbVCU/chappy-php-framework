<?php
declare(strict_types=1);
namespace Console\Helpers\Testing;

use Console\Console;
use Core\Lib\Utilities\Arr;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class that contains functions that can be used by runner child classes.
 */
class TestRunner extends Console {
    /**
     * The array of options allowed as input for the test command.
     */
    public const ALLOWED_OPTIONS = [];

    /**
     * A string of input options provided as input when running the 
     * test command.
     *
     * @var InputInterface
     */
    protected InputInterface $input;

    /**
     * The Symfony OutputInterface object.
     *
     * @var OutputInterface 
     */
    protected OutputInterface $output;

    /**
     * Test command for unit testing framework.
     */
    public const TEST_COMMAND = "";

    /**
     * Array of supported test file extensions.
     */
    public const TEST_FILE_EXTENSIONS = [];

    /**
     * Array of available test suites.
     */
    public const TEST_SUITES = [];

    /**
     * Constructor
     *
     * @param InputInterface $input The Symfony InputInterface object.
     * @param OutputInterface $output The Symfony OutputInterface object.
     */
    public function __construct(InputInterface $input, OutputInterface $output) {
        $this->input = $input;
        $this->output = $output;
    }

    /**
     * Performs all available tests.
     *
     * @return int A value that indicates success, invalid, or failure.
     */
    public function allTests(): int {
        $suites = [];

        foreach(static::testSuites() as $testSuite) {
            foreach(static::testFileExtensions() as $extension) {
                $suites[] = self::getAllTestsInSuite($testSuite, $extension);
            }
        }

        if($this->areAllSuitesEmpty($suites)) {
            self::noAvailableTestsMessage();
            return Command::SUCCESS;
        }

        $statuses = [];
        foreach($suites as $suite) {
            $statuses[] = $this->testSuite($suite);
        }

        if($this->testSuiteStatus($statuses)) {
            console_info("All available test have been completed");
            return Command::SUCCESS;
        }

        return Command::FAILURE;
    }

    /**
     * Test to ensure there is not an empty test suite.
     *
     * @param array $testSuites The collection of all available test suites.  
     * Best practice is to use const provided by child class.
     * @return bool True if all test suites are empty.  Otherwise, we return 
     * false.
     */
    public function areAllSuitesEmpty(array $testSuites): bool {
        $flattened = Arr::collapse($testSuites);
        if(Arr::isNotEmpty($flattened)) return false; 
        else return true;
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
        console_warning("You have more than one test file with the same name.");
    }

    /**
     * Generates filter when testing by particular line number/function 
     * within a test file
     *
     * @param string $file The name of the test file.
     * @param string $location Line number/function within the test file
     * @param string $extension The file extension.  Default value is an 
     * empty string.
     * @return string The formatted string for filtering tests by location 
     * within a file.
     */
    protected static function filter(string $file, string $location, string $extension = ""): string { return ""; }

    /**
     * Retrieves all files in test suite so they can be run.
     *
     * @param string $path Path to test suite.
     * @param string $ext File extension to specify between php and js related 
     * tests.  Best practice is to use const provided by child class.
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
    private static function noAvailableTestsMessage(): void {
        console_notice("No test available to perform");
    }

    /**
     * Parses related arguments and ignore Symfony arguments.
     *
     * @return string A string containing the arguments to be provided to 
     * test command.
     */
    public function parseOptions(): string { return ""; }

    /**
     * Runs the unit test for your testing suite.
     *
     * @param string $test The test to be performed.
     * @return void
     */
    public function runTest(string $test): void {
        $command = self::testCommand() . ' ' . $test . $this->parseOptions();
        console_info('File: '.$test);
        $this->output->writeln(shell_exec($command));
    }

    /**
     * Supports ability to run test by class/file name.
     *
     * @param string $testArg The name of the class/file.
     * @return int A value that indicates success, invalid, or failure.
     */
    public function selectByTestName(string $testArg): int {
        $statuses = [];

        foreach(self::testSuites() as $testSuite) {
            foreach(self::testFileExtensions() as $extension) {
                $statuses[] = $this->singleFileWithinSuite($testArg, $testSuite, $extension);    
            }
        }

        if(self::testSuiteStatus($statuses)) {
            console_info("Selected tests have been completed");
            return Command::SUCCESS;
        }

        // No such test class exists.
        if(!$this->testExists($testArg)) {
            console_warning(
                "The {$testArg} test file does not exist.", 
            );
            return Command::FAILURE;
        }
        
        return Command::FAILURE;
    }

    /**
     * Performs testing against a single class within a test suite.
     *
     * @param string $testArg The name of the test file without extension.
     * @param string $testSuite The name of the test suite.  Best practice is 
     * to use const provided by child class.
     * @param string $ext The file extension.  Best practice is to use const provided by child class.
     * @return int A value that indicates success, invalid, or failure.
     */
    public function singleFileWithinSuite(string $testArg, string $testSuite, string $ext): int {
        if(file_exists($testSuite.$testArg.$ext)) {
            $test = ' '.$testSuite.$testArg.$ext;
            $this->runTest($test);
            return Command::SUCCESS;
        }
        return Command::FAILURE;
    }
    
    /**
     * Run filtered test by line number or function within a test file.  This 
     * function works across multiple test suite types and supports multiple 
     * file extensions.  Correct filter syntax must be implemented in separate 
     * filter() function that must be implemented before this function can 
     * successfully execute.
     *
     * @param string $testArg The name of the test file.
     * @return int A value that indicates success, invalid, or failure.
     */
    public function testByFilter(string $testArg): int {
        $message = "Enter particular test using filter syntax (::).";
        Console::argOptionValidate($testArg, $message, $this->input, $this->output, ['testFilterNotation'], true);

        [$testFile, $location] = explode('::', $testArg);
        if(self::testIfSame($testFile)) return Command::FAILURE; 
        
        $exists = false;
        foreach(self::testSuites() as $testSuite) {
            $file = $testSuite.$testFile;
            foreach(self::testFileExtensions() as $extension) {
                if(file_exists($file.$extension)) {
                    $filter = static::filter($file, $location, $extension);
                    $exists = true;
                    break;
                }
            }
        }
            
        if($exists) {
            $this->runTest($filter);
            return Command::SUCCESS;
        }
        return Command::FAILURE;
    }

    /**
     * Returns value of TEST_COMMAND constant.
     *
     * @return string The test command string.
     */
    public static function testCommand(): string {
        return static::TEST_COMMAND;
    }

    /**
     * Determine if test file exists in any of the available test suites.
     *
     * @param string $name The name of the test we want to confirm if it exists.
     * @return bool True if test does exist.  Otherwise, we return false.
     */
    public static function testExists(string $name): bool {
        $count = 0; 
        foreach(self::testSuites() as $testSuite) {
            foreach(self::testFileExtensions() as $extension) {
                if(file_exists($testSuite.$name.$extension)) $count++;
                if($count > 0) return true;
            }
        }
        return false;
    }

    /**
     * Returns array of supported test file extensions.
     *
     * @return array An array of supported test file extensions.
     */
    public static function testFileExtensions(): array {
        return static::TEST_FILE_EXTENSIONS;
    }

    /**
     * Enforces rule that classes/files across test suites should be unique for filtering.
     *
     * @param string $name Name of the test class to be executed.
     * @return bool True if the class or file name exists in multiple test suites.  Otherwise, 
     * we return false.
     */
    public static function testIfSame(string $name): bool {
        $count = 0;
        foreach(self::testSuites() as $testSuite) {
            foreach(self::testFileExtensions() as $extension) {
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
    public function testSuite(array $collection): int {
        if(Arr::isEmpty($collection)) {
            return Command::SUCCESS;
        }

        foreach($collection as $fileName) {
            $this->runTest($fileName);
        }

        return Command::SUCCESS;
    }

    /**
     * Return array of available test suites.
     *
     * @return array
     */
    public static function testSuites(): array {
        return static::TEST_SUITES;
    }

    /**
     * Determines if execution of a test suite(s) is successful.  The
     * result is determined by testing if the status value is set and 
     * its integer value is equal to Command::SUCCESS.
     *
     * @param array<int> $suiteStatuses Array of integers that indicates a 
     * test is successful.  
     * @return bool True if execution is successful.  Otherwise, we return 
     * false.
     */
    public static function testSuiteStatus(array $suiteStatuses): bool {
        foreach($suiteStatuses as $status) {
            if(isset($status) && $status == Command::SUCCESS) return true;
        }
        return false;
    }
}