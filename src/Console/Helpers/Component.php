<?php
declare(strict_types=1);
namespace Console\Helpers;

use Console\Console;
use Console\FrameworkQuestion;
use Core\Lib\Utilities\Str;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;

/**
 * Supports operations related to the creation of components.
 */
class Component extends Console {
    /** Path to components. */
    public const COMPONENTS_PATH = ROOT.DS.'resources'.DS.'views'.DS.'components'.DS;

    /**
     * The message to present to user when name of component is being asked.
     */
    public const PROMPT_MESSAGE = "Enter name for your component";

    /**
     * Prompts uses for information if argument is not provided.
     *
     * @param FrameworkQuestion $question Instance of FrameworkQuestion class.
     * @return mixed The user response.
     */
    public static function componentNamePrompt(FrameworkQuestion $question,): mixed {
        return self::prompt(self::PROMPT_MESSAGE, $question, ['max:5', 'fieldName:component-name']);
    }

    /**
     * Determines the type of component to be created based on flag 
     * provided.  If no flag is provided or multiple flags are set then user 
     * is presented with relevant message and asked which type of component 
     * that they want to create.
     *
     * @param InputInterface $input The Symfony InputInterface object.
     * @param FrameworkQuestion $question Instance of FrameworkQuestion class.
     * @return string A string with the name of the component to be created.
     */
    public static function componentType(InputInterface $input, FrameworkQuestion $question): string {
        $types = [
            'card' => $input->getOption('card'),
            'form' => $input->getOption('form'),
            'table' => $input->getOption('table')
        ];

        $flag = null;
        $count = 0;
        $multipleFlags = false;

        foreach($types as $k => $v) {
            if($v) {
                $flag = $k;
                $count++;
            }
            if($count > 1) {
                $multipleFlags = true;
                console_warning("You can only choose one component type at a time.");
            }
        }

        if(!$flag) console_warning("Component flag not set.");
        if($multipleFlags || $flag == null) {
            $message = "Choose a component type";
            $choices = ['card', 'form', 'table'];
            $flag = self::choice($message, $choices, $question);
        }
        return $flag;
    }

    /**
     * Prompts user for information about enctype to be used in form component.
     *
     * @param FrameworkQuestion $question Instance of FrameworkQuestion class.
     * @return string The user's response.
     */
    public static function enctype(FrameworkQuestion $question): string {
        $message = "Choose a the enctype (default: none)";
        $choices = ['default: none', 'multipart/form-data', 'text/plain'];
        $response = self::choice($message, $choices, $question, $choices[0]);
        if($response == 'default: none') $response = '';
        return $response;
    }

    /**
     * Prompts user for information about which form method to be used in form component.
     *
     * @param FrameworkQuestion $question Instance of FrameworkQuestion class.
     * @return string The user's response.
     */
    public static function formMethod(FrameworkQuestion $question): string {
        $message = "Choose a form method (default: post).";
        $choices = ['get', 'post', 'put'];
        return self::choice($message, $choices, $question, $choices[0]);
    }

    /**
     * Writes card component to a file.
     *
     * @param string $componentName The name of the card component.
     * @return int A value that indicates success, invalid, or failure.
     */
    public static function makeCardComponent(string $componentName): int {
        return Tools::writeFile(
            self::COMPONENTS_PATH.Str::lower($componentName).".php",
            ComponentStubs::cardComponent(),
            "Form component"
        );
    }

    /**
     * Generate a component when argument is provided.
     *
     * @param string $componentName The name for the new component.
     * @param string $componentFlag The type of component to be created.
     * @param FrameworkQuestion $question Instance of FrameworkQuestion class.
     * @return int A value that indicates success, invalid, or failure.
     */
    public static function makeComponent(string $componentName, string $componentFlag, FrameworkQuestion $question): int {
        if($componentFlag === 'card') {
            return self::makeCardComponent($componentName);
        } else if($componentFlag === 'form') {
            return self::makeFormComponent(
                $componentName,
                self::formMethod($question),
                self::enctype($question)
            );
        } else if($componentFlag === 'table') {
            return self::makeTableComponent($componentName);
        }
        return Command::FAILURE;
    }
    
    /**
     * Writes form component to file.
     *
     * @param string $componentName The name of the form component.
     * @param string $method The method to be used.
     * @param string $encType The enctype to be used.
     * @return int A value that indicates success, invalid, or failure.
     */
    public static function makeFormComponent(string $componentName, string $method, string $encType): int {
        return Tools::writeFile(
            self::COMPONENTS_PATH.Str::lower($componentName).".php",
            ComponentStubs::formComponent($method, $encType),
            "Form component"
        );
    }

    /**
     * Writes table component to a file.
     *
     * @param string $componentName The name of the table component.
     * @return int A value that indicates success, invalid, or failure.
     */
    public static function makeTableComponent(string $componentName): int {
        return Tools::writeFile(
            self::COMPONENTS_PATH.Str::lower($componentName).".php",
            ComponentStubs::tableComponent(),
            "Table component"
        );
    }
}