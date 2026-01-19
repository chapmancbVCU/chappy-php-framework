<?php
declare(strict_types=1);
namespace Console\Helpers\Testing;

class ThirdPartyTestsStubs {
    public static function builderStub(string $className) {
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