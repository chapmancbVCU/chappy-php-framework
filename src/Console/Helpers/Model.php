<?php
declare(strict_types=1);
namespace Console\Helpers;

use Console\FrameworkQuestion;
use Core\Lib\Utilities\Str;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Helper class for model related console commands.
 */
class Model {
    /**
     * Path to model classes.
     */
    public const MODEL_PATH = ROOT.DS.'app'.DS.'Models'.DS;

    /**
     * Generates a new model class.
     *
     * @param InputInterface $input The Symfony InputInterface object.
     * @param string $modelName The name for the new model class.
     * @return string A value that indicates success, invalid, or failure.
     */
    public static function contents(string $modelName, $uploadOption): string {
        if($uploadOption) {
            return ModelStubs::uploadModelTemplate($modelName);
        }
            
        return ModelStubs::modelTemplate($modelName);
    }

    /**
     * Handles question for model name if it is not provided as an 
     * argument.
     *
     * @param InputInterface $input The Symfony InputInterface object.
     * @param OutputInterface $output The Symfony OutputInterface object.
     * @return string The name of the model class.
     */
    public static function modelNamePrompt(InputInterface $input, OutputInterface $output): string {
        $question = new FrameworkQuestion($input, $output);
        $message = "Enter name for model.";
        $response = $question->required()->ask($message);
        return Str::ucfirst($response);
    }

    /**
     * Prompts user if they want an upload model if model name 
     * argument is not provided and upload flag is not set.  Once the input 
     * has been processed the contents for the model class is returned.
     *
     * @param string $modelName The name of the new model class.
     * @param InputInterface $input he Symfony InputInterface object.
     * @param OutputInterface $output The Symfony OutputInterface object.
     * @param mixed $uploadOption Value/state of upload flag.
     * @return string The contents of the model class.
     */
    public static function uploadPrompt(
        string $modelName, 
        InputInterface $input, 
        OutputInterface $output,
        mixed $uploadOption
    ): string {
        if($uploadOption) return self::contents($modelName, $output);

        $question = new FrameworkQuestion($input, $output);
        $message = "Do you want to create a model that supports uploads? (y/n)";
        if($question->confirm($message)) {
            return ModelStubs::uploadModelTemplate($modelName);
        }

        return ModelStubs::modelTemplate($modelName);
    }
}