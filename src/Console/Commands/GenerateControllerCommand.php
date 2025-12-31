<?php
namespace Console\Commands;
 
use Console\Helpers\Tools;
use Core\Lib\Utilities\Str;
use Console\Helpers\Controller;
use Core\Lib\Logging\Logger;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
/**
 * Supports ability to generate new controller class by typing make:controller.
 * More information can be found <a href="https://chapmancbvcu.github.io/chappy-php-starter/controllers#creating-a-controller">here</a>.
 */
class GenerateControllerCommand extends Command
{
    /**
     * Configures the command.
     *
     * @return void
     */
    protected function configure(): void
    {
        $this->setName('make:controller')
            ->setDescription('Generates a new controller file!')
            ->setHelp('php console make:controller MyController, add --layout=<optional_layout_name> to set layout, and --resource to generate CRUD functions')
            ->addArgument('controllername', InputArgument::REQUIRED, 'Pass the controller\'s name.')
            ->addOption(
                'layout',
                null,
                InputOption::VALUE_REQUIRED,
                'Layout for views associated with controller.',
                false)
            ->addOption(
                'resource',
                null,
                InputOption::VALUE_NONE,
                'Add CRUD functions'
            );
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
        $controllerName = Str::ucfirst($input->getArgument('controllername'));
        
        // Test if --layout is properly set
        $layoutInput = $input->getOption('layout');
        if($layoutInput === false) {
            $layout = 'default';
        } else if ($layoutInput === null) {
            Tools::info('Please supply name of layout.', Logger::DEBUG, Tools::BG_RED);
            return Command::FAILURE;
        } else {
            if($layoutInput === '') {
                Tools::info('Please supply name of layout.', Logger::DEBUG, Tools::BG_RED);
                return Command::FAILURE;
            }
            $layout = Str::lower($layoutInput);
        }
        
        // Test if --resource flag is set and generate appropriate version of file
        if($input->getOption('resource')) {
            $content = Controller::resourceTemplate($controllerName, $layout);
        } else {
            $content = Controller::defaultTemplate($controllerName, $layout);
        }

        // Generate Controller class
        return Tools::writeFile(
            Controller::CONTROLLER_PATH.$controllerName.'Controller.php',
            $content,
            "Controller"
        );
    }  
}
