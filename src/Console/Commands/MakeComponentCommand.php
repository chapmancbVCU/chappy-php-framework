<?php
namespace Console\Commands;
 
use Core\Lib\Utilities\Str;
use Console\HasValidators;
use Console\Helpers\Component;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Supports ability to create components by running make:component.
 * More information can be found <a href="https://chapmancbvcu.github.io/chappy-php-starter/components">here</a>.
 */
class MakeComponentCommand extends Command {
    use HasValidators;

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
     * @param InputInterface $input The input.
     * @param OutputInterface $output The output.
     * @return int A value that indicates success, invalid, or failure.
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $componentName = $input->getArgument('component-name');

        if($componentName) {
            return $this->required()
                        ->noSpecialChars()
                        ->fieldName('component-name')
                        ->alpha()
                        ->notReservedKeyword()
                        ->max(100)
                        ->validate($componentName) ?
                Component::componentContents($componentName, $input, $output) :
                Command::FAILURE;
        } else return Component::componentPrompt($input, $output);
        return Command::FAILURE;
    }
}
