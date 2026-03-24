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

    /**
     * Creates a new file
     *
     * @param string \$testName The name for the test.
     * @param mixed \$suite The flag for a particular suite.
     * @return int A value that indicates success, invalid, or failure.
     */
    public static function makeTest(string \$testName, mixed \$suite): int {

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

use Console\Helpers\Testing\TestRunner;
use Core\Lib\Utilities\Arr;
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
     * Array of supported test file extensions.
     */
    public const TEST_FILE_EXTENSIONS = [];

    /**
     * Array of available test suites.
     */
    public const TEST_SUITES = [];
    
    /**
     * Constructor
     *
     * @param InputInterface \$input The Symfony InputInterface object.
     * @param OutputInterface \$output The Symfony OutputInterface object.
     */
    public function __construct(InputInterface \$input, OutputInterface \$output) {
        parent::__construct(\$input, \$output);
    }

    /**
     * Parses unit test related arguments and ignore Symfony arguments.
     *
     * @return string A string containing the arguments to be provided to 
     * to your testing framework.
     */
    public function parseOptions(): string { 
        \$args = [];

        foreach(self::ALLOWED_OPTIONS as \$allowed) {
            if(\$this->input->hasOption(\$allowed) && \$this->input->getOption(\$allowed)) {
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