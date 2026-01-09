<?php
declare(strict_types=1);
namespace Console\Helpers\Testing;
use Console\Helpers\Tools;
use Console\Helpers\Testing\PHPUnitStubs;
use Core\Lib\Logging\Logger;
use Core\Lib\Utilities\Arr;
use Core\Lib\Utilities\Str;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Supports PHPUnit testing operations.
 */
class PHPUnitRunner {
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
     * The path for feature tests.
     */
    public const FEATURE_PATH = 'tests'.DS.'Feature'.DS;

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
        $this->output = $output;
    }

    /**
     * Performs all tests.
     *
     * @return int A value that indicates success, invalid, or failure.
     */
    public function allTests(): int {
        $unitTests = self::unitTests();
        $featureTests = self::featureTests();

        if(Arr::isEmpty($unitTests) && Arr::isEmpty($featureTests)) {
            Tools::info("No test available to perform", Logger::DEBUG, Tools::BG_YELLOW);
            return Command::FAILURE;
        }

        $this->testSuite($unitTests);
        $this->testSuite($featureTests);

        Tools::info("All available test have been completed");
        return Command::SUCCESS;
    }

    /**
     * Returns array containing all filenames in Feature directory.
     *
     * @return array The array of all filenames in the Feature directory.
     */
    public static function featureTests(): array {
        return glob(self::FEATURE_PATH.'*.php');
    }

    /**
     * Creates a new test class.  When --feature flag is provided a test 
     * feature class is created.
     *
     * @param string $testName The name for the test.
     * @param InputInterface $input The Symfony InputInterface object.
     * @return int A value that indicates success, invalid, or failure.
     */
    public static function makeTest(string $testName, InputInterface $input): int {
        if(self::testIfExists($testName)) {
            return Command::FAILURE;
        }

        if($input->getOption('feature')) {
            return Tools::writeFile(
                ROOT.DS.self::FEATURE_PATH.$testName.'.php',
                PHPUnitStubs::featureTestStub($testName),
                'Test'
            );
        } else {
            return Tools::writeFile(
                ROOT.DS.self::UNIT_PATH.$testName.'.php',
                PHPUnitStubs::unitTestStub($testName),
                'Test'
            );
        }

        return Command::FAILURE;
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
     * Runs the unit test contained in the TestCase class.
     *
     * @param string $tests The test to be performed.
     * @return void
     */
    public function runTest(string $tests): void {
        $command = 'php vendor/bin/phpunit ' . $tests . $this->inputOptions;
        Tools::info('File: '.$tests);
        $this->output->writeln(shell_exec($command));
    }

    /**
     * Supports ability to run test by class name or function name within 
     * a class.
     *
     * @param string $testArg The name of the class or class::test_name.
     * @return int A value that indicates success, invalid, or failure.
     */
    public function selectTests(string $testArg): int {
        // Run a specific function
        if(Str::contains($testArg, '::')) {
            [$class, $method] = explode('::', $testArg);

            if(self::testIfSame($class)) { return Command::FAILURE; }

            $namespaces = [
                'Tests\\Unit\\' => self::UNIT_PATH,
                'Tests\\Feature\\' => self::FEATURE_PATH
            ];

            foreach ($namespaces as $namespace => $path) {
                $file = $path . $class . '.php';
                if (file_exists($file)) {
                    $filter = "--filter " . escapeshellarg("{$class}::{$method}");
                    $this->runTest($filter);
                    return Command::SUCCESS;
                }
            }

        } 
        
        // Run the test class if it exists in feature, unit, or both.
        $unitStatus = self::singleFileWithinSuite($testArg, self::UNIT_PATH);
        $featureStatus = self::singleFileWithinSuite($testArg, self::FEATURE_PATH);
        if($unitStatus == Command::SUCCESS || $featureStatus == Command::SUCCESS) {
            Tools::info("Selected tests have been completed");
            return Command::SUCCESS;
        }

        // No such test class exists.
        if(!file_exists(self::UNIT_PATH.$testArg.'.php') && !file_exists(self::FEATURE_PATH.$testArg.'.php')) {
            Tools::info("The {$testArg} test file does not exist", Logger::DEBUG, Tools::BG_YELLOW);
            return Command::FAILURE;
        }
        
        return Command::FAILURE;
    }

    /**
     * Performs testing against a single class within a test suite.
     *
     * @param string $suite The name of the test suite.
     * @param string $testArg The name of the test class.
     * @return void
     */
    public function singleFileWithinSuite(string $testArg, string $suite = self::UNIT_PATH) {
        if(file_exists($suite.$testArg.'.php')) {
            $command = ' '.$suite.$testArg.'.php';
            $this->runTest($command);
            return Command::SUCCESS;
        }
        return Command::FAILURE;
    }

    /**
     * Checks if file exists in either test suite.
     *
     * @param string $name The name of the file to be created.
     * @return bool True if file exists in either test suite, otherwise we 
     * return false.
     */
    public static function testIfExists(string $name): bool {
        $testName = $name.'.php';
        if(file_exists(self::FEATURE_PATH.$testName) || file_exists(self::UNIT_PATH.$testName)) {
            Tools::info("File with the name '{$testName}' cannot exist in both test suites", Logger::ERROR, Tools::BG_RED);
            return true;
        }
        return false;
    }

    /**
     * Enforces rule that classes/files across test suites should be unique.
     *
     * @param string $name The name of the test class to be executed.
     * @return bool True if class/file exists across both test suites, 
     * otherwise we return false.
     */
    public static function testIfSame(string $name): bool {
        $testName = $name.'.php';
        if(file_exists(self::FEATURE_PATH.$testName) && file_exists(self::UNIT_PATH.$testName)) {
            Tools::info("You have files with the same name in both test suites.  Cannot use built in filtering", Logger::ERROR, Tools::BG_RED);
            return true;
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
            return Command::FAILURE;
        }

        foreach($collection as $fileName) {
            $this->runTest($fileName);
        }

        return Command::SUCCESS;
    }

    /**
     * Determines if execution of a test suite(s) is successful.
     *
     * @param int|null $featureStatus Tracks if feature test are 
     * successful.
     * @param int|null $unitStatus Tracks if unit test are successful.
     * @return bool True if successful, otherwise we return false.
     */
    public static function testSuiteStatus(int|null $featureStatus, int|null $unitStatus): bool {
        return (isset($unitStatus) && $unitStatus == Command::SUCCESS) || 
            (isset($featureStatus) && $featureStatus == Command::SUCCESS);
    }

    /**
     * Returns array containing all filenames in Unit directory.
     *
     * @return array The array of all filenames in the Unit directory.
     */
    public static function unitTests(): array {
        return glob(self::UNIT_PATH.'*.php');
    }
}