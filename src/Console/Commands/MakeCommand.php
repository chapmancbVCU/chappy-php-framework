<?php
namespace Console\Commands;

use Console\Helpers\CommandHelper;
use Core\Lib\Utilities\Str;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Supports ability to create new console command by running make:command.
 * More information can be found <a href="https://chapmancbvcu.github.io/chappy-php-starter/console#build-command">here</a>.
 */
class MakeCommand extends Command
{
    /**
     * Configures the command.
     *
     * @return void
     */
    protected function configure(): void
    {
        $this->setName('make:command')
            ->setDescription('Generates a new command class')
            ->setHelp('php console make:command <command_name>')
            ->addArgument('command-name', InputArgument::OPTIONAL, 'Pass the command\'s name.');
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
        $commandName = $input->getArgument('command-name');
        $message = "Enter name for new Command class.";
        if($commandName) {
            CommandHelper::argOptionValidate($commandName, $message, $input, $output);
        } else {
            $commandName = CommandHelper::prompt($message, $input, $output);
        }
        return CommandHelper::makeCommand(Str::ucfirst($commandName));
    }
}