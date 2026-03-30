<?php
declare(strict_types=1);
namespace Console\Helpers;

use Console\Console;
use Console\FrameworkQuestion;
use Core\Lib\Utilities\Str;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Helper class for model related console commands.
 */
class Model extends Console {
    /**
     * Path to model classes.
     */
    public const MODEL_PATH = ROOT.DS.'app'.DS.'Models'.DS;
    
    /**
     * The message to present to user when name of model is being asked.
     */
    public const PROMPT_MESSAGE = "Enter name for the model.";

    /**
     * Generates a new model class.
     *
     * @param string $modelName The name for the new model class.
     * @param string $uploadOption Value of --upload flag.
     * @return string A value that indicates success, invalid, or failure.
     */
    public static function contents(string $modelName, mixed $uploadOption): string {
        if($uploadOption) {
            return ModelStubs::uploadModelTemplate($modelName);
        }
            
        return ModelStubs::modelTemplate($modelName);
    }

    /**
     * Handles question for model name if it is not provided as an 
     * argument.
     *
     * @param FrameworkQuestion $question Instance of FrameworkQuestion class.
     * @return string The name of the model class.
     */
    public static function modelNamePrompt(FrameworkQuestion $question): string {
        $response = self::prompt(self::PROMPT_MESSAGE, $question, ['max:50', 'fieldName:model-name']);
        return Str::ucfirst($response);
    }

    /**
     * Prompts user if they want an upload model if model name 
     * argument is not provided and upload flag is not set.  Once the input 
     * has been processed the contents for the model class is returned.
     *
     * @param string $modelName The name of the new model class.
     * @param FrameworkQuestion $question Instance of FrameworkQuestion class.
     * @param OutputInterface $output The Symfony OutputInterface object.
     * @param mixed $uploadOption Value/state of upload flag.
     * @return string The contents of the model class.
     */
    public static function uploadPrompt(
        string $modelName, 
        FrameworkQuestion $question, 
        OutputInterface $output,
        mixed $uploadOption
    ): string {
        if($uploadOption) return self::contents($modelName, $output);
        $message = "Do you want to create a model that supports uploads? (y/n)";
        if(self::confirm($message, $question)) {
            return ModelStubs::uploadModelTemplate($modelName);
        }

        return ModelStubs::modelTemplate($modelName);
    }
}