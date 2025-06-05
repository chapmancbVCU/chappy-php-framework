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

    public const UNIT_PATH = 'tests'.DS.'Unit'.DS;
    public const FEATURE_PATH = 'tests'.DS.'Feature'.DS;
    
    /**
     * Performs all tests.
     *
     * @param OutputInterface $output The output.
     * @return int A value that indicates success, invalid, or failure.
     */
    public static function allTests(OutputInterface $output): int {
        $unitTests = self::unitTests();
        $featureTests = self::featureTests();

        if(Arr::isEmpty($unitTests) && Arr::isEmpty($featureTests)) {
            Tools::info("No test available to perform", 'debug', 'yellow');
            return Command::FAILURE;
        }

        Test::testSuite($output, $unitTests);
        Test::testSuite($output, $featureTests);

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
     * The template for a new TestCase class.
     *
     * @param string $testName The name of the TestCase class.
     * @param string $type The type of test.
     * @return string The contents for the new TestCase class.
     */
    public static function makeTest(string $testName, $type): string {
        return '<?php
namespace Tests\\'.$type.';
use PHPUnit\Framework\TestCase;

/**
 * Unit tests
 */
class '.$testName.' extends TestCase {
    
}
';
    }

    /**
     * Runs the unit test contained in the TestCase class
     *
     * @param InputInterface $input Input obtained from the console used to 
     * set name of unit test we want to run.
     * @param OutputInterface $output The results of the test.
     */
    public static function runTest(string $tests, OutputInterface $output): void {
        $command = 'php vendor/bin/phpunit '.$tests;
        Tools::info('File: '.$tests);
        $output->writeln(shell_exec($command));
    }

    /**
     * Supports ability to run test by class name or function name within 
     * a class.
     *
     * @param OutputInterface $output
     * @param string $testArg The name of the class or class::test_name.
     * @return int A value that indicates success, invalid, or failure.
     */
    public static function selectTests(OutputInterface $output, string $testArg): int {
        // Run a specific function
        if(Str::contains($testArg, '::')) {
            [$class, $method] = explode('::', $testArg);

            $path = Test::UNIT_PATH.$class.'.php';
            if(!file_exists($path)) { $path = Test::FEATURE_PATH.$class.'.php'; }

            if(file_exists($path)) {
                $command = escapeshellarg($path) . ' --filter ' . escapeshellarg($method);
                Test::runTest($command, $output);
                Tools::info("The selected test has been completed");
                return Command::SUCCESS;
            } else {
                Tools::info("Test class file not found for '$class'", 'debug', 'yellow');
                return Command::FAILURE;
            }
        } 
        
        // Run the test class if it exists in feature, unit, or both.
        if(file_exists(Test::UNIT_PATH.$testArg.'.php')) {
            $command = ' '.Test::UNIT_PATH.$testArg.'.php';
            Test::runTest($command, $output);
        } 

        $command = '';
        if(file_exists(Test::FEATURE_PATH.$testArg.'.php')) {
            $command = ' '.Test::FEATURE_PATH.$testArg.'.php';
            Test::runTest($command, $output);
        }
        
        // No such test class exists.
        if(!file_exists(Test::UNIT_PATH.$testArg.'.php') && !file_exists(Test::FEATURE_PATH.$testArg.'.php')) {
            Tools::info("Test does not exist", 'debug', 'yellow');
            return Command::FAILURE;
        }
        
        Tools::info("Selected tests have been completed");
        return Command::SUCCESS;
    }

    /**
     * Run all test files in an individual test suite.
     *
     * @param OutputInterface $output The output.
     * @param array $collection All classes in a particular test suite.
     */
    public static function testSuite(OutputInterface $output, array $collection): void {
        if(Arr::isEmpty($collection)) {
            return;
        }
        foreach($collection as $fileName) {
            self::runTest($fileName, $output);
        }
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