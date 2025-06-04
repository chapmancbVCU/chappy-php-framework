<?php
declare(strict_types=1);
namespace Console\Helpers;
use Console\Helpers\Tools;
use Core\Lib\Utilities\Arr;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Supports unit test related console commands.
 */
class Test {

    public const UNIT_PATH = 'tests'.DS.'Unit'.DS;
    public const FEATURE_PATH = 'tests'.DS.'Feature'.DS;
    
    public static function allTests(
        string $feature, 
        OutputInterface $output, 
        null|string $testArg, 
        null|string $unit
        ): bool {
        // Get classes
        $unitTests = glob(Test::UNIT_PATH.'*.php');
        $featureTests = glob(Test::FEATURE_PATH.'*.php');

        if(!$unit && !$feature && !$testArg) {
            Test::testSuite($output, $unitTests);
            Test::testSuite($output, $featureTests);
            return true;
        }
        return false;
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
     * @return int A value that indicates success, invalid, or failure.
     */
    public static function runTest(string $tests, OutputInterface $output): int {
        $command = 'php vendor/bin/phpunit '.$tests;
        Tools::info('File: '.$tests);
        $output->writeln(shell_exec($command));
        return Command::SUCCESS;
    }

    public static function testSuite(OutputInterface $output, array $collection): int {
        foreach($collection as $fileName) {
            self::runTest($fileName, $output);
        }
        return Command::SUCCESS;
    }
}