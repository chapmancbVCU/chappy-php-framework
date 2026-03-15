<?php
declare(strict_types=1);
namespace Console\Helpers\Testing;

use Console\Console;
use Console\Helpers\Tools;
use ReflectionClass;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;

/**
 * Utility class that supports ability to create new tests for Vitest based 
 * tests.
 */
class VitestTestBuilder extends Console implements TestBuilderInterface {
    private const COMPONENT = 'component';
    private const UNIT = 'unit';
    private const VIEW = 'view';

    /**
     * Creates a new test class.  Three types of test can be generated based 
     * on flag
     * 
     * 1. --component - Creates component test
     * 2. --unit - Creates unit test
     * 3. --view - Creates view test
     * 
     * @param string $testName The name for the test.
     * @param string $suite The flag for --unit, --component, or --view suites.
     * @return int A value that indicates success, invalid, or failure.
     */
    public static function makeTest(string $testName, mixed $suite): int { 
        $testSuites = [VitestTestRunner::COMPONENT_PATH, VitestTestRunner::UNIT_PATH, VitestTestRunner::VIEW_PATH];
        $extensions = [VitestTestRunner::REACT_TEST_FILE_EXTENSION, VitestTestRunner::UNIT_TEST_FILE_EXTENSION];

        if(VitestTestRunner::testExists($testName, $testSuites, $extensions)) {
            console_warning("File with the name '{$testName}' already exists in one of the supported test suites");
            return Command::FAILURE;
        }

        $component = $input->getOption('component');
        $unit = $input->getOption('unit');
        $view = $input->getOption('view');

        if($component && !$unit && !$view) {
            return Tools::writeFile(
                ROOT.DS.VitestTestRunner::COMPONENT_PATH.$testName.VitestTestRunner::REACT_TEST_FILE_EXTENSION,
                VitestStubs::componentAndViewTestStub(),
                'Component test'
            );
        } else if($unit && !$component && !$view) {
            return Tools::writeFile(
                ROOT.DS.VitestTestRunner::UNIT_PATH.$testName.VitestTestRunner::UNIT_TEST_FILE_EXTENSION,
                VitestStubs::unitTestStub(),
                'Unit test'
            );
        } else if($view && !$component && !$unit){
            return Tools::writeFile(
                ROOT.DS.VitestTestRunner::VIEW_PATH.$testName.VitestTestRunner::REACT_TEST_FILE_EXTENSION,
                VitestStubs::componentAndViewTestStub(),
                'View test'
            );
        } else {
            console_warning("More than one flag has been supplied.");
            return Command::FAILURE;
        }

        console_warning("Please use a flag to ensure test is created in intended test suite.");
        
        return Command::SUCCESS;
    }

    /**
     * Determines which suite is selected.  If more than one suit is selected then 
     * a message is displayed.
     *
     * @param InputInterface $input The Symfony InputInterface object.
     * @return string|null The name of the suite that was selected.  If more than 
     * one suite flag is set then null is returned.
     */
    public static function suite(InputInterface $input): string|null {
        $options = $input->getOptions();
        $reflectionClass = new ReflectionClass(__CLASS__);
        $constants = $reflectionClass->getConstants();

        $count = 0;
        $result = "";
        foreach($options as $k => $v) {
            if(array_keys($constants, $k) && $v == 1) {
                $result = $k;
                $count++;
            }
            if($count > 1) {
                console_debug("More than one suite flag was set.  Proceed to ask user which suite to use.");
                return null;
            }
        }
        return $result;
    }
}