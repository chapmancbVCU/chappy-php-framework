<?php
declare(strict_types=1);
namespace Console\Helpers;
use Console\Helpers\Tools;

class Validator {
    public static function makeValidator($validatorName) {
        return Tools::writeFile(
            ROOT.DS.'app'.DS.'CustomValidators'.DS.$validatorName.'.php',
            self::validatorTemplate($validatorName),
            'Custom validator class'
        );
    }

    private static function validatorTemplate($validatorName): string {
        return <<<PHP
<?php
namespace App\CustomValidators;
use Core\Validators\CustomValidator;
/**
 * Describe your validator class.
 */
class {$validatorName}Validator extends CustomValidator {

    /**
     * Describe your function.
     * 
     * @return bool
     */ 
    public function runValidation(): bool {
        // Implement your custom validator.
    }
}
PHP;
    }
}