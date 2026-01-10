<?php
declare(strict_types=1);
namespace Console\Helpers\Testing;

class VitestStubs {
    public static function jestStub($testName): string {
        return <<<JS

JS;
    }
}