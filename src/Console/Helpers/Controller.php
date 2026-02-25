<?php
declare(strict_types=1);
namespace Console\Helpers;

use Core\Lib\Utilities\Str;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;

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
    public static function contents(string $className, InputInterface $input, string $layout): string {
        if($input->getOption('resource')) {
            return ControllerStubs::resourceTemplate($className, $layout);
        } 

        return ControllerStubs::defaultTemplate($className, $layout);
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
}