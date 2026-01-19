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
     * @param string $className Name for the new builder class.
     * @return string The contents of the new test builder class.
     */
    public static function builderStub(string $className): string {
        return <<<PHP
<?php
namespace App\Testing;

use Console\Helpers\Testing\TestBuilderInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;

class {$className} implements TestBuilderInterface {

    public static function makeTest(string \$testName, InputInterface \$input): int {

        return Command::SUCCESS;
    }
}
PHP;
    }

    /**
     * Stub for custom test runner class.
     *
     * @param string $className Name for the new runner class.
     * @return string The contents of the new runner class.
     */
    public static function runnerStub(string $className): string {
        return <<<PHP
<?php
declare(strict_types=1);
namespace App\Testing;

use Console\Helpers\Tools;
use Console\Helpers\Testing\TestRunner;
use Core\Lib\Logging\Logger;
use Core\Lib\Utilities\Arr;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class {$className} extends TestRunner {
    /**
     * The array of options allowed as input for the test command.
     */
    public const ALLOWED_OPTIONS = [];

    /**
     * The command for Unit Testing Framework.
     */
    public const TEST_COMMAND = '';

    /**
     * Constructor
     *
     * @param InputInterface \$input The Symfony InputInterface object.
     * @param OutputInterface \$output The Symfony OutputInterface object.
     */
    public function __construct(InputInterface \$input, OutputInterface \$output) {
        \$this->inputOptions = self::parseOptions(\$input);
        parent::__construct(\$output);
    }

    /**
     * Parses unit test related arguments and ignore Symfony arguments.
     *
     * @param InputInterface \$input Instance of InputInterface from command.
     * @return string A string containing the arguments to be provided to 
     * to your testing framework.
     */
    public static function parseOptions(InputInterface \$input): string { 
        \$args = [];

        foreach(self::ALLOWED_OPTIONS as \$allowed) {
            if(\$input->hasOption(\$allowed) && \$input->getOption(\$allowed)) {
                switch(\$allowed) {
                    default;
                        \$args[] = '--' . \$allowed;
                        break;
                }
            }
        }
        return (Arr::isEmpty(\$args)) ? '' : ' ' . implode(' ', \$args);
    }
}
PHP;
    }
}