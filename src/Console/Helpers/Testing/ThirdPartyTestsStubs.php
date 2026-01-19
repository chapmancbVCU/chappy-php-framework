<?php
declare(strict_types=1);
namespace Console\Helpers\Testing;

/**
 * Contains stubs for generating classes associated with third party unit 
 * testing frameworks.
 */
class ThirdPartyTestsStubs {
    /**
     * Stub for custom test builder class.
     *
     * @param string $className
     * @return string The contents of the new test builder class.
     */
    public static function builderStub(string $className): string {
        return <<<PHP
namespace App\CustomTests\Testing;

use Console\Helpers\Testing\TestBuilderInterface;
use Symfony\Component\Console\Input\InputInterface;

class {$className} implements TestBuilderInterface {

    public static function makeTest(string \$testName, InputInterface \$input): int {

    }
}
PHP;
    }
}