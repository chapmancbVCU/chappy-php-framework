<?php
declare(strict_types=1);
namespace Console\Helpers\Testing;

use Core\Lib\Logging\Logger;
use Console\Helpers\Tools;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;

/**
 * Utility class that supports ability to create new tests for Vitest based 
 * tests.
 */
class VitestTestBuilder implements TestBuilderInterface {
    /**
     * Creates a new test class.  Three types of test can be generated based 
     * on flag
     * 
     * 1. --component - Creates component test
     * 2. --unit - Creates unit test
     * 3. --view - Creates view test
     * 
     * @param string $testName The name for the test.
     * @param InputInterface $input The Symfony InputInterface object.
     * @return int A value that indicates success, invalid, or failure.
     */
    public static function makeTest(string $testName, InputInterface $input): int { 
        $testSuites = [VitestTestRunner::COMPONENT_PATH, VitestTestRunner::UNIT_PATH, VitestTestRunner::VIEW_PATH];
        $extensions = [VitestTestRunner::REACT_TEST_FILE_EXTENSION, VitestTestRunner::UNIT_TEST_FILE_EXTENSION];

        if(VitestTestRunner::testExists($testName, $testSuites, $extensions)) {
            Tools::info("File with the name '{$testName}' already exists in one of the supported test suites", Logger::ERROR, Tools::BG_RED);
            return Command::FAILURE;
        }

        if($input->getOption('component')) {
            return Tools::writeFile(
                ROOT.DS.VitestTestRunner::COMPONENT_PATH.$testName.VitestTestRunner::REACT_TEST_FILE_EXTENSION,
                VitestStubs::componentAndViewTestStub(),
                'Component test'
            );
        } else if($input->getOption('unit')) {
            return Tools::writeFile(
                ROOT.DS.VitestTestRunner::UNIT_PATH.$testName.VitestTestRunner::UNIT_TEST_FILE_EXTENSION,
                VitestStubs::unitTestStub(),
                'Unit test'
            );
        } else if($input->getOption('view')){
            return Tools::writeFile(
                ROOT.DS.VitestTestRunner::VIEW_PATH.$testName.VitestTestRunner::REACT_TEST_FILE_EXTENSION,
                VitestStubs::componentAndViewTestStub(),
                'View test'
            );
        } 

        Tools::info(
            "Please use a flag to ensure test is created in intended test suite.", 
            Logger::WARNING, 
            Tools::BG_YELLOW
        );
        
        return Command::SUCCESS;
    }
}