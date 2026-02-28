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

    public static function modelNamePrompt(InputInterface $input, OutputInterface $output): string {
        $question = new FrameworkQuestion($input, $output);
        $message = "Enter name for model";
        $response = $question->ask($message);

        while($response == '') {
            $message = "This field is required.  Please enter name for a model";
            $response = $question->ask($message);
        }

        return Str::ucfirst($response);
    }

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