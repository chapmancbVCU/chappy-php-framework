<?php
declare(strict_types=1);
namespace Console\Helpers\Testing;

use Symfony\Component\Console\Input\InputInterface;

/**
 * Interface to be implemented by test builder classes for PHPUnit, Jest, and Vitest.
 */
interface TestBuilderInterface {
    /**
     * Creates a new test class.  When --feature flag is provided a test 
     * feature class is created.
     *
     * @param string $testName The name for the test.
     * @param InputInterface $input The Symfony InputInterface object.
     * @return int A value that indicates success, invalid, or failure.
     */
    public static function makeTest(string $testName, InputInterface $input): int;

    /**
     * Checks if file exists in either test suite.
     *
     * @param string $name The name of the file to be created.
     * @return bool True if file exists in either test suite, otherwise we 
     * return false.
     */
    public static function testIfExists(string $name): bool;
}