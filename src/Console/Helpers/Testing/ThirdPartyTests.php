<?php
declare(strict_types=1);
namespace Console\Helpers\Testing;

use Console\Helpers\Tools;

/**
 * Contains collection of functions that allows uses to add support for third 
 * parting unit testing frameworks.
 */
class ThirdPartyTests {
    public const THIRD_PARTY_TEST_PATH = ROOT.DS.'app'.DS.'CustomTests'.DS;

    /**
     * Generates a new unit test builder class.
     *
     * @param string $className Name for the new builder class.
     * @return int A value that indicates success, invalid, or failure.
     */
    public static function makeBuilder(string $className): int {
        return Tools::writeFile(
            self::THIRD_PARTY_TEST_PATH.'Testing'.DS.$className.".php",
            ThirdPartyTestsStubs::builderStub($className),
            "Test builder"
        );
    }

    /**
     * Generates a new unit test runner class.
     *
     * @param string $filePath The name for the new runner class.
     * @param string $className
     * @return int A value that indicates success, invalid, or failure.
     */
    public static function makeRunner(string $filePath, string $className): int {
        return Tools::writeFile(
            $filePath.$className.".php",
            ThirdPartyTestsStubs::runnerStub($className),
            "Test runner"
        );
    }
}