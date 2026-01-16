<?php
declare(strict_types=1);
namespace Console\Helpers\Testing;

use Console\Helpers\Tools;
use Core\Lib\Logging\Logger;
use Core\Lib\Utilities\Arr;
use Core\Lib\Utilities\Str;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Supports PHPUnit testing operations.
 */
final class PHPUnitRunner extends TestRunner {
    /**
     * The array of options allowed as input for the test command.
     */
    public const ALLOWED_OPTIONS = [
        'coverage',
        'debug',
        'display-deprecations',
        'display-errors',
        'display-incomplete',
        'display-skipped',
        'fail-on-incomplete',
        'fail-on-risky',
        'random-order',
        'reverse-order',
        'stop-on-error',
        'stop-on-failure',
        'stop-on-incomplete',
        'stop-on-risky',
        'stop-on-skipped',
        'stop-on-warning',
        'testdox',
    ];

    /**
     * The path for feature tests.
     */
    public const FEATURE_PATH = 'tests'.DS.'Feature'.DS;

    /**
     * The command for PHPUnit
     */
    public const TEST_COMMAND = 'php vendor/bin/phpunit ';

    /**
     * File extension for PHPUnit unit tests.
     */
    public const TEST_FILE_EXTENSION = ".php";
    
    /**
     * The path for unit tests.
     */
    public const UNIT_PATH = 'tests'.DS.'Unit'.DS;

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
        $unitTests = self::getAllTestsInSuite(self::UNIT_PATH, self::TEST_FILE_EXTENSION);
        $featureTests = self::getAllTestsInSuite(self::FEATURE_PATH, self::TEST_FILE_EXTENSION);

        if($this->areAllSuitesEmpty([$unitTests, $featureTests])) {
            $this->noAvailableTestsMessage();
            return Command::FAILURE;
        }

        $this->testSuite($unitTests, self::TEST_COMMAND);
        $this->testSuite($featureTests, self::TEST_COMMAND);

        Tools::info("All available test have been completed");
        return Command::SUCCESS;
    }

    /**
     * Parses PHPUnit related arguments and ignore Symfony arguments.
     *
     * @param InputInterface $input Instance of InputInterface from command.
     * @return string A string containing the arguments to be provided to 
     * PHPUnit.
     */
    public static function parseOptions(InputInterface $input): string {
        $args = [];

        foreach(self::ALLOWED_OPTIONS as $allowed) {
            if($input->hasOption($allowed) && $input->getOption($allowed)) {
                switch ($allowed) {
                    case 'coverage':
                        $args[] = '--coverage-text';
                        break;
                    case 'debug':
                        $args[] = '--debug';
                        break;
                    case 'display-deprecations':
                        $args[] = '--display-deprecations';
                        break;
                    case 'display-errors':
                        $args[] = '--display-errors';
                        break;
                    case 'display-incomplete':
                        $args[] = '--display-incomplete';
                        break;
                    case 'display-skipped':
                        $args[] = '--display-skipped';
                        break;
                    case 'fail-on-incomplete':
                        $args[] = '--fail-on-incomplete';
                        break;
                    case 'fail-on-risky':
                        $args[] = '--fail-on-risky';
                        break;
                    case 'random-order':
                        $args[] = '--random-order';
                        break;
                    case 'reverse-order':
                        $args[] = '--reverse-order';
                        break;
                    case 'stop-on-error':
                        $args[] = '--stop-on-error';
                        break;
                    case 'stop-on-failure':
                        $args[] = '--stop-on-failure';
                        break;
                    case 'stop-on-incomplete':
                        $args[] = '--stop-on-incomplete';
                        break;
                    case 'stop-on-risky':
                        $args[] = '--stop-on-risky';
                        break;
                    case 'stop-on-skipped':
                        $args[] = '--stop-on-skipped';
                        break;
                    case 'stop-on-warning':
                        $args[] = '--stop-on-warning';
                        break;
                    case 'testdox':
                        $args[] = '--testdox';
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
     * Supports ability to run test by class name or function name within 
     * a class.
     *
     * @param string $testArg The name of the class or class::test_name.
     * @return int A value that indicates success, invalid, or failure.
     */
    public function selectTests(string $testArg, array $testSuites, string|array $extensions): int {
        // Run a specific function in a class.
        if(Str::contains($testArg, '::')) {
            [$testFile, $location] = explode('::', $testArg);

            if(self::testIfSame($testFile, $testSuites, $extensions)) { 
                return Command::FAILURE; 
            }

            $exists = false;
            foreach($testSuites as $testSuite) {
                $file = $testSuite.$testFile;
                if(file_exists($file.self::TEST_FILE_EXTENSION)) {
                    $filter = "--filter " . escapeshellarg("{$testFile}::{$location}");
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

        // No such test class exists.
        if(!$this->testExists($testArg, $testSuites, $extensions)) {
            Tools::info("The {$testArg} test file does not exist or missing :: syntax error when filtering.", Logger::DEBUG, Tools::BG_YELLOW);
            return Command::FAILURE;
        }
        
        return Command::FAILURE;
    }
}