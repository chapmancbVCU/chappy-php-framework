<?php
declare(strict_types=1);
namespace Console\Helpers\Testing;

/**
 * Collection of template stub files for vitest unit tests.
 */
class VitestStubs {
    public static function unitTestStub($testName): string {
        return <<<JS
import { expect, test } from 'vitest'


test('Test description ...', () => {

});
JS;
    }
}