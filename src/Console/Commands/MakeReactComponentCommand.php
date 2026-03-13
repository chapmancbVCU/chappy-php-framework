<?php
namespace Console\Commands;

use Console\Helpers\React;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Implements command for making a new react component by running react:component.
 * More information can be found <a href="https://chapmancbvcu.github.io/chappy-php-starter/controllers_and_views#view-commands">here</a>.
 */
class MakeReactComponentCommand extends Command {
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
     * @param InputInterface $input The input.
     * @param OutputInterface $output The output.
     * @return int A value that indicates success, invalid, or failure.
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $componentName = $input->getArgument('component-name');
        $message = "Enter name for new component.";
        
        $named = $input->getOption('named');
        if($componentName) {
            React::argOptionValidate($componentName, $message, $input, $output, ['max:50']);
        } else {
            $componentName = React::prompt($message, $input, $output, ['max:50']);
            $named = React::namedComponentPrompt($named, $input, $output);
        }
        return React::makeComponent($componentName, $named);
    }
}
