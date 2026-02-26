<?php
declare(strict_types=1);
namespace Console\Helpers;

use Console\FrameworkQuestion;
use Core\Lib\Utilities\Str;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Supports operations related to generating controllers.
 */
final class Controller {
    /**
     * Path for controller classes.
     */
    public const CONTROLLER_PATH = ROOT.DS.'app'.DS.'Controllers'.DS;

    /**
     * Returns contents for the controller class.  If the resource flag is 
     * set then a resource controller is generated.
     *
     * @param string $className The name for the new controller class.
     * @param InputInterface $input The Symfony InputInterface object.
     * @param string $layout The name of the layout to be used.
     * @return string The contents for the controller class.
     */
    public static function contents(
        string $className, 
        InputInterface $input, 
        string $layout
    ): string {

        if($input->getOption('resource')) {
            return ControllerStubs::resourceTemplate($className, $layout);
        } 

        return ControllerStubs::defaultTemplate($className, $layout);
    }

    /**
     * Handles question for controller name if it is not provided as an 
     * argument.
     *
     * @param InputInterface $input The Symfony InputInterface object.
     * @param OutputInterface $output The Symfony OutputInterface object.
     * @return string The name of the controller class.
     */
    public static function controllerNamePrompt(
        InputInterface $input, 
        OutputInterface $output
    ): string {

        $question = new FrameworkQuestion($input, $output);
        $message = "Enter name for controller";
        return Str::ucfirst($question->ask($message));
    }

    /**
     * Sets layout for controller
     *
     * @param InputInterface $input The Symfony InputInterface object.
     * @return string|int The layout or Command::FAILURE if there is an issue.
     */
    public static function layout(InputInterface $input): string|int {
        $layoutInput = $input->getOption('layout');
        if($layoutInput === false) {
            $layout = 'default';
        } else if ($layoutInput === null) {
            console_warning('Please supply name of layout.');
            return Command::FAILURE;
        } else {
            if($layoutInput === '') {
                console_warning('Please supply name of layout.');
                return Command::FAILURE;
            }
            $layout = Str::lower($layoutInput);
        }
        return $layout;
    }

    /**
     * Sets layout when user does not enter name of controller as argument.  
     * If layout flag is not set then user is asked questions about desired 
     * layout name.
     *
     * @param FrameworkQuestion $question Instance of Framework Question 
     * object.
     * @param InputInterface $input The Symfony InputInterface object.
     * @param string $layout Current value set for layout to be used.
     * @return string The name of the layout to be used.
     */
    public static function layoutPrompt(
        InputInterface $input, 
        OutputInterface $output, 
        string $layout
    ): string {
    
        $question = new FrameworkQuestion($input, $output);
        $layoutInput = $input->getOption('layout');
        if($layoutInput === true) return $layout;

        $message = "Do you want to set a name for your layout? (y/n)";
        if($question->confirm($message)) {
            $message = "Enter name for your layout";
            return Str::lower($question->ask($message));
        } else {
            return 'default';
        }
    }
}