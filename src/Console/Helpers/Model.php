<?php
declare(strict_types=1);
namespace Console\Helpers;

use Core\Lib\Utilities\Str;
use Symfony\Component\Console\Input\InputInterface;

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
     * @return int A value that indicates success, invalid, or failure.
     */
    public static function makeModel(InputInterface $input): int {
        $modelName = Str::ucfirst($input->getArgument('modelname'));
        $path = self::MODEL_PATH.$modelName.'.php';

        if($input->getOption('upload')) {
            return Tools::writeFile($path, ModelStubs::uploadModelTemplate($modelName), 'Model');
        } else {
            return Tools::writeFile($path, ModelStubs::modelTemplate($modelName), 'Model');
        }
    }
}