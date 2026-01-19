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
            ->setHelp('php console make:view <directory_name>.<view_name>')
            ->addArgument('runner-name', InputArgument::REQUIRED, 'Pass name of directory and runner');
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
        $className = Str::ucfirst($input->getArgument('runner-name'));
        return ThirdPartyTests::makeRunner($className."Runner");
    }
}
