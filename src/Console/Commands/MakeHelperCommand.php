<?php
namespace Console\Commands;

use Console\ConsoleCommand;
use Console\Helpers\CommandHelper;
use Core\Lib\Utilities\Str;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Class for the make:command-helper command.  This class generates helpers 
 * for your custom commands.
 * More information can be found <a href="https://chapmancbvcu.github.io/chappy-php-starter/console#command-helpers">here</a>.
 */
class MakeHelperCommand extends ConsoleCommand {
    /**
     * Configures the command.
     *
     * @return void
     */
    protected function configure(): void
    {
        $this->setName('make:command:helper')
            ->setDescription('Generates a class that supports multiple commands')
            ->setHelp('php console make:command-helper <helper_name>')
            ->addArgument('helper-name', InputArgument::OPTIONAL, 'Pass the command helper\'s name');
    }

    /**
     * Executes the command
     *
     * @return int A value that indicates success, invalid, or failure.
     */
    protected function handle(): int
    {
        $helperName = $this->getArgument('helper-name');
        $message = "Enter name for new command helper class";
        if($helperName) {
            CommandHelper::argOptionValidate($helperName, $message, $this->question(), ['max:50']);
        } else {
            $helperName = CommandHelper::prompt($message, $this->question(), ['max:50']);
        }
        return CommandHelper::makeHelper(Str::ucfirst($helperName));
    }
}
