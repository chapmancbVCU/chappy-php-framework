<?php
declare(strict_types=1);
namespace Core\Models;

use Core\Model;
use Core\Lib\Utilities\Arr;
use Core\Services\AuthService;
use Core\Lib\Contracts\Principal;
use Core\Traits\{
    HasTimestamps,
    IsPrincipal,
    PasswordPolicy,
};

/**
 * Base user model.  Holds the columns and behavior the framework relies on
 * so Core never depends on the application's user model directly.
 * Applications extend this class (App\Models\Users by default) to add
 * columns, relations, or override validation.
 *
 * Framework code should resolve the concrete class with Auth::userModel()
 * rather than calling static methods on this class.  Static finders resolve
 * against the class they are called on, so User::findById() would hydrate a
 * base User instead of the application's subclass.
 */
class User extends Model implements Principal {
    use HasTimestamps, IsPrincipal, PasswordPolicy;

    /**
     * Columns converted when the model is serialized with toArray() or json_encode().
     * @var array<string, string>
     */
    protected array $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public ?string $description = null;
    public ?string $email = null;
    public ?string $fname = null;
    public int $id;
    public ?string $lname = null;
    protected static string $_table = 'users';
    public ?string $username = null;
    public const whiteList = ['username', 'email', 'password', 'fname', 'lname', 'description'];
    public const hidden = ['password','deleted', 'confirm', 'login_attempts'];

    /**
     * The users currently assigned acls.
     *
     * @var string
     */
    public string $acl;

    /**
     * Columns that may never be mass-assigned.  Model::assign() prefers 
     * whiteList when both are defined.
     */
    public const blackList = ['id','deleted'];

    /**
     * Flag to set mode for being able to change a password.  When 
     * true then the password can be changed or reset.  Default 
     * value is false.
     *
     * @var bool
     */
    protected bool $changePassword = false;

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
     * User records are soft deleted.
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
    protected function userBeforeSave(): void {
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

    /**
     * Implements beforeSave function described in Model parent class.
     * Ensures password is not in plain text but a hashed one.  The
     * reset_password flag is also set to 0.
     *
     * @return void
     */
    public function beforeSave(): void {
        $this->timeStamps();
        $this->userBeforeSave();
    }

    /**
     * Retrieves a list of all users except current logged in user.
     *
     * @param int $current_user_id The id of the currently logged in user.
     * @param array $params Used to build conditions for database query.  The
     * default value is an empty array.
     * @return array|bool The list of users that is returned from the database.
     */
    public static function findAllUsersExceptCurrent(int $current_user_id, array $params = []): array|bool {
        $conditions = [
            'conditions' => 'id != ?',
            'bind' => [(int)$current_user_id]
        ];
        // In case you want to add more conditions
        $conditions = Arr::merge($conditions, $params);
        return static::find($conditions);
    }

    /**
     * Finds user by username in the users table.
     *
     * @param string $username The username we want to find in the users table.
     * @return bool|object An object containing information about a user from
     * the users table.
     */
    public static function findByUserName(string $username): bool|object {
        return static::findFirst(['conditions'=> "username = ?", 'bind'=>[$username]]);
    }

    /**
     * Remember-me tokens are stored per device in the user_sessions table,
     * not on the users row.
     *
     * @return string|null Always null.
     */
    public function getRememberTokenName(): ?string { return null; }

    /**
     * Performs validation on user related forms.
     *
     * @return void
     */
    public function validator(): void {
        $this->runValidation('fname', ['required', 'max:150'], 'First Name');
        $this->runValidation('lname', ['required', 'max:150'], 'Last Name');
        $this->runValidation('email', ['required', 'max:150'], 'Email');
        $this->runValidation('password', ['required'], 'Password');
        $this->runValidation('username', ['required'], 'Username');

        if($this->isNew()) {
            $this->runValidation('username', ['min:6', 'max:150', 'unique:'.static::class], 'Username');
        }

        if($this->isNew() || $this->changePassword) {
            $validators = [
                "min:{$this->minLength()}",
                "max:{$this->maxLength()}",
                'lower',
                'upper',
                'number',
                'special'
            ];

            $this->runValidation('password', $validators, 'Password');
            $this->runValidation('confirm', ["match:{$this->password}"], 'Confirm Password');
        }
    }
}
