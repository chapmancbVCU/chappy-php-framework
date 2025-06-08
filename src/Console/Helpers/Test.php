<?php
declare(strict_types=1);
namespace Console\Helpers;
use Console\Helpers\Tools;
use Core\Lib\Utilities\Arr;
use Core\Lib\Utilities\Str;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Supports unit test related console commands.
 */
class Test {
    public const ALLOWED_OPTIONS = [
        'coverage', 'debug', 'testdox', 'stop-on-failure'
    ];

    public string $inputOptions;
    public OutputInterface $output;
    public const FEATURE_PATH = 'tests'.DS.'Feature'.DS;
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
            Tools::info("No test available to perform", 'debug', 'yellow');
            return Command::FAILURE;
        }

        Test::testSuite($unitTests);
        Test::testSuite($featureTests);

        Tools::info("All available test have been completed");
        return Command::SUCCESS;
    }

    /**
     * Returns array containing all filenames in Feature directory.
     *
     * @return array The array of all filenames in the Feature directory.
     */
    public static function featureTests(): array {
        return glob(Test::FEATURE_PATH.'*.php');
    }

    /**
     * The template for a new Feature test class that extends ApplicationTestCase.
     *
     * @param string $testName The name of the TestCase class.
     * @return string The contents for the new TestCase class.
     */
    public static function makeFeatureTest(string $testName): string {
        return '<?php
namespace Tests\Feature;
use Core\Lib\Testing\ApplicationTestCase;

/**
 * Unit tests
 */
class '.$testName.' extends ApplicationTestCase {
    
}
';
    }

    /**
     * The template for a new Unit Test class that extends TestCase.
     *
     * @param string $testName The name of the TestCase class.
     * @return string The contents for the new TestCase class.
     */
    public static function makeUnitTest(string $testName): string {
        return '<?php
namespace Tests\Unit;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests
 */
class '.$testName.' extends TestCase {
    
}
';
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
                    case 'testbox':
                        $args[] = '--testbox';
                        break;
                    case 'stop-on-failure':
                        $args[] = '--stop-on-failure';
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
        // Run a specific function
        if(Str::contains($testArg, '::')) {
            [$class, $method] = explode('::', $testArg);

            $path = Test::UNIT_PATH.$class.'.php';
            if(!file_exists($path)) { $path = Test::FEATURE_PATH.$class.'.php'; }

            if(file_exists($path)) {
                $command = escapeshellarg($path) . ' --filter ' . escapeshellarg($method);
                $this->runTest($command, $this->output);
                return Command::SUCCESS;
            } else {
                Tools::info("Test class file not found for '$class'", 'debug', 'yellow');
                return Command::FAILURE;
            }
        } 
        
        // Run the test class if it exists in feature, unit, or both.
        $unitStatus = self::singleFileWithinSuite(self::UNIT_PATH, $testArg);
        $featureStatus = self::singleFileWithinSuite(self::FEATURE_PATH, $testArg);
        if($unitStatus == Command::SUCCESS || $featureStatus == Command::SUCCESS) {
            Tools::info("Selected tests have been completed");
            return Command::SUCCESS;
        }

        // No such test class exists.
        if(!file_exists(self::UNIT_PATH.$testArg.'.php') && !file_exists(self::FEATURE_PATH.$testArg.'.php')) {
            Tools::info("The {$testArg} test file does not exist", 'debug', 'yellow');
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
    public function singleFileWithinSuite(string $suite = self::UNIT_PATH, string $testArg) {
        if(file_exists($suite.$testArg.'.php')) {
            $command = ' '.$suite.$testArg.'.php';
            $this->runTest($command, $this->output);
            return Command::SUCCESS;
        }
        return Command::FAILURE;
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
            $this->runTest($fileName, $this->output);
        }

        return Command::SUCCESS;
    }

    /**
     * Determines if execution of a test suite(s) is successful.
     *
     * @param integer|null $featureStatus Tracks if feature test are 
     * successful.
     * @param integer|null $unitStatus Tracks if unit test are successful.
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
        return glob(Test::UNIT_PATH.'*.php');
    }
}