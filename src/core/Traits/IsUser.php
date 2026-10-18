<?php
declare(strict_types=1);

namespace Core\Traits;
use Core\Services\AuthService;
use Core\Lib\Utilities\Arr;

/**
 * Specifies things users must include in order for the model to work.
 */
trait IsUser {
    /**
     * The users currently assigned acls.
     *
     * @var string
     */
    public string $acl;

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
     * The password field.
     *
     * @var string|null
     */
    public ?string $password = null;

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
     * Returns the user's assigned ACLs as an array.
     *
     * Decodes the JSON-encoded acl field. Returns an empty array if the field
     * is null, empty, or not valid JSON — so callers always get an array.
     *
     * @return array The user's ACLs, or an empty array if none/invalid.
     */
    public function getAcls(): array {
        $userAcls = json_decode($this->acl, true);
        return is_array($userAcls) ? $userAcls : [];
    }

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
     * Checks if the user has a specific ACL assigned.
     *
     * @param string $acl The ACL to check.
     * @return bool True if the user has the ACL, otherwise false.
     */
    public function hasAcl(string $acl): bool {
        $userAcls = json_decode($this->acl, true);
    
        if (!is_array($userAcls)) {
            return false; // Ensures it always returns a boolean
        }
        return in_array($acl, $userAcls, true);
    }

    /**
     * Setter function for `$changePassword`.
     *
     * @param bool $value The value we will assign to $changePassword.
     * @return void
     */
    public function setChangePassword(bool $value): void {
        $this->changePassword = $value;
    }

    /**
     * To be called within `beforeSave()`.
     *
     * @return void
     */
    private function userBeforeSave(): void {
        if($this->isNew()) {
            $this->password = AuthService::hashPassword($this->password);
        }
        if($this->changePassword) {
            $this->password = AuthService::hashPassword($this->password);
            $this->reset_password = 0;
        }
        
        // Ensure ACL is always stored as `[""]` when empty
        if (Arr::isEmpty(json_decode($this->acl, true))) {
            $this->acl = json_encode([""]);
        }
    }
}