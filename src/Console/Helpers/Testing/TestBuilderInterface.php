<?php
declare(strict_types=1);
namespace Console\Helpers\Testing;

use Symfony\Component\Console\Input\InputInterface;

/**
 * Interface to be implemented by test builder classes for PHPUnit, Jest, and Vitest.
 */
interface TestBuilderInterface {
    /**
     * Creates a new test class.
     *
     * @param string $testName The name for the test.
     * @param string $suite The currently suite flag if available.
     * @return int A value that indicates success, invalid, or failure.
     */
    public static function makeTest(string $testName, string $suite): int;
}