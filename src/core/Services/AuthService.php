<?php
declare(strict_types=1);
namespace Core\Services;
use Core\DB;
use Core\Input;
use Core\Cookie;
use Core\Session;
use Core\Models\User;
use Core\Lib\Auth\Auth;
use Core\Models\Login;
use Core\Lib\Utilities\Env;
use Core\Lib\Utilities\Str;
use Core\Models\UserSessions;
use Core\Models\ProfileImages;
use Core\Lib\FileSystem\Uploads;
use Core\Lib\Mail\AccountDeactivatedMailer;

/**
 * Supports authentication operations.
 */
class AuthService {
    /**
     * Session key holding the id of the user who has just proven their 
     * credentials and must set a new password.  The reset page only acts on 
     * this user, so the user id in the URL cannot be used to reset someone 
     * else's password.
     */
    public const PENDING_PASSWORD_RESET = 'pending_password_reset_user_id';

    /**
     * Gets value of password confirm field.  Assumes field value is "confirm".
     *
     * @param Input $request The request.
     * @return string The value of the confirm field.
     */
    public static function confirm(Input $request): string {
        return $request->get('confirm');
    }

    /**
     * Checks if a user is logged in.
     *
     * @return User|false|null An object containing information about current 
     * logged in user from users table.
     */
    public static function currentUser(): User|false|null {
        return Auth::user();
    }

    /**
     * Hashes a plain text password using PHP's `password_hash()` with the default algorithm.
     *
     * @param string $password Original password submitted on a registration 
     * or update password form.
     * @return string The hashed version of the password.
     */
    public static function hashPassword(string $password): string {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    /**
     * Attempts to log a user in. If successful, resets login attempts and 
     * creates a session. Otherwise, tracks failed attempts and optionally 
     * triggers an email notification.
     *
     * @param Input $request The request for the login.
     * @param Login $loginModel The login model.
     * @param string $username The user to be logged in.
     * @param bool $mailer Sends account deactivated E-mail when user 
     * surpasses max number of login attempts before account is locked.
     * @return Login Model that handles logins.
     */
    public static function login(Input $request, Login $loginModel, string $username, bool $mailer = false) : Login {
        $user = Auth::userModel()::findByUsername($username);
        if($user && password_verify($request->get('password'), $user->password)) {
            if($user->reset_password == 1) {
                self::beginPasswordReset($user);
                redirect('auth.resetPassword', [$user->id]);
            }
            if($user->inactive == 1) {
                flashMessage(Session::DANGER, 'Account is currently inactive');
                redirect('auth.login');
            } 
            $remember = $loginModel->getRememberMeChecked();
            $user->login_attempts = 0;
            $user->save();
            self::loginUser($user, $remember);
            redirect(env('DEFAULT_CONTROLLER'));
        } else {
            if($user) {
                $loginModel = self::loginAttempts($user, $loginModel, $mailer);
            }
            else {
                $loginModel->addErrorMessage('There is an error with your username or password');
                warning('User failed to log in');
            }
        }

        return $loginModel;
    }

    /**
     * Tests for login attempts and sets session messages when there is a 
     * failed attempt or when account is locked.
     *
     * @param User $user The user whose login attempts we are tracking.
     * @param Login $loginModel The model that will be responsible for 
     * displaying messages.
     * @param bool $mailer Sends account deactivated E-mail when user 
     * surpasses max number of login attempts before account is locked.
     * @return Login $loginModel The Login model after login in attempt test 
     * and session messages are assigned.
     */
    public static function loginAttempts(User $user, Login $loginModel, bool $mailer = false): Login {
        $previousInactiveState = $user->inactive;
        if($user->login_attempts >= Env::get('MAX_LOGIN_ATTEMPTS', 5)) {
            $user->inactive = 1; 
        }
        if($previousInactiveState == 0 && $user->inactive == 1 && $mailer == true) {
            AccountDeactivatedMailer::sendTo($user);
        }

        if($user->login_attempts < Env::get('MAX_LOGIN_ATTEMPTS', 5)) {
            $loginModel->addErrorMessage('There is an error with your username or password.');
        } else {
            flashMessage(Session::DANGER, 'Your account has been locked due to too many failed login attempts.');
        }
        $user->login_attempts = $user->login_attempts + 1;
        $user->save();
        return $loginModel;
    }

    /**
     * Creates a session when the user logs in.  A new record is added to the 
     * user_sessions table and a cookie is created if remember me is 
     * selected.
     *
     * @param User $loginUser The user to be logged in.
     * @param bool $rememberMe Value obtained from remember me checkbox 
     * found in login form.  Default value is false.
     * @return void
     */
    public static function loginUser(User $loginUser, bool $rememberMe = false): void {
        $user = Auth::userModel()::findFirst([
            'conditions' => 'username = ?',
            'bind' => [$loginUser->username]
        ]);

        if (!$user) {
            warning("Failed login attempt: Username '{$loginUser->username}' not found.");
        }

        if ($user->inactive == 1) {
            warning("Failed login attempt: Inactive account for user ID {$user->id} ({$user->username}).");
        }

        Auth::guard()->login($loginUser, $rememberMe);
        info("User {$user->id} ({$user->username}) logged in successfully.");
    }

    /**
     * Logs in user from cookie.
     *
     * @return User The user associated with previous session.
     */
    public static function loginUserFromCookie(): ?User {
        $userSession = UserSessions::getFromCookie();
        if($userSession && $userSession->user_id != '') {
            $user = Auth::userModel()::findById((int)$userSession->user_id);
            if($user) {
                self::loginUser($user);
            }
            return $user;
        }
        return null;
    }

    /**
     * Logs out the currently logged-in user by clearing the session and 
     * deleting any active cookies.
     * 
     * @param Input $request The current request.
     * @return void
     */
    public static function logout(Input $request): void {
        if(!$request->isPost()) {
            flashMessage(Session::DANGER, 'You must logout through menu');
            redirect('home');
        }

        $request->csrfCheck();
        $user = self::currentUser();
        if($user) {
            self::logoutUser($user);
        }
    }

    /**
     * Perform logout operation on current logged in user.  The record for the 
     * current logged in user is removed from the user_session table and the 
     * corresponding cookie is deleted.
     *
     * @param User $user The user to be logged out.
     * @return bool Returns true if operation is successful.
     */
    public static function logoutUser(User $user): bool {
        Auth::guard()->logout();
        info("User {$user->id} ({$user->username}) logged out.");
        return true;
    }

    /**
     * Handles the complete flow of resetting a user’s password, including 
     * setting the confirmation field and updating the record.
     *
     * @param Input $request The request for the password reset action.
     * @param User $user The user whose password we will reset.
     * @return void
     */
    public static function passwordReset(Input $request, User $user): void {
        // Only the user who just logged in with a pending reset may set a 
        // new password.  Guards callers that load the user themselves.
        if(self::pendingPasswordResetUser((int)$user->id) === null) {
            warning("Rejected password reset for user {$user->id}: no pending reset in this session.");
            redirect('auth.login');
            return;
        }

        $user->assign($request->get(), $user::passwordFields, $user::WHITELIST);
            
        // PW mode on for correct validation.
        $user->setChangePassword(true);
        
        // Allows password matching confirmation.
        $user->confirm = self::confirm($request);
        
        if($user->save()) {
            // PW change mode off.
            $user->reset_password = 0;
            $user->setChangePassword(false);    
            Session::delete(self::PENDING_PASSWORD_RESET);
            redirect('auth.login');
        }
    }

    /**
     * Records that a user has proven their credentials and must now set a 
     * new password.  Call this when login detects the reset_password flag, 
     * before redirecting to the reset page.
     *
     * @param User $user The user who must reset their password.
     * @return void
     */
    public static function beginPasswordReset(User $user): void {
        Session::set(self::PENDING_PASSWORD_RESET, (int)$user->id);
    }

    /**
     * Returns the user whose password may be reset in this session.  The id 
     * must match the user recorded by beginPasswordReset() and that user 
     * must still have the reset_password flag set.
     *
     * @param int $id The user id from the reset page URL.
     * @return User|null The user, or null if this session has no pending 
     * reset for that id.
     */
    public static function pendingPasswordResetUser(int $id): ?User {
        if(!Session::exists(self::PENDING_PASSWORD_RESET)
            || (int)Session::get(self::PENDING_PASSWORD_RESET) !== $id) {
            return null;
        }

        $user = Auth::userModel()::findById($id);
        if(!$user instanceof User || (int)$user->reset_password !== 1) {
            Session::delete(self::PENDING_PASSWORD_RESET);
            return null;
        }
        return $user;
    }

    /**
     * Processes a profile image upload and returns an `Uploads` object. 
     * Assumes the input name is `profileImage`.
     *
     * @param User $user The user who uploaded a profile image.
     * @return Uploads|null The uploads object if it's created or null.
     */
    public static function profileImageUpload(User $user): ?Uploads{
        return Uploads::handleUpload(
            'profileImage',
            ProfileImages::class,
            ROOT . DS,
            "5mb",
            $user
        );
    }
}