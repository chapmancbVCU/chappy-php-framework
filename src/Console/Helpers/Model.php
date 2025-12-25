<?php
declare(strict_types=1);
namespace Console\Helpers;
use Core\Lib\Utilities\Str;
use Symfony\Component\Console\Input\InputInterface;

/**
 * Helper class for model related console commands.
 */
class Model {
    public const MODEL_PATH = ROOT.DS.'app'.DS.'Models'.DS;

    public static function makeModel(InputInterface $input) {
        $modelName = Str::ucfirst($input->getArgument('modelname'));
        $path = self::MODEL_PATH.$modelName.'.php';

        if($input->getOption('upload')) {
            return Tools::writeFile($path, self::uploadModelTemplate($modelName), 'Model');
        } else {
            return Tools::writeFile($path, self::modelTemplate($modelName), 'Model');
        }
    }

    /**
     * The default template for a new model class.
     *
     * @param string $modelName The name of the model.
     * @return string The contents for a new model.
     */
    public static function modelTemplate(string $modelName): string {
        $table = Str::lcfirst($modelName);
        return <<<PHP
<?php
namespace App\Models;
use Core\Model;

/**
 * Implements features of the {$modelName} class.
 */
class {$modelName} extends Model {

    // Fields you don\'t want saved on form submit
    // public const blackList = [];

    // Set to name of database table.
    protected static \$_table = '{$table}';

    // Soft delete
    // protected static \$_softDelete = true;
    
    // Fields from your database


    public function afterDelete(): void {
        // Implement your function
    }

    public function afterSave(): void {
        // Implement your function
    }

    public function beforeDelete(): void {
        // Implement your function
    }

    public function beforeSave(): void {
        // Implement your function
    }

    /**
     * Performs validation for the {$modelName} model.
     *
     * @return void
     */
    public function validator(): void {
        // Implement your function
    }
}
PHP;
    }

    /**
     * The default template for a new upload model class.
     *
     * @param string $modelName The name of the model.
     * @return string The contents for a new model.
     */
    public static function uploadModelTemplate(string $modelName): string {
        $table = Str::lcfirst($modelName);
        return <<<PHP
<?php
namespace App\Models;
use Core\Model;

/**
 * Implements features of the {$modelName} class.
 */
class {$modelName} extends Model {

    // Fields you don\'t want saved on form submit
    // public const blackList = [];

    // Set to name of database table.
    protected static \$_table = '{$table}';

    // Soft delete
    // protected static \$_softDelete = true;
    
    // List your allowed file types.
    protected static \$allowedFileTypes = [];
    
    // Set your max file size.
    protected static \$maxAllowedFileSize = 5242880;

    // Set your file path.  Include your bucket if necessary.
    protected static \$_uploadPath = "";
    
    // Fields from your database


    public function afterDelete(): void {
        // Implement your function
    }

    public function afterSave(): void {
        // Implement your function
    }

    public function beforeDelete(): void {
        // Implement your function
    }

    public function beforeSave(): void {
        // Implement your function
    }

    /**
     * Getter function for \$allowedFileTypes array
     *
     * @return array \$allowedFileTypes The array of allowed file types.
     */
    public static function getAllowedFileTypes(): array {
        return self::\$allowedFileTypes;
    }

    /**
     * Getter function for \$maxAllowedFileSize.
     *
     * @return int \$maxAllowedFileSize The max file size for an individual 
     * file.
     */
    public static function getMaxAllowedFileSize(): int {
        return self::\$maxAllowedFileSize;
    }

    /**
     * Performs upload
     *
     * @return void
     */
    public static function uploadFile(): void {
        // Implement your function
    }
}
PHP;
    }
}