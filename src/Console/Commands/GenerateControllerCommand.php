<?php
namespace Console\Commands;

use Console\Console;
use Console\Helpers\Controller;
use Console\Helpers\Tools;
use Core\Lib\Utilities\Str;
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
            ->addArgument('controller-name', InputArgument::OPTIONAL, 'Pass the controller\'s name.')
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
        $controllerName = $input->getArgument('controller-name');
        if($controllerName) {
            Console::argOptionValidate(
                $controllerName, 
                Controller::PROMPT_MESSAGE, 
                $input, 
                $output, 
                'controller-name'
            );
        }

        $layout = Controller::layout($input, $output);
        $resourceOption = $input->getOption('resource');
        if(Tools::isFailure($layout)) return Command::FAILURE;
        
        if($controllerName) {
            $controllerName = Str::ucfirst($controllerName);
            $contents = Controller::contents($controllerName, $resourceOption, $layout);
        } else {
            $controllerName = Controller::controllerNamePrompt($input, $output);
            $layout = Controller::layoutPrompt($input, $output, $layout);
            $contents = Controller::resourcePrompt($controllerName, $input, $layout, $output, $resourceOption);
        }
        
        return Tools::writeFile(
            Controller::CONTROLLER_PATH.$controllerName.'Controller.php',
            $contents,
            "Controller"
        );
    }  
}
