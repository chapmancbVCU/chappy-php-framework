<?php
namespace Console\Commands;

use Console\ConsoleCommand;
use Console\Helpers\React;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * Implements command for making a new react component by running react:component.
 * More information can be found <a href="https://chapmancbvcu.github.io/chappy-php-starter/controllers_and_views#view-commands">here</a>.
 */
class MakeReactComponentCommand extends ConsoleCommand {
    /**
     * Configures the command.
     *
     * @return void
     */
    protected function configure(): void
    {
        $this->setName('react:component')
            ->setDescription('Generates a new React.js component')
            ->setHelp('php console react:component <component_name>')
            ->addArgument('component-name', InputArgument::OPTIONAL, 'Pass the name for the new React.js component')
            ->addOption('named', null, InputOption::VALUE_NONE, 'Creates as a named export');
    }

    /**
     * Executes the command
     *
     * @return int A value that indicates success, invalid, or failure.
     */
    protected function handle(): int
    {
        $componentName = $this->getArgument('component-name');
        $message = "Enter name for new component.";
        
        $named = $this->getOption('named');
        if($componentName) {
            React::argOptionValidate($componentName, $message, $this->question(), ['max:50']);
        } else {
            $componentName = React::prompt($message, $this->question(), ['max:50']);
            $named = React::namedComponentPrompt($named, $this->question());
        }
        return React::makeComponent($componentName, $named);
    }
}
