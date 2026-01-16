<?php
declare(strict_types=1);
namespace Console\Helpers\Testing;

use Console\Helpers\Tools;
use Core\Lib\Utilities\Arr;
use Core\Lib\Utilities\Str;
use Core\Lib\Logging\Logger;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Supports Vitest unit testing operations.
 */
final class VitestTestRunner extends TestRunner {
    /**
     * Path for component tests.
     */
    public const COMPONENT_PATH = 'resources'.DS.'js'.DS.'tests'.DS.'component'.DS;

    /**
     * File extension for component and view tests.
     */
    public const REACT_TEST_FILE_EXTENSION = ".test.jsx";

    /**
     * Path for view tests.
     */
    public const VIEW_PATH = 'resources'.DS.'js'.DS.'tests'.DS.'view'.DS;

    /**
     * The command for Vitest
     */
    public const TEST_COMMAND = "npm test ";

    /**
     * File extension for Vitest unit tests.
     */
    public const UNIT_TEST_FILE_EXTENSION = ".test.js";
    
    /**
     * Path for unit tests.
     */
    public const UNIT_PATH = 'resources'.DS.'js'.DS.'tests'.DS.'unit'.DS;

    /**
     * Constructor
     *
     * @param InputInterface $input The Symfony InputInterface object.
     * @param OutputInterface $output The Symfony OutputInterface object.
     */
    public function __construct(InputInterface $input, OutputInterface $output) {
        $this->inputOptions = self::parseOptions($input);
        parent::__construct($output);
    }

    /**
     * Performs all tests.
     *
     * @return int A value that indicates success, invalid, or failure.
     */
    public function allTests(): int {
        $componentTests = self::getAllTestsInSuite(self::COMPONENT_PATH, self::UNIT_TEST_FILE_EXTENSION);
        $unitTests = self::getAllTestsInSuite(self::UNIT_PATH, self::UNIT_TEST_FILE_EXTENSION);
        $viewTests = self::getAllTestsInSuite(self::VIEW_PATH, self::REACT_TEST_FILE_EXTENSION);

        if($this->areAllSuitesEmpty([$componentTests, $unitTests, $viewTests])) {
            $this->noAvailableTestsMessage();
            return Command::FAILURE;
        }

        $this->testSuite($componentTests, self::TEST_COMMAND);
        $this->testSuite($unitTests, self::TEST_COMMAND);
        $this->testSuite($viewTests, self::TEST_COMMAND);

        Tools::info("All available test have been completed");
        return Command::SUCCESS;
    }

    /**
     * Parses Vitest related arguments and ignore Symfony arguments.
     *
     * @param InputInterface $input Instance of InputInterface from command.
     * @return string A string containing the arguments to be provided to 
     * PHPUnit.
     */
    public static function parseOptions(InputInterface $input): string { return ""; }

    /**
     * Supports ability to run test by file name or function name within 
     * a class.
     *
     * @param string $testArg The name of the class or class::test_name.
     * @return int A value that indicates success, invalid, or failure.
     */
    public function selectTests(string $testArg, array $testSuites, string|array $extensions): int {
        // Run test at specific line and file.
        if(Str::contains($testArg, '::')) {
            [$testFile, $location] = explode('::', $testArg);

            if(self::testIfSame($testFile, $testSuites, $extensions)) { 
                return Command::FAILURE; 
            }
            $exists = false;
            foreach($testSuites as $testSuite) {
                $file = $testSuite.$testFile;
                if(file_exists($file.self::UNIT_TEST_FILE_EXTENSION)) {
                    $filter = $file.self::UNIT_TEST_FILE_EXTENSION.":".$location;
                    $exists = true;
                }
                if(file_exists($file.self::REACT_TEST_FILE_EXTENSION)) {
                    $filter = $file.self::REACT_TEST_FILE_EXTENSION.":".$location;
                    $exists = true;
                }

                if($exists) {
                    $this->runTest($filter, self::TEST_COMMAND);
                    return Command::SUCCESS;
                }
            }
        }

        // Run the test case fie if it exists in a specific suite then report results.
        $statuses = [];
        if(is_array($extensions)) {
            foreach($testSuites as $testSuite) {
                foreach($extensions as $ext) {
                    $statuses[] = self::singleFileWithinSuite($testArg, $testSuite, $ext, self::TEST_COMMAND);    
                }
            }
        } else {
            foreach($testSuites as $testSuite) {
                $statuses[] = self::singleFileWithinSuite($testArg, $testSuite, $extensions, self::TEST_COMMAND);
            }
        }

        if($this->didTestInSuiteSucceed($statuses)) {
            Tools::info("Selected tests have been completed");
            return Command::SUCCESS;
        }

        // No such test file exists.
        if(!$this->testExists($testArg, $testSuites, $extensions)) {
            Tools::info("The {$testArg} test file does not exist or missing :: syntax error when filtering.", Logger::DEBUG, Tools::BG_YELLOW);
            return Command::FAILURE;
        }
        return Command::FAILURE;
    }
}