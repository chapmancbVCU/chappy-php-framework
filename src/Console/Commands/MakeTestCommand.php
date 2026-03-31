<?php
namespace Console\Commands;

use Console\ConsoleCommand;
use Console\Helpers\Testing\PHPUnitTestBuilder;
use Core\Lib\Utilities\Str;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * Supports ability to generate new PHPUnit test file by running make:test.
 * More information can be found <a href="https://chapmancbvcu.github.io/chappy-php-starter/php_unit#creating-tests">here</a>.
 */
class MakeTestCommand extends ConsoleCommand
{
    /**
     * Configures the command.
     *
     * @return void
     */
    protected function configure(): void
    {
        $this->setName('make:test')
            ->setDescription('Generates a new test file!')
            ->setHelp('php console make:test <test_name>')
            ->addArgument('test-name', InputArgument::OPTIONAL, 'Pass the test\'s name.')
            ->addOption('feature', null, InputOption::VALUE_NONE, 'Create feature test');
    }
 
    /**
     * Executes the command
     *
     * @return int A value that indicates success, invalid, or failure.
     */
    protected function handle(): int
    {
        $testName = $this->getArgument('test-name');
        $feature = $this->getOption('feature');
        $message = "Enter name for new test case class.";

        if($testName) {
            PHPUnitTestBuilder::argOptionValidate($testName, $message, $this->question(), ['max:150']);
        } else {
            $testName = PHPUnitTestBuilder::prompt($message, $this->question(), ['max:150']);
            $feature = PHPUnitTestBuilder::featurePrompt($feature, $this->question());
        }

        return PHPUnitTestBuilder::makeTest(Str::ucfirst($testName), $feature);
    }
}
