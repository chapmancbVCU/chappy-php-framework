<?php
namespace Console\Commands;
 
use Core\Lib\Utilities\Str;
use Console\Helpers\{Tools, View};
use Core\Lib\Logging\Logger;
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
            ->addArgument('component-name', InputArgument::REQUIRED, 'Pass the name for the new component')

            // Configure form component
            ->addOption('form', null, InputOption::VALUE_NONE, 'Create a form component')
            ->addOption('form-method', null, InputOption::VALUE_OPTIONAL, 'Form method (default: POST)', 'post')
            ->addOption('enctype', null, InputOption::VALUE_OPTIONAL, 'Form enctype', '')

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

        if($input->getOption('card')) {
            return View::makeCardComponent($componentName);
        } else if($input->getOption('form')) {
            return View::makeFormComponent(
                $componentName,
                Str::lower($input->getOption('form-method') ?? 'post'),
                $input->getOption('enctype') ??  ''
            );
        } else if($input->getOption('table')) {
            return View::makeTableComponent($componentName);
        }

        console_warning('No component type selected');
        return Command::FAILURE;
    }
}
