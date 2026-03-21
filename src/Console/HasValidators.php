<?php
declare(strict_types=1);

namespace Console;

use Core\Exceptions\FrameworkRuntimeException;
use Core\Lib\Utilities\Arr;

/**
 * Supports ability to validate console input.
 */
trait HasValidators {
    /**
     * An array of errors.
     *
     * @var array
     */
    protected array $errors = [];

    /**
     * The name of the field to be validated.
     *
     * @var string
     */
    protected string $fieldName = "";

    /**
     * An array of reserved keywords.
     *
     * @var array
     */
    protected array $reservedKeywords = [
        // Reserved keywords
        'abstract', 'and', 'array', 'as', 'break', 'callable', 'case', 'catch',
        'class', 'clone', 'const', 'continue', 'declare', 'default', 'die', 'do',
        'echo', 'else', 'elseif', 'empty', 'enddeclare', 'endfor', 'endforeach',
        'endif', 'endswitch', 'endwhile', 'eval', 'exit', 'extends', 'final',
        'finally', 'fn', 'for', 'foreach', 'function', 'global', 'goto', 'if',
        'implements', 'include', 'include_once', 'instanceof', 'insteadof',
        'interface', 'isset', 'list', 'match', 'namespace', 'new', 'or', 'print',
        'private', 'protected', 'public', 'readonly', 'require', 'require_once',
        'return', 'static', 'switch', 'throw', 'trait', 'try', 'unset', 'use',
        'var', 'while', 'xor', 'yield',

        // Predefined class names
        'self', 'parent', 'static',

        // Soft reserved / predefined constants
        'null', 'true', 'false',

        // Predefined classes worth avoiding
        'stdclass', 'exception', 'errorexception', 'closure', 'generator',
        'arithmetic error', 'typeerror', 'valueerror', 'stringable',

        // Enum related (PHP 8.1+)
        'enum',

        // Fiber related (PHP 8.1+)
        'fiber',
    ];

    /**
     * Array of validator callbacks.
     *
     * @var array
     */
    protected array $validators = [];

    /**
     * Adds a new error message to the $errors array.
     *
     * @param string $message The error message to be added to the $errors 
     * array.
     * @return void
     */
    public function addErrorMessage(string $message): void {
        $prefix = $this->fieldName ? "[$this->fieldName] " : "";
        $this->errors[] = "{$prefix}{$message}";
    }

    /**
     * Enforce rule where input must contain only alphabetic characters.
     *
     * @return static
     */
    public function alpha(): static {
        return $this->setValidator(function($response): void {
            if($response == null) return;
            if(preg_match('/[^a-zA-z]/', $response)) {
                $this->addErrorMessage("Input must contain only alphabetic characters.");
            }
        });
    }

    /**
     * Enforce rule where input must be alphanumeric characters.
     *
     * @return static
     */
    public function alphaNumeric(): static {
        return $this->setValidator(function($response):void {
            if($response == null) return;
            if(preg_match('/[^a-z0-9]/i', $response)) {
                $this->addErrorMessage("Input must contain only alphanumeric characters.");
            }
        });
    }

    /**
     * Ensures input is between within a certain range in length.
     * 
     * @param array $range 2 element array where position 0 is min and 
     * position 1 is max.
     * @return static
     */
    public function between(array $range): static {
        return $this->setValidator(function($response) use ($range): void {
            if(is_array($range)) {
                $minRule = $range[0];
                $maxRule = $range[1];
            }
            if($minRule >= $maxRule) {
                throw new FrameworkRuntimeException("between(): Min must be less than max.");
            }
            if($response == null) return;
            if((strlen($response) < $minRule) || (strlen($response) > $maxRule)) {
                $this->addErrorMessage(
                    "This field must be between {$minRule} and {$maxRule} characters in length."
                );
            } 
        });
    }

    /**
     * Checks if class exists within the specified namespace.
     *
     * @param array $namespace An array containing one element with string for 
     * the namespace.
     * @return static
     */
    public function classExists(array $namespace): static {
        return $this->setValidator(function($response) use ($namespace): void {
            if(is_array($namespace)) $namespace = $namespace[0];
            if($response == null) return;
            if(!class_exists($namespace.$response)) {
                $this->addErrorMessage("The '{$response}' class does not exist in the '{$namespace}' namespace.");
            }
        });
    }

    /**
     * Ensures response is in colon notation format.
     *
     * @return static
     */
    public function colonNotation(): static {
        return $this->setValidator(function($response): void {
            if($response == null) return;
            $arr = explode(":", $response);
            if(sizeof($arr) !== 2) {
                $this->addErrorMessage(
                    'Issue parsing data. Make sure your input is in the format: <arg_1>:<arg_2>'
                );
            }
        });
    }
    
    /**
     * Enforce rule where response and $match parameter needs to be different.
     *
     * @param mixed $data The value we want to compare.
     * @return static
     */
    public function different(mixed $data): static {
        return $this->setValidator(function($response) use ($data): void {
            if(is_array($data)) $data = $data[0];
            if($response == null) return;
            if($response === $data) {
                $this->addErrorMessage("These values must be different.");
            }
        });
    }

    /**
     * Displays a list of all error messages.
     *
     * @return void
     * 
     * @throws FrameworkRuntimeException Exception is thrown if an error is 
     * encountered.
     */
    public function displayErrorMessages(): void {
        if(Arr::isNotEmpty($this->errors)) {
            $errors = $this->errors;
            $this->errors = [];
            foreach($errors as $error) {
                console_error($error);
            }
        }
        $this->errors = [];
    }

    /**
     * Ensures response is in dot notation format.
     *
     * @return static
     */
    public function dotNotation(): static {
        return $this->setValidator(function($response): void {
            if($response == null) return;
            $arr = explode(".", $response);
            if(sizeof($arr) !== 2) {
                $this->addErrorMessage(
                    'Issue parsing data. Make sure your input is in the format: <arg_1>.<arg_2>'
                );
            }
        });
    }

    /**
     * Ensures input is a valid E-mail address.
     *
     * @return static
     */
    public function email(): static {
        return $this->setValidator(function($response): void {
            if($response == null) return;
            if(!filter_var($response, FILTER_VALIDATE_EMAIL)) {
                $this->addErrorMessage("Input must match valid E-mail format.");
            }
        });
    }

    /**
     * Sets name of field to be validated.
     *
     * @param string|array $fieldName The name of the field to be validated.
     * @return static
     */
    public function fieldName(string|array $fieldName): static {
        if(is_array($fieldName)) $fieldName = $fieldName[0];
        $this->fieldName = $fieldName;
        return $this;
    }

    /**
     * Enforce rule where input must be an integer.
     *
     * @return static
     */
    public function integer(): static {
        return $this->setValidator(function($response): void {
            if($response == null) return;
            if(!is_numeric($response) || str_contains($response, '.')) {
                $this->addErrorMessage("Input must be an integer.");
            }
        });
    }

    /**
     * Enforce rule where input must be a valid IP address.
     *
     * @return static
     */
    public function ip(): static {
        return $this->setValidator(function($response): void {
            if($response == null) return;
            if(!filter_var($response, FILTER_VALIDATE_IP)) {
                $this->addErrorMessage("Input must match valid IP address.");
            }
        });
    }

    /**
     * Ensure user inputs valid comma separated list of values.  The user must 
     * provide the following in the $attributes parameter:
     * 1) Class containing full namespaced path
     * 2) Name of function that returns an array of strings or a comma 
     * separated array of strings.
     * 3) A string value in this array as an alias (optional).
     * 
     * @param array $attributes A : separate list in the following format: 
     * NamespaceToClass\\Class:Method:Alias.
     * @return static
     */
    public function list(array $attributes): static {
        return $this->setValidator(function($response) use ($attributes): void {
            if(is_array($attributes)) {
                $class = $attributes[0];
                $list = $attributes[1];
                $alias = $attributes[2] ?? '';
            }

            if($response === null || $response === '') {
                console_debug("list Validator: No value provided.  May produce unexpected results if required validator is not used.");
                return;
            }
            
            if(method_exists($class, $list)) $all = $class::$list();
            else if(is_string($list)) {
                if(str_contains($list, ','))   $all = explode(',', $list);
                else $all = $list;
            }
            
            $tokens = self::tokens($response);
            if (in_array($alias, $tokens, true)) return;
            
            // Validate + dedupe
            $invalid = array_diff($tokens, $all);
            if (!empty($invalid)) {
                $this->addErrorMessage(
                    'Unknown value(s): ' . implode(', ', $invalid) .
                    '. Allowed: ' . implode(', ', $all) . ' or "an alias (optional)".'
                );
            }
        });
    }

    /**
     * Enforces rule when input must contain at least one lower case character.
     *
     * @return static
     */
    public function lower(): static {
        return $this->setValidator(function($response): void {
            if($response == null) return;
            if(!preg_match('/[a-z]/', $response)) {
                $this->addErrorMessage("Input must contain at least one lower case character.");
            }
        });
    }

    /**
     * Enforce rule where response and $match parameter needs to match.
     *
     * @param mixed $match The value we want to compare.
     * @return static
     */
    public function match(mixed $match): static {
        return $this->setValidator(function($response) use ($match): void {
            if($response == null) return;
            if($response !== $match) {
                $this->addErrorMessage("The these values do not match.");
            }
        });
    }

    /**
     * Ensures input meets requirements for maximum allowable length.
     *
     * @param int|array $maxRule The maximum allowed size for input.
     * @return static
     */
    public function max(int|array $maxRule): static {
        return $this->setValidator(function($response) use ($maxRule): void {
            if(is_array($maxRule)) $maxRule = $maxRule[0];
            if($response == null) return;
            if(strlen($response) > $maxRule) {
                $this->addErrorMessage("This field must be less than {$maxRule} characters in length.");
            } 
        });
    }

    /**
     * Ensures input meets requirements for minimum allowable length.
     *
     * @param int|array $minRule The minimum allowed size for input.
     * @return static
     */
    public function min(int|array $minRule): static {
        return $this->setValidator(function($response) use ($minRule): void {
            if(is_array($minRule)) $minRule = $minRule[0];
            if($response == null) return;
            if(strlen($response) < $minRule) {
                $this->addErrorMessage("This field must be at least {$minRule} characters in length.");
            } 
        });
    }

    /**
     * Enforces rule when input must be a negative number.
     *
     * @return static
     */
    public function negative(): static {
        return $this->setValidator(function($response): void {
            if($response == null) return;
            if(!is_numeric($response) || $response >= 0) {
                $this->addErrorMessage("Input must be a negative number.");
            }
        });
    }

    /**
     * Enforces rule when input must contain at least one numeric character.
     *
     * @return static
     */
    public function number(): static {
        return $this->setValidator(function($response): void {
            if($response == null) return;
            if(!preg_match('/[0-9]/', $response)) {
                $this->addErrorMessage("Input must contain at least one numeric character.");
            }
        });
    }

    /**
     * Enforces rule when input must contain no special characters.
     *
     * @return static
     */
    public function noSpecialChars(): static {
        return $this->setValidator(function($response): void {
            if($response == null) return;
            if((preg_match('/[^a-zA-Z0-9]/', $response) == 1) || (!preg_match('/\s/', $response) == 0)) {
                $this->addErrorMessage("Input must contain no special characters.");
            }
        });
    }

    /**
     * Enforce rule when reserved keywords should be avoided.
     *
     * @return static
     */
    public function notReservedKeyword(): static {
        return $this->setValidator(function($response) {
            if($response == null) return;
            if(in_array(strtolower($response), $this->reservedKeywords)) {
                $this->addErrorMessage("{$response} is a reserved keyword and cannot be used.");
            }
        });
    }

    /**
     * Enforce rule where input must contain only numeric characters.
     *
     * @return static
     */
    public function numeric(): static {
        return $this->setValidator(function($response): void {
            if($response == null) return;
            if(!is_numeric($response)) {
                $this->addErrorMessage("Input must consist of only numeric characters.");
            }
        });
    }

    /**
     * Ensures required input is entered.
     *
     * @return static
     */
    public function required(): static {
        return $this->setValidator(function($response): void {
            if($response === '' || $response === null) {
                $this->addErrorMessage('This field is required.');
            }
        });
    }
    
    /**
     * Enforces rule when input must a positive number.
     *
     * @return static
     */
    public function positive(): static {
        return $this->setValidator(function($response): void {
            if($response == null) return;
            if(!is_numeric($response) || $response <= 0) {
                $this->addErrorMessage("Input must be a positive number.");
            }
        });
    }

    /**
     * Adds validator to array of validators to be used.
     *
     * @param callable $validator The anonymous function for a validator.
     * @return static
     */
    public function setValidator(callable $validator): static {
        $this->validators[] = $validator;
        return $this;
    } 

    /**
     * Enforces rule when input must contain at least one special character.
     *
     * @return static
     */
    public function special(): static {
        return $this->setValidator(function($response):void {
            if($response == null) return;
            if(!(preg_match('/[^a-zA-Z0-9]/', $response) == 1) || (!preg_match('/\s/', $response) == 0)) {
                $this->addErrorMessage("Input must contain at least one special character.");
            }
        });
    }

    /**
     * Split on commas (tolerate spaces), normalize to lowercase, drop empties.  
     * Useful for cases where you have a comma separated string.
     *
     * @param string $data Comma separated strings of values to be converted 
     * into an array.
     * @return array An array containing values originally found in comma 
     * separated string.
     */
    protected static function tokens(string $data): array {
        $tokens = preg_split('/\s*,\s*/', $data, -1, PREG_SPLIT_NO_EMPTY);
        return array_map(static fn($s) => strtolower($s), $tokens);
    }

    /**
     * Enforces rule when input must contain at least one lower case character.
     *
     * @return static
     */
    public function upper(): static {
        return $this->setValidator(function($response):void {
            if($response == null) return;
            if(!preg_match('/[A-Z]/', $response)) {
                $this->addErrorMessage("Input must contain at least one upper case character.");
            }
        });
    }

    /**
     * Enforce rule where input must be a valid URL.
     *
     * @return static
     */
    public function url(): static {
        return $this->setValidator(function($response): void {
            if($response == null) return;
            if(!filter_var($response, FILTER_VALIDATE_URL)) {
                $this->addErrorMessage("Input must match valid URL.");
            }
        });
    }

    /**
     * Calls validator callbacks.  This function also ensures validators 
     * don't bleed into next question if instance is reused.
     *
     * @param mixed $response The user answer.
     * @return bool True if validation passed.  Otherwise, we return false.
     */
    protected function validate(mixed $response): bool {
        foreach($this->validators as $callback) {
            $callback($response);
        }

        if(Arr::isNotEmpty($this->errors)){
            $this->displayErrorMessages();
            return false;
        }

        $this->validators = [];
        $this->fieldName = "";
        return true;
    }
}