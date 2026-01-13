<?php
declare(strict_types=1);
namespace Console\Helpers\Testing;

/**
 * Collection of template stub files for vitest unit tests.
 */
class VitestStubs {
    /**
     * Stub for component and view tests.
     *
     * @return string The test file for component and view tests.
     */
    public static function componentAndViewTestStub(): string {
        return <<<JS
import { describe, it, expect } from "vitest";
import { render, screen } from "@testing-library/react";

test('Test description ...', () => {

});
JS;
    }

    /**
     * Stub for unit tests.
     *
     * @return string The test file for unit tests.
     */
    public static function unitTestStub(): string {
        return <<<JS
import { expect, test } from 'vitest'


test('Test description ...', () => {

});
JS;
    }
}