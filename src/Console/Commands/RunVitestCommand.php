<?php
namespace Console\Commands;

use Console\Helpers\Testing\PHPTestBuilder;
use Console\Helpers\Testing\VitestTestRunner;
use Core\Lib\Utilities\Str;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Supports operations related to running Jest unit tests.
 */
class RunVitestCommand extends Command
{
    /**
     * Configures the command.
     *
     * @return void
     */
    protected function configure(): void
    {
        $this->setName('react:vitest')
            ->setDescription('Runs Vitest unit tests')
            ->setHelp('php console react:vitest');
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
        // $output->writeln(shell_exec("npm test"));
        $test = new VitestTestRunner($input, $output);

        $suite = VitestTestRunner::getAllTestsInSuite(VitestTestRunner::UNIT_PATH, "test.js");
        // $test->runTest($suite[0], VitestTestRunner::TEST_COMMAND);
        $test->testSuite($suite, VitestTestRunner::TEST_COMMAND);
        return Command::SUCCESS;
    }
}
