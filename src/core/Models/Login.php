<?php
namespace Core\Models;
use Core\Model;

/**
 * Extends the Model class.  Supports functions for the Login model.
 */
final class Login extends Model {
    /**
     * Password of user authenticating.
     *
     * @var string
     */
    public $password;

    /**
     * Indicates if remember_me was selected. 
     * @var bool
     */
    public $remember_me;

    /**
     * Placeholder table name.
     * @var string
     */
    protected static $_table = 'tmp_fake';

    /**
     * Username of authenticating user.
     * @var string
     */
    public $username;

    /**
     * Returns result for remember me checkbox so user stays logged in if it's
     * checked.
     *
     * @return bool The value for remember_me checkbox.
     */
    public function getRememberMeChecked(): bool {
        return $this->remember_me == 'on';
    }

    /**
     * Performs form validation checks for the login screen.
     *
     * @return void
     */
    public function validator(): void {
        $this->runValidation($this->required()->fieldName('username')->validate($this->username));
        $this->runValidation($this->required()->fieldName('password')->validate($this->password));
    }
}