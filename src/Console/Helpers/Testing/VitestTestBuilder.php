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
        if(self::testIfExists($testName)) {
            return Command::FAILURE;
        }

        if($input->getOption('component')) {
            return Tools::writeFile(
                ROOT.DS.VitestTestRunner::COMPONENT_PATH.$testName.'.test.js',
                VitestStubs::unitTestStub($testName),
                'Test'
            );
        } else if($input->getOption('unit')) {
            return Tools::writeFile(
                ROOT.DS.VitestTestRunner::UNIT_PATH.$testName.'.test.js',
                VitestStubs::unitTestStub($testName),
                'Test'
            );
        } else {
            return Tools::writeFile(
                ROOT.DS.VitestTestRunner::VIEW_PATH.$testName.'.test.js',
                VitestStubs::unitTestStub($testName),
                'Test'
            );
        }
        
        return Command::SUCCESS;
    }

    public static function testIfExists(string $name): bool { return false; }
}