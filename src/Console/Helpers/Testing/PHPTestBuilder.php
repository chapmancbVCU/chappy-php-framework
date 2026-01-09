<?php
declare(strict_types=1);
namespace Console\Helpers\Testing;

use Core\Lib\Logging\Logger;
use Console\Helpers\Tools;
use Console\Helpers\Testing\PHPUnitStubs;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;

class PHPTestBuilder extends TestBuilderInterface {
    public const FEATURE_PATH = ROOT.DS.'tests'.DS.'Feature'.DS;
    public const UNIT_PATH = ROOT.DS.'tests'.DS.'Unit'.DS;

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
                self::FEATURE_PATH.$testName.'.php',
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
}