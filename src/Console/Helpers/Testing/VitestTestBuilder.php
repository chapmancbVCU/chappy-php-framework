<?php
declare(strict_types=1);
namespace Console\Helpers\Testing;

use Core\Lib\Logging\Logger;
use Console\Helpers\Tools;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;

class VitestTestBuilder implements TestBuilderInterface {
    
    public static function makeTest(string $testName, InputInterface $input): int { 
        if(self::testIfExists($testName)) {
            return Command::FAILURE;
        }

        return Tools::writeFile(
                ROOT.DS.VitestTestRunner::UNIT_PATH.$testName.'.test.js',
                VitestStubs::unitTestStub($testName),
                'Test'
            );
        return Command::SUCCESS;
    }

    public static function testIfExists(string $name): bool { return false; }
}