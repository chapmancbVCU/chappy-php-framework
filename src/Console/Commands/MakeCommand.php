<?php
namespace Console\Commands;

use Console\ConsoleCommand;
use Console\Helpers\CommandHelper;
use Core\Lib\Utilities\Str;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Supports ability to create new console command by running make:command.
 * More information can be found <a href="https://chapmancbvcu.github.io/chappy-php-starter/console#build-command">here</a>.
 */
class MakeCommand extends ConsoleCommand
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
     * @param FrameworkQuestion $question Instance of FrameworkQuestion class.
     * @return int A value that indicates success, invalid, or failure.
     */
    protected function handle(): int
    {
        $commandName = $this->input->getArgument('command-name');
        $message = "Enter name for new Command class.";
        if($commandName) {
            CommandHelper::argOptionValidate($commandName, $message, $this->question(), ['max:50']);
        } else {
            $commandName = CommandHelper::prompt($message, $this->question(), ['max:50']);
        }
        return CommandHelper::makeCommand(Str::ucfirst($commandName));
    }
}