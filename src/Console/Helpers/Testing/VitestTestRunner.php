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
    public function allTests(array $testSuites, string|array $extensions, string $command): int {
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

        foreach($suites as $suite) {
            $this->testSuite($suite, $command);
        }

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
     * Run filtered test by line number.
     *
     * @param string $testArg The name of the test file.
     * @param array $testSuites An array of test suite paths.
     * @param array $extensions An array of file extensions supported by Vitest.
     * @return int A value that indicates success, invalid, or failure.
     */
    public function testByFilter(string $testArg, array $testSuites, array $extensions): int {
        if(!Str::contains($testArg, '::') || Str::contains($testArg, ':::')) {
            Tools::info("Syntax error when filtering.", Logger::DEBUG, Tools::BG_YELLOW);
            return Command::FAILURE;
        }

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

        return Command::FAILURE;
    }
}