<?php
declare(strict_types=1);
namespace Console\Helpers\Testing;

class JestStubs {
    public static function jestStub($testName): string {
        return <<<JS

JS;
    }
}