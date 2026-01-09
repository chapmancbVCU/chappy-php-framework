<?php
declare(strict_types=1);
namespace Console\Helpers\Testing;

use Symfony\Component\Console\Input\InputInterface;

interface TestBuilderInterface {
    public static function makeTest(string $testName, InputInterface $input): int;
}