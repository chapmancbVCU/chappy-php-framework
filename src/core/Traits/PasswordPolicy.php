<?php
declare(strict_types=1);
namespace Core\Traits;

use Core\Lib\Utilities\Config;

/**
 * This trait contains a collection of getter functions for retrieving values
 * regarding your password policy.
 *
 * Values come from config/password.php.  Applications without that file fall
 * back to the PW_* variables in .env.  Env::load() converts "true" to a
 * boolean and numbers to integers, so values are normalized here rather than
 * compared to strings.
 */
trait PasswordPolicy {
    /**
     * Gets max length rule for password.
     *
     * @return bool True if the maximum length rule is enforced.
     */
    public function isMaxLength(): bool {
        return self::policyFlag('set_pw_max_length', 'SET_PW_MAX_LENGTH');
    }

    /**
     * Gets minimum rule for password.
     *
     * @return bool True if the minimum length rule is enforced.
     */
    public function isMinLength(): bool {
        return self::policyFlag('set_pw_min_length', 'SET_PW_MIN_LENGTH');
    }

    /**
     * Gets lower case character rule for password.
     *
     * @return bool True if a lower case character is required.
     */
    public function lowerChar(): bool {
        return self::policyFlag('pw_lower_char', 'PW_LOWER_CHAR');
    }

    /**
     * Gets max length for password.
     *
     * @return int The maximum password length.
     */
    public function maxLength(): int {
        return self::policyLength('pw_max_length', 'PW_MAX_LENGTH', 25);
    }

    /**
     * Gets minimum length for password.
     *
     * @return int The minimum password length.
     */
    public function minLength(): int {
        return self::policyLength('pw_min_length', 'PW_MIN_LENGTH', 12);
    }

    /**
     * Gets numeric character rule for password.
     *
     * @return bool True if a numeric character is required.
     */
    public function numericChar(): bool {
        return self::policyFlag('pw_num_char', 'PW_NUM_CHAR');
    }

    /**
     * Gets special character rule for password.
     *
     * @return bool True if a special character is required.
     */
    public function specialChar(): bool {
        return self::policyFlag('pw_special_char', 'PW_SPECIAL_CHAR');
    }

    /**
     * Gets upper case character rule for password.
     *
     * @return bool True if an upper case character is required.
     */
    public function upperChar(): bool {
        return self::policyFlag('pw_upper_char', 'PW_UPPER_CHAR');
    }

    /**
     * Reads a boolean password setting.
     *
     * @param string $configKey Key in config/password.php.
     * @param string $envKey Fallback .env variable.
     * @return bool The setting.  Accepts true, "true", 1, "1", "on" and "yes".
     */
    private static function policyFlag(string $configKey, string $envKey): bool {
        $value = Config::get("password.{$configKey}", env($envKey, false));
        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Reads a numeric password setting.
     *
     * @param string $configKey Key in config/password.php.
     * @param string $envKey Fallback .env variable.
     * @param int $default Used when neither source holds a number.
     * @return int The setting.
     */
    private static function policyLength(string $configKey, string $envKey, int $default): int {
        $value = Config::get("password.{$configKey}", env($envKey, $default));
        return is_numeric($value) ? (int)$value : $default;
    }
}
