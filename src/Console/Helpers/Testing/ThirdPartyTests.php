<?php
declare(strict_types=1);
namespace Console\Helpers\Testing;

use Console\Helpers\Tools;

/**
 * Contains collection of functions that allows uses to add support for third 
 * parting unit testing frameworks.
 */
class ThirdPartyTests {
    public const THIRD_PARTY_TEST_PATH = ROOT.DS.'app'.DS.'Testing'.DS;

    /**
     * Generates a new unit test builder class.
     *
     * @param string $className Name for the new builder class.
     * @return int A value that indicates success, invalid, or failure.
     */
    public static function makeBuilder(string $className): int {
        Tools::pathExists(self::THIRD_PARTY_TEST_PATH);
        return Tools::writeFile(
            self::THIRD_PARTY_TEST_PATH.$className.".php",
            ThirdPartyTestsStubs::builderStub($className),
            "Test builder"
        );
    }

    /**
     * Generates a new unit test runner class.
     *
     * @param string $className The name for the new runner class.
     * @return int A value that indicates success, invalid, or failure.
     */
    public static function makeRunner(string $className): int {
        Tools::pathExists(self::THIRD_PARTY_TEST_PATH);
        return Tools::writeFile(
            self::THIRD_PARTY_TEST_PATH.DS.$className.".php",
            ThirdPartyTestsStubs::runnerStub($className),
            "Test runner"
        );
    }
}