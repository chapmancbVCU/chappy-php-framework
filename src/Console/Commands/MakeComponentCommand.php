<?php
namespace Console\Commands;

use Console\ConsoleCommand;
use Console\Helpers\Component;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Supports ability to create components by running make:component.
 * More information can be found <a href="https://chapmancbvcu.github.io/chappy-php-starter/components">here</a>.
 */
class MakeComponentCommand extends ConsoleCommand {
    /**
     * Configures the command.
     *
     * @return void
     */
    protected function configure(): void
    {
        $this->setName('make:component')
            ->setDescription('Generates a new component')
            ->setHelp('php console make:component <component_name>')
            ->addArgument('component-name', InputArgument::OPTIONAL, 'Pass the name for the new component')

            // Configure form component
            ->addOption('form', null, InputOption::VALUE_NONE, 'Create a form component')

            // Configure card component
            ->addOption('card', null, InputOption::VALUE_NONE, 'Create a card component')

            // Configure table component
            ->addOption('table', null, InputOption::VALUE_NONE, 'Create a table component');
    }

    /**
     * Executes the command
     *
     * @return int A value that indicates success, invalid, or failure.
     */
    protected function handle(): int
    {
        $componentName = $this->getArgument('component-name');
        $flag = Component::componentType($this->input, $this->question());
  
        if($componentName) {
            Component::argOptionValidate(
                $componentName,
                Component::PROMPT_MESSAGE,
                $this->question(),
                ['max:50', 'fieldName:component-name']
            );
            return Component::makeComponent($componentName, $flag, $this->question());
        } 

        $componentName = Component::componentNamePrompt($this->question());
        return Component::makeComponent($componentName, $flag, $this->question());
    }
}
