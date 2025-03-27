<?php
declare(strict_types=1);
namespace Console\Helpers;
use Core\Lib\Utilities\Str;

/**
 * Helper class for model related console commands.
 */
class Model {
    /**
     * The default template for a new model.
     *
     * @param string $modelName The name of the model.
     * @return string The contents for a new model.
     */
    public static function makeModel(string $modelName): string {
        return '<?php
namespace App\Models;
use Core\Model;
use Core\Lib\Utilities\Str;


/**
 * 
 */
class '.ucfirst($modelName).' extends Model {

    // Fields you don\'t want saved on form submit
    // public const blackList = [];

    // Set to name of database table.
    protected static $_table = \''.Str::lcfirst($modelName).'\';

    // Soft delete
    // protected static $_softDelete = true;
    
    // Fields from your database

    public function afterDelete(): void {
        //
    }

    public function afterSave(): void {
        //
    }

    public function beforeDelete(): void {
        //
    }

    public function beforeSave(): void {
        //
    }

    /**
     * Performs validation for the '.$modelName.' model.
     *
     * @return void
     */
    public function validator(): void {
        //
    }
}
';
    }
}