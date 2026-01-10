<?php
declare(strict_types=1);
namespace Console\Helpers\Testing;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;

class JestTestBuilder implements TestBuilderInterface {
    
    public static function makeTest(string $testName, InputInterface $input): int { 
        return Command::FAILURE;
    }

    public static function testIfExists(string $name): bool { return false; }
}