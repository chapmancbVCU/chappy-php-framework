<?php
declare(strict_types=1);
namespace Core\Services;
use Core\DB;
use Core\Input;
use Core\Cookie;
use Core\Session;
use App\Models\Users;
use Core\Models\Login;
use Core\Lib\Utilities\Env;
use Core\Lib\Utilities\Str;
use Core\Lib\Logging\Logger;
use Core\Models\UserSessions;
use Core\Models\ProfileImages;
use Core\Lib\FileSystem\Uploads;
use Core\Lib\Mail\AccountDeactivatedMailer;

/**
 * Supports authentication operations.
 */
class AuthService {
    /**
     * Gets value of password confirm field.  Assumes field value is "confirm".
     *
     * @param Input $request The request.
     * @return void
     */
    public static function confirm(Input $request): string {
        return $request->get('confirm');
    }

    /**
     * Checks if a user is logged in.
     *
     * @return Users|null An object containing information about current 
     * logged in user from users table.
     */
    public static function currentUser(): ?Users {
        if(!isset(Users::$currentLoggedInUser) && Session::exists(Env::get('CURRENT_USER_SESSION_NAME'))) {
            Users::$currentLoggedInUser = Users::findById((int)Session::get(Env::get('CURRENT_USER_SESSION_NAME')));
        }
        return Users::$currentLoggedInUser;
    }

    /**
     * Hashes password.
     *
     * @param string $password Original password submitted on a registration 
     * or update password form.
     * @return void
     */
    public static function hashPassword(string $password): string {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    /**
     * Processes login attempts
     *
     * @param Input $request The request for the login.
     * @param Login $loginModel The login model.
     * @param string $username The user to be logged in.
     * @param bool $mailer Sends account deactivated E-mail when user 
     * surpasses max number of login attempts before account is locked.
     * @return Login Model that handles logins.
     */
    public static function login(Input $request, Login $loginModel, string $username, bool $mailer = false) : Login {
        $user = Users::findByUsername($username);
        if($user && password_verify($request->get('password'), $user->password)) {
            if($user->reset_password == 1) {
                redirect('auth.resetPassword', [$user->id]);
            }
            if($user->inactive == 1) {
                flashMessage('danger', 'Account is currently inactive');
                redirect('auth.login');
            } 
            $remember = $loginModel->getRememberMeChecked();
            $user->login_attempts = 0;
            $user->save();
            self::loginUser($user, $remember);
            redirect('home');
        }  else {
            if($user) {
                $loginModel = self::loginAttempts($user, $loginModel, $mailer);
            }
            else {
                $loginModel->addErrorMessage('username','There is an error with your username or password');
                Logger::log('User failed to log in', 'warning');
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
    public static function loginAttempts(Users $user, Login $loginModel, bool $mailer = false): Login {
        $previousInactiveState = $user->inactive;
        if($user->login_attempts >= Env::get('MAX_LOGIN_ATTEMPTS', 5)) {
            $user->inactive = 1; 
        }
        if($previousInactiveState == 0 && $user->inactive == 1 && $mailer == true) {
            AccountDeactivatedMailer::sendTo($user);
        }

        if($user->login_attempts < Env::get('MAX_LOGIN_ATTEMPTS', 5)) {
            $loginModel->addErrorMessage('username', 'There is an error with your username or password.');
        } else {
            flashMessage('danger', 'Your account has been locked due to too many failed login attempts.');
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
     * @param Users $loginUser The user to be logged in.
     * @param bool $rememberMe Value obtained from remember me checkbox 
     * found in login form.  Default value is false.
     * @return void
     */
    public static function loginUser(Users $loginUser, bool $rememberMe = false): void {
        $user = Users::findFirst([
            'conditions' => 'username = ?',
            'bind' => [$loginUser->username]
        ]);

        if (!$user) {
            Logger::log("Failed login attempt: Username '{$loginUser->username}' not found.", 'warning');
        }

        if ($user->inactive == 1) {
            Logger::log("Failed login attempt: Inactive account for user ID {$user->id} ({$user->username}).", 'warning');
        }

        Session::set(Env::get('CURRENT_USER_SESSION_NAME'), $loginUser->id);
        Logger::log("User {$user->id} ({$user->username}) logged in successfully.", 'info');
        
        if($rememberMe) {
            $hash = Str::md5(uniqid() . rand(0, 100));
            $user_agent = Session::uagent_no_version();
            Cookie::set(Env::get('REMEMBER_ME_COOKIE_NAME'), $hash, (int)Env::get('REMEMBER_ME_COOKIE_EXPIRY', 2592000));
            $fields = ['session'=>$hash, 'user_agent'=>$user_agent, 'user_id'=>$loginUser->id];
            DB::getInstance()->query("DELETE FROM user_sessions WHERE user_id = ? AND user_agent = ?", [$loginUser->id, $user_agent]);
            $us = new UserSessions();
            $us->assign($fields);
            $us->save();
            Logger::log("Remember Me token set for user {$user->id} ({$user->username}).", 'info');
        }
    }

    /**
     * Logs in user from cookie.
     *
     * @return Users The user associated with previous session.
     */
    public static function loginUserFromCookie(): ?Users {
        $userSession = UserSessions::getFromCookie();
        if($userSession && $userSession->user_id != '') {
            $user = Users::findById((int)$userSession->user_id);
            if($user) {
                self::loginUser($user);
            }
            return $user;
        }
        return null;
    }

    /**
     * Logs user out.
     *
     * @return void
     */
    public static function logout(): void {
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
    public static function logoutUser(Users $user): bool {
        $userSession = UserSessions::getFromCookie();
        if($userSession) {
            $userSession->delete();
        }
        Session::delete(Env::get('CURRENT_USER_SESSION_NAME'));
        if(Cookie::exists(Env::get('REMEMBER_ME_COOKIE_NAME'))) {
            Cookie::delete(Env::get('REMEMBER_ME_COOKIE_NAME'));
        }
        $user::$currentLoggedInUser = null;
        Logger::log("User {$user->id} ({$user->username}) logged out.", 'info');
        return true;
    }

    /**
     * Resets password.
     *
     * @param Input $request The request for the password reset action.
     * @param Users $user The user whose password we will reset.
     * @return void
     */
    public static function passwordReset(Input $request, Users $user): void {
        $user->assign($request->get(), Users::blackListedFormKeys);
            
        // PW mode on for correct validation.
        $user->setChangePassword(true);
        
        // Allows password matching confirmation.
        $user->confirm = self::confirm($request);
        
        if($user->save()) {
            // PW change mode off.
            $user->reset_password = 0;
            $user->setChangePassword(false);    
            redirect('auth.login');
        }
    }

    public static function profileImageUpload(Users $user): ?Uploads{
        return Uploads::handleUpload(
            $_FILES['profileImage'],
            ProfileImages::class,
            ROOT . DS,
            "5mb",
            $user,
            'profileImage'
        );
    }
}