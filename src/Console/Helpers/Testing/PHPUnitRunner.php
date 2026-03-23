<?php
declare(strict_types=1);
namespace Console\Helpers\Testing;

use Console\Console;
use Core\Lib\Utilities\Arr;
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
    public const TEST_COMMAND = 'php vendor/bin/phpunit';

    /**
     * File extension for PHPUnit unit tests.
     */
    public const TEST_FILE_EXTENSION = ".php";

    /**
     * Array of supported test file extensions.
     */
    public const TEST_FILE_EXTENSIONS = [self::TEST_FILE_EXTENSION];
    
    /**
     * The path for unit tests.
     */
    public const UNIT_PATH = 'tests'.DS.'Unit'.DS;

    /**
     * Array of available test suites.
     */
    public const TEST_SUITES = [
        self::FEATURE_PATH, self::UNIT_PATH   
    ];

    /**
     * Constructor
     *
     * @param InputInterface $input The Symfony InputInterface object.
     * @param OutputInterface $output The Symfony OutputInterface object.
     */
    public function __construct(InputInterface $input, OutputInterface $output) {
        parent::__construct($input, $output);
    }

    /**
     * Parses PHPUnit related arguments and ignore Symfony arguments.
     *
     * @return string A string containing the arguments to be provided to 
     * PHPUnit.
     */
    public function parseOptions(): string {
        $args = [];

        foreach(self::ALLOWED_OPTIONS as $allowed) {
            if($this->input->hasOption($allowed) && $this->input->getOption($allowed)) {
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
     * Run filtered test by function name.
     *
     * @param string $testArg The name of the class.
     * @return int A value that indicates success, invalid, or failure.
     */
    public function testByFilter(string $testArg): int {
        $message = "Enter particular test using filter syntax (::).";
        self::argOptionValidate($testArg, $message, $this->input, $this->output, ['testFilterNotation'], true);
        [$class, $method] = explode('::', $testArg);

        foreach(self::testSuites() as $testSuite) {
            $file = $testSuite.$class;
            if(file_exists($file.self::TEST_FILE_EXTENSION)) {
                $filter = "--filter " . escapeshellarg("{$class}::{$method}");
                $this->runTest($filter, self::TEST_COMMAND);
                return Command::SUCCESS;
            }
        }
        
        return Command::FAILURE;
    }
}