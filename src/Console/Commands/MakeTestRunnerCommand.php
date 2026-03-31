<?php
namespace Console\Commands;

use Console\ConsoleCommand;
use Console\Helpers\Testing\ThirdPartyTests;
use Core\Lib\Utilities\Str;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Command for generating new unit test runner.
 */
class MakeTestRunnerCommand extends ConsoleCommand {
    /**
     * Configures the command.
     *
     * @return void
     */
    protected function configure(): void
    {
        $this->setName('make:test:runner')
            ->setDescription('Generates a test runner for a 3rd party suite')
            ->setHelp('php console make:test:runner <runner-name>')
            ->addArgument('runner-name', InputArgument::OPTIONAL, 'Pass name of runner');
    }

    /**
     * Executes the command
     *
     * @return int A value that indicates success, invalid, or failure.
     */
    protected function handle(): int
    {
        $className = $this->getArgument('runner-name');
        $message = "Enter name for new test runner.";
        if($className) {
            ThirdPartyTests::argOptionValidate($className, $message, $this->question(), ['max:50']);
        } else {
            $className = ThirdPartyTests::prompt($message, $this->question(), ['max:50']);
        }
        return ThirdPartyTests::makeRunner(Str::ucfirst($className)."Runner");
    }
}
