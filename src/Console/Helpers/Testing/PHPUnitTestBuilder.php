<?php
declare(strict_types=1);
namespace Console\Helpers\Testing;

use Console\Helpers\Tools;
use Console\Helpers\Testing\PHPUnitStubs;
use Symfony\Component\Console\Command\Command;
use Console\Console;
use Console\FrameworkQuestion;

/**
 * Utility class that supports ability to create new test classes for PHPUnit 
 * based tests.
 */
class PHPUnitTestBuilder extends Console implements TestBuilderInterface {
    /**
     * Asks user if they want to create a test in the feature suite.
     *
     * @param mixed $feature The value of the --feature flag.
     * @param FrameworkQuestion $question Instance of FrameworkQuestion class.
     * @return mixed If the feature flat is set then $feature is returned.  
     * If the user answers yes then a string with the value "feature" is 
     * returned.  Otherwise, null is returned to match case when feature 
     * flag is not set.
     */
    public static function featurePrompt(mixed $feature, FrameworkQuestion $question): mixed {
        if($feature) return $feature;
        $message = "Do you want this to be a feature test? (y/n)";
        if(self::confirm($message, $question)) return "feature";
        return null;
    }

    /**
     * Creates a new test class.  When --feature flag is provided a test 
     * feature class is created.
     *
     * @param string $testName The name for the test.
     * @param mixed $feature The --feature flag.
     * @return int A value that indicates success, invalid, or failure.
     */
    public static function makeTest(string $testName, mixed $suite): int {
        $testSuites = [PHPUnitRunner::FEATURE_PATH, PHPUnitRunner::UNIT_PATH];
        
        if(PHPUnitRunner::testExists($testName, $testSuites, PHPUnitRunner::TEST_FILE_EXTENSION)) {
            console_warning("File with the name '{$testName}' already exists in one of the supported test suites");
            return Command::FAILURE;
        }

        if($suite) {
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