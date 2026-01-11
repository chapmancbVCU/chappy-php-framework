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

        if(Arr::isEmpty($unitTests) && Arr::isEmpty($featureTests)) {
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
    public function selectTests(string $testArg): int {
        if(!$this->verifyFilterSyntax($testArg)) {
            return Command::FAILURE;
        }
        
        // Run a specific function in a class.
        if(Str::contains($testArg, '::')) {
            [$class, $method] = explode('::', $testArg);

            if(self::testIfSame($class, [self::FEATURE_PATH, self::UNIT_PATH], self::TEST_FILE_EXTENSION)) { 
                return Command::FAILURE; 
            }

            $namespaces = [
                'Tests\\Unit\\' => self::UNIT_PATH,
                'Tests\\Feature\\' => self::FEATURE_PATH
            ];

            foreach ($namespaces as $namespace => $path) {
                $file = $path . $class . '.php';
                if (file_exists($file)) {
                    $filter = "--filter " . escapeshellarg("{$class}::{$method}");
                    $this->runTest($filter, self::TEST_COMMAND);
                    return Command::SUCCESS;
                }
            }

        } 
        
        // Run the test class if it exists in a specific suite.
        $unitStatus = self::singleFileWithinSuite($testArg, self::UNIT_PATH, self::TEST_FILE_EXTENSION, self::TEST_COMMAND);
        $featureStatus = self::singleFileWithinSuite($testArg, self::FEATURE_PATH, self::TEST_FILE_EXTENSION, self::TEST_COMMAND);
        if($this->didTestInSuiteSucceed([$unitStatus, $featureStatus])) {
            Tools::info("Selected tests have been completed");
            return Command::SUCCESS;
        }

        // No such test class exists.
        if(!$this->testExists($testArg, [self::FEATURE_PATH, self::UNIT_PATH], self::TEST_FILE_EXTENSION)) {
            Tools::info("The {$testArg} test file does not exist", Logger::DEBUG, Tools::BG_YELLOW);
            return Command::FAILURE;
        }
        
        return Command::FAILURE;
    }
}