<?php
declare(strict_types=1);
namespace Console\Helpers;
use Console\Helpers\Tools;

/**
 * Supports operations for the make:validator command.
 */
class Validator {
    private const VALIDATOR_PATH = ROOT.DS.'app'.DS.'CustomValidators'.DS;
    /**
     * Generates a new user defined validator class.
     *
     * @param string $validatorName Name for new validator class.
     * @return int A value that indicates success, invalid, or failure.
     */
    public static function makeValidator($validatorName): int {
        Tools::pathExists(self::VALIDATOR_PATH);
        return Tools::writeFile(
            self::VALIDATOR_PATH.$validatorName.'.php',
            self::validatorTemplate($validatorName),
            'Custom validator class'
        );
    }

    /**
     * Returns a string containing the structure for the new custom 
     * validator class with the name of the validator as the only parameter.
     *
     * @param string $validatorName The name for the new custom validator 
     * class.
     * @return string The contents for the new custom validator class.
     */
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