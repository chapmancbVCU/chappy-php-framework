<?php
declare(strict_types=1);
namespace Console\Helpers\Testing;

use Console\Helpers\Tools;


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

    // public static function makeRunner(string $filePath, string $className): int {
    //     return Tools::writeFile(
    //         $filePath.$className.".php",
    //         ThirdPartyTestsStubs::builderStub($className),
    //         "Test builder"
    //     );
    // }
}