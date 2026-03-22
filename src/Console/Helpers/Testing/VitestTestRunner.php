<?php
declare(strict_types=1);
namespace Console\Helpers\Testing;

use Console\Console;
use Core\Lib\Utilities\Arr;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Supports Vitest unit testing operations.
 */
final class VitestTestRunner extends TestRunner {
    /**
     * The array of options allowed as input for the test command.
     */
    public const ALLOWED_OPTIONS = [
        'bail',
        'clearCache',
        'coverage',
        'pass-with-no-tests',
        'retry',
        'update'
    ];

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
    public const TEST_COMMAND = "npx vitest run";

    /**
     * Path for unit tests.
     */
    public const UNIT_PATH = 'resources'.DS.'js'.DS.'tests'.DS.'unit'.DS;

    /**
     * File extension for Vitest unit tests.
     */
    public const UNIT_TEST_FILE_EXTENSION = ".test.js";

    /**
     * Array of supported test file extensions.
     */
    public const TEST_FILE_EXTENSIONS = [
        self::REACT_TEST_FILE_EXTENSION, self::UNIT_TEST_FILE_EXTENSION
    ];

    /**
     * Array of available test suites.
     */
    public const TEST_SUITES = [
        self::COMPONENT_PATH, self::UNIT_PATH, self::VIEW_PATH 
    ];

    /**
     * Constructor
     *
     * @param InputInterface $input The Symfony InputInterface object.
     * @param OutputInterface $output The Symfony OutputInterface object.
     */
    public function __construct(InputInterface $input, OutputInterface $output) {
        $this->input = $input;
        parent::__construct($output);
    }

    /**
     * Parses Vitest related arguments and ignore Symfony arguments.
     *
     * @param InputInterface $input Instance of InputInterface from command.
     * @return string A string containing the arguments to be provided to 
     * PHPUnit.
     */
    public function parseOptions(): string { 
        $args = [];

        foreach(self::ALLOWED_OPTIONS as $allowed) {
            if($this->input->hasOption($allowed) && $this->input->getOption($allowed)) {
                switch($allowed) {
                    case 'bail':
                        $args[] = '--bail ' . $this->input->getOption('bail');
                        break;
                    case 'clearCache':
                        $args[] = '--clearCache';
                        break;
                    case 'coverage':
                        $args[] = '--coverage';
                        break;
                    case 'pass-with-no-tests':
                        $args[] = '--pass-with-no-tests';
                        break;
                    case 'retry':
                        $args[] = '--retry ' . $this->input->getOption('retry');
                        break;
                    case 'update':
                        $args[] = '--update';
                        break;
                    default;
                        $args[] = '--' . $allowed;
                        break;
                }
            }
        }
        return (Arr::isEmpty($args)) ? '' : ' ' . implode(' ', $args);
    }

    /**
     * Run filtered test by line number.
     *
     * @param string $testArg The name of the test file.
     * @param array $extensions An array of file extensions supported by Vitest.
     * @param InputInterface $input The Symfony InputInterface object.
     * @param OutputInterface $output The Symfony OutputInterface object.
     * @return int A value that indicates success, invalid, or failure.
     */
    public function testByFilter(string $testArg, InputInterface $input, OutputInterface $output): int {
        $message = "Enter particular test using filter syntax (::).";
        Console::argOptionValidate($testArg, $message, $input, $output, ['testFilterNotation'], true);

        [$testFile, $location] = explode('::', $testArg);
        if(self::testIfSame($testFile, self::testSuites())) { 
            return Command::FAILURE; 
        }

        $exists = false;
        foreach(self::testSuites() as $testSuite) {
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