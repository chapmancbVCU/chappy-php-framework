<?php
declare(strict_types=1);

namespace Core\Traits;

/**
 * Specifies things users must include in order for the model to work.
 */
trait IsUser {
    /**
     * User classes must use this blackList
     */
    public const blackList = ['id','deleted'];

    /**
     * Flag to set mode for being able to change a password.  When 
     * true then the password can be changed or reset.  Default 
     * value is false.
     *
     * @var bool
     */
    private bool $changePassword = false;

    /**
     * Users must support the $confirm variable name contract for the 
     * confirm field.
     *
     * @var string|null
     */
    public ?string $confirm = null;

    /**
     * Set default value for db field.
     *
     * @var int
     */
    public int $deleted = 0;

    /**
     * Inactive status flag.
     *
     * @var int
     */
    public int $inactive = 0;

    /**
     * Login attempts counter.
     *
     * @var int
     */
    public int $login_attempts = 0;

    /**
     * The reset_password flag.  When set to 1 the user is asked to set a new 
     * password.
     *
     * @var int
     */
    public int $reset_password = 0;

    /**
     * Users models must implement soft delete.
     *
     * @var boolean
     */
    protected static bool $_softDelete = true;

    /**
     * Assists in setting value of inactive field based on POST.
     *
     * @return bool The value for inactive based on value received from 
     * POST.
     */
    public function isInactiveChecked(): bool {
        return $this->inactive === 1;
    }

    /**
     * Assists in setting value of reset_password field based on 
     * POST.
     *
     * @return bool The value for reset_password based on value 
     * received from POST.
     */
    public function isResetPWChecked(): bool {
        return $this->reset_password === 1;
    }

    /**
     * Setter function for $changePassword.
     *
     * @param bool $value The value we will assign to $changePassword.
     * @return void
     */
    public function setChangePassword(bool $value): void {
        $this->changePassword = $value;
    }
}