<?php
namespace Console\Commands;

use Console\Helpers\Testing\PHPTestBuilder;
use Core\Lib\Utilities\Str;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Supports operations related to running Jest unit tests.
 */
class RunJestCommand extends Command
{
    /**
     * Configures the command.
     *
     * @return void
     */
    protected function configure(): void
    {
        $this->setName('react:jest')
            ->setDescription('Runs Jest unit tests')
            ->setHelp('php console react:jest');
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
        $output->writeln(shell_exec("npm test"));
        return Command::SUCCESS;
    }
}
