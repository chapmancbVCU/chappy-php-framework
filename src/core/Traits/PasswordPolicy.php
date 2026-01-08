<?php
declare(strict_types=1);
namespace Core\Traits;

/**
 * This trait contains a collection of getter functions for retrieving values 
 * regarding your password policy.
 */
trait PasswordPolicy {
    /**
     * Gets max length rule for password.
     *
     * @return bool
     */
    public function isMaxLength(): bool {
        return (env('SET_PW_MAX_LENGTH', false) === "true") ? true : false;
    }

    /**
     * Gets minimum rule for password.
     *
     * @return bool
     */
    public function isMinLength(): bool {
        return (env('SET_PW_MIN_LENGTH', false) === "true") ? true : false;
    }

    /**
     * Gets lower char requirement for password.
     *
     * @return bool
     */
    public function lowerChar(): bool {
        return (env('PW_LOWER_CHAR', false) === "true") ? true : false;
    }

    /**
     * Gets value for max length for passwords.
     *
     * @return string
     */
    public function maxLength(): string {
        return env('PW_MAX_LENGTH', 12);
    }

    /**
     * Gets value for min length for passwords.
     *
     * @return string
     */
    public function minLength(): string {
        return env('PW_MIN_LENGTH', 12);
    }

    /**
     * Gets numeric char requirement for password.
     *
     * @return bool
     */
    public function numericChar(): bool {
        return (env('PW_NUM_CHAR', false)  === "true") ? true : false;
    }

    /**
     * Gets special char requirement for password.
     *
     * @return bool
     */
    public function specialChar(): bool {
        return (env('PW_SPECIAL_CHAR', false) === "true") ? true : false;
    }

    /**
     * Gets upper char requirement for password.
     *
     * @return bool
     */
    public function upperChar(): bool {
        return (env('PW_LOWER_CHAR', false) === "true") ? true : false;
    }
}