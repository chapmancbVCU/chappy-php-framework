<?php
namespace Console\Commands;

use Console\Helpers\Testing\ThirdPartyTests;
use Core\Lib\Utilities\Str;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command for generating new unit test runner.
 */
class MakeTestRunnerCommand extends Command {
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
     * @param InputInterface $input The input.
     * @param OutputInterface $output The output.
     * @return int A value that indicates success, invalid, or failure.
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $className = $input->getArgument('runner-name');
        $message = "Enter name for new test runner.";
        if($className) {
            ThirdPartyTests::argOptionValidate($className, $message, $input, $output, ['max:50']);
        } else {
            $className = ThirdPartyTests::prompt($message, $input, $output, ['max:50']);
        }
        return ThirdPartyTests::makeRunner(Str::ucfirst($className)."Runner");
    }
}
