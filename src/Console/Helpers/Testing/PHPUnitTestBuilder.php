<?php
declare(strict_types=1);
namespace Console\Helpers\Testing;

use Core\Lib\Logging\Logger;
use Console\Helpers\Tools;
use Console\Helpers\Testing\PHPUnitStubs;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;

/**
 * Utility class that supports ability to create new test classes for PHPUnit 
 * based tests.
 */
class PHPUnitTestBuilder implements TestBuilderInterface {
    /**
     * Creates a new test class.  When --feature flag is provided a test 
     * feature class is created.
     *
     * @param string $testName The name for the test.
     * @param InputInterface $input The Symfony InputInterface object.
     * @return int A value that indicates success, invalid, or failure.
     */
    public static function makeTest(string $testName, InputInterface $input): int {
        $testSuites = [PHPUnitRunner::FEATURE_PATH, PHPUnitRunner::UNIT_PATH];
        
        if(PHPUnitRunner::testExists($testName, $testSuites, PHPUnitRunner::TEST_FILE_EXTENSION)) {
            console_warning("File with the name '{$testName}' already exists in one of the supported test suites");
            return Command::FAILURE;
        }

        if($input->getOption('feature')) {
            return Tools::writeFile(
                ROOT.DS.PHPUnitRunner::FEATURE_PATH.$testName.PHPUnitRunner::TEST_FILE_EXTENSION,
                PHPUnitStubs::featureTestStub($testName),
                'Test'
            );
        } else {
            return Tools::writeFile(
                ROOT.DS.PHPUnitRunner::UNIT_PATH.$testName.PHPUnitRunner::TEST_FILE_EXTENSION,
                PHPUnitStubs::unitTestStub($testName),
                'Test'
            );
        }

        return Command::FAILURE;
    }
}