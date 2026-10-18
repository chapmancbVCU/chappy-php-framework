<?php
declare(strict_types=1);
namespace Console\Helpers;

use Console\Console;
use Console\FrameworkQuestion;
use Core\Lib\Utilities\Str;
use Symfony\Component\Console\Input\InputInterface;

/**
 * Supports operations related to generating controllers.
 */
final class Controller extends Console {
    /**
     * Path for controller classes.
     */
    public const CONTROLLER_PATH = ROOT.DS.'app'.DS.'Controllers'.DS;

    /**
     * The message to present to user when name of controller is being asked.
     */
    public const LAYOUT_PROMPT = "Enter name for the layout.";

    /**
     * The message to present to user when name of controller is being asked.
     */
    public const PROMPT_MESSAGE = "Enter name for the controller";

    /**
     * Returns contents for the controller class.  If the resource flag is 
     * set then a resource controller is generated.
     *
     * @param string $className The name for the new controller class.
     * @param string $layout The name of the layout to be used.
     * @param mixed $resourceOption Value/state of resource flag.
     * @return string The contents for the controller class.
     */
    public static function contents(
        string $className, 
        string $layout,
        mixed $reactOption,
        mixed $resourceOption
    ): string {

        if($resourceOption) {
            return ControllerStubs::resourceTemplate($className, $layout);
        } else if($reactOption) {
            return ControllerStubs::reactTemplate($className, $layout);
        } else {
            return ControllerStubs::defaultTemplate($className, $layout);
        }
    }

    /**
     * Handles question for controller name if it is not provided as an 
     * argument.
     *
     * @param FrameworkQuestion $question Instance of FrameworkQuestion class.
     * @return string The name of the controller class.
     */
    public static function controllerNamePrompt(FrameworkQuestion $question): string {

        $response = self::prompt(self::PROMPT_MESSAGE, $question, ['max:50', 'fieldName:controller-name']);
        return Str::ucfirst($response);
    }

    /**
     * Sets layout for controller when provided as an option.
     *
     * @param InputInterface $input The Symfony InputInterface object.
     * @param FrameworkQuestion $question Instance of FrameworkQuestion class.
     * @return string The layout to be used with the controller.
     */
    public static function layout(InputInterface $input, FrameworkQuestion $question): string {
        $layoutInput = $input->getOption('layout');

        if($layoutInput === false) return 'default';

        self::argOptionValidate(
            $layoutInput,
            self::LAYOUT_PROMPT,
            $question,
            ['max:50', 'fieldName:layout']
        );
        
        return Str::lower($layoutInput);
    }

    /**
     * Sets layout when user does not enter name of controller as argument.  
     * If layout flag is not set then user is asked questions about desired 
     * layout name.
     *
     * @param InputInterface $input The Symfony InputInterface object.
     * @param FrameworkQuestion $question Instance of Framework Question 
     * object.
     * @param string $layout Current value set for layout to be used.
     * @return string The name of the layout to be used.
     */
    public static function layoutPrompt(
        InputInterface $input, 
        FrameworkQuestion $question,
        string $layout
    ): string {
        $layoutInput = $input->getOption('layout');
        if($layoutInput) return $layout;

        $message = "Do you want to set a name for your layout? (y/n)";
        if(self::confirm($message, $question)) {
            $response = self::prompt(self::PROMPT_MESSAGE, $question, ['max:50', 'fieldName:layout']);
            return Str::lower($response);
        } else {
            return 'default';
        }
    }

    /**
     * Returns contents for resource or react version of the class.
     *
     * @param string $className The name of the new class.
     * @param string $layout The layout of the class.
     * @param mixed $reactOption The value of the reactOption flag.
     * @param mixed $resourceOption The value of the resourceOption flag.
     * @return string|false The contents or false if neither flag is set.
     */
    private static function resourceContents(
        string $className,
        string $layout,
        mixed $reactOption,
        mixed $resourceOption
    ): string|false {
        if($reactOption) {
            return ControllerStubs::reactTemplate($className, $layout);
        }
        else if($resourceOption) {
            return ControllerStubs::resourceTemplate($className, $layout);
        } else {
            return false;
        } 
    }

    /**
     * Prompts user if they want a resource controller if controller name 
     * argument is not provided and resource flag is not set.  Once the input 
     * has been processed the contents for the controller class is returned.
     *
     * @param string $className The name for the new controller class.
     * @param FrameworkQuestion $question Instance of FrameworkQuestion class.
     * @param string $layout The name of the layout to be used.
     * @param mixed $resourceOption Value/state of resource flag.
     * @return string The contents for the controller class.
     */
    public static function resourcePrompt(
        string $className, 
        FrameworkQuestion $question,
        string $layout, 
        mixed $reactOption,
        mixed $resourceOption
    ): string {
        if($resourceOption) return self::contents($className, $layout, $reactOption, $resourceOption);
        $message = "Do you want to use a resource controller? (y/n)";
        if(self::confirm($message, $question)) {
            $contents = self::resourceContents($className, $layout, $reactOption, $resourceOption);
            return ($contents) ? 
                $contents : 
                self::resourceTypePrompt($className, $layout, $question);
        }

        return ControllerStubs::defaultTemplate($className, $layout);
    }

    /**
     * Asks user which type of controller they want generated and returns 
     * requested version's contents.
     *
     * @param string $className The name for the class.
     * @param string $layout The name for the layout.
     * @param FrameworkQuestion $question Instance of FrameworkQuestion class.
     * @return string The contents for the controller class.
     */
    private static function resourceTypePrompt(
        string $className,
        string $layout,
        FrameworkQuestion $question
    ): string {
        $message = "Do you want a PHP or React.js controller?";
        $choices = ['PHP', 'React.js'];
        
        $results = self::choice($message, $choices, $question, $choices[0]);
        return ($results === 'PHP') ?
            ControllerStubs::resourceTemplate($className, $layout) :
            ControllerStubs::reactTemplate($className, $layout);
    }
}