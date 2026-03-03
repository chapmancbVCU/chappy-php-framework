<?php
declare(strict_types=1);

namespace Console;

use Core\Exceptions\FrameworkRuntimeException;

/**
 * Supports ability to validate console input.
 */
trait HasValidators {
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
     * Enforce rule where input must contain only alphabetic characters.
     *
     * @return static
     */
    public function alpha(): static {
        return $this->setValidator(function($response): void {
            if(preg_match('/[^a-zA-z]/', $response)) {
                throw new FrameworkRuntimeException("Input must contain only alphabetic characters.");
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
            if(preg_match('/[^a-z0-9]/i', $response)) {
                throw new FrameworkRuntimeException("Input must contain only alphanumeric characters.");
            }
        });
    }

    /**
     * Ensures input is between within a certain range in length.
     * @param int $minRule The minimum allowed size for input.
     * @param int $maxRule The maximum allowed size for input.
     * @return static
     */
    public function between(int $minRule, int $maxRule): static {
        return $this->setValidator(function($response) use ($minRule, $maxRule): void {
            if((strlen($response) < $minRule) || (strlen($response) > $maxRule)) {
                throw new FrameworkRuntimeException(
                    "This field must be between {$minRule} and {$maxRule} characters in length."
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
            if($response === $data) {
                throw new FrameworkRuntimeException("The these values must be different.");
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
            if(!filter_var($response, FILTER_VALIDATE_EMAIL)) {
                throw new FrameworkRuntimeException("Input must match valid E-mail format.");
            }
        });
    }

    /**
     * Enforce rule where input must be an integer.
     *
     * @return static
     */
    public function integer(): static {
        return $this->setValidator(function($response): void {
            if(!is_numeric($response) || str_contains($response, '.')) {
                throw new FrameworkRuntimeException("Input must be an integer.");
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
            if(!filter_var($response, FILTER_VALIDATE_IP)) {
                throw new FrameworkRuntimeException("Input must match valid IP address.");
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
            if(!preg_match('/[a-z]/', $response)) {
                throw new FrameworkRuntimeException("Input must contain at least one lower case character.");
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
            if($response !== $match) {
                throw new FrameworkRuntimeException("The these values do not match.");
            }
        });
    }

    /**
     * Ensures input meets requirements for maximum allowable length.
     *
     * @param int $maxRule The maximum allowed size for input.
     * @return static
     */
    public function max(int $maxRule): static {
        return $this->setValidator(function($response) use ($maxRule): void {
            if(strlen($response) > $maxRule) {
                throw new FrameworkRuntimeException("This field must be less than {$maxRule} characters in length.");
            } 
        });
    }

    /**
     * Ensures input meets requirements for minimum allowable length.
     *
     * @param int $minRule The minimum allowed size for input.
     * @return static
     */
    public function min(int $minRule): static {
        return $this->setValidator(function($response) use ($minRule): void {
            if(strlen($response) < $minRule) {
                throw new FrameworkRuntimeException("This field must be at least {$minRule} characters in length.");
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
            if(!is_numeric($response) || $response >= 0) {
                throw new FrameworkRuntimeException("Input must be a negative number.");
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
            if((preg_match('/[^a-zA-Z0-9]/', $response) == 1) || (!preg_match('/\s/', $response) == 0)) {
                throw new FrameworkRuntimeException("Input must contain no special characters.");
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
            if(!preg_match('/[0-9]/', $response)) {
                throw new FrameworkRuntimeException("Input must contain at least one numeric character.");
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
            if(!is_numeric($response)) {
                throw new FrameworkRuntimeException("Input must consist of only numeric characters.");
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
                throw new FrameworkRuntimeException('This field is required.');
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
            if(in_array(strtolower($response), $this->reservedKeywords)) {
                throw new FrameworkRuntimeException("{$response} is a reserved keyword and cannot be used.");
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
            if(!is_numeric($response) || $response <= 0) {
                throw new FrameworkRuntimeException("Input must be a positive number.");
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
            if(!(preg_match('/[^a-zA-Z0-9]/', $response) == 1) || (!preg_match('/\s/', $response) == 0)) {
                throw new FrameworkRuntimeException("Input must contain at least one special character.");
            }
        });
    }

    /**
     * Enforces rule when input must contain at least one lower case character.
     *
     * @return static
     */
    public function upper(): static {
        return $this->setValidator(function($response):void {
            if(!preg_match('/[A-Z]/', $response)) {
                throw new FrameworkRuntimeException("Input must contain at least one upper case character.");
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
            if(!filter_var($response, FILTER_VALIDATE_URL)) {
                throw new FrameworkRuntimeException("Input must match valid URL.");
            }
        });
    }

    /**
     * Calls validator callbacks.  This function also ensures validators 
     * don't bleed into next question if instance is reused.
     *
     * @param mixed $response The user answer.
     * @return void
     */
    protected function validate(mixed $response): void {
        foreach($this->validators as $callback) {
            $callback($response);
        }

        $this->validators = [];
    }
}