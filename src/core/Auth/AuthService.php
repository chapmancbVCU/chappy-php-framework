<?php
namespace core\Auth;
use Core\Input;
use Core\Cookie;
use Core\Session;
use App\Models\Users;
use Core\Models\Login;
use Core\Lib\Utilities\Env;
use Core\Lib\Utilities\Str;
use Core\Lib\Logging\Logger;
use Core\Models\UserSessions;

class AuthService {
    /**
     * Processes login attempts
     *
     * @param Input $request The request for the login.
     * @param Login $loginModel The login model.
     * @param string $username The user to be logged in.
     * @return Login Model that handles logins.
     */
    public static function login(Input $request, Login $loginModel, string $username) : Login {
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
            $user->loginUser($user, $remember);
            redirect('home');
        }  else {
            if($user) {
                $loginModel = self::loginAttempts($user, $loginModel);
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
     * @return Login $loginModel The Login model after login in attempt test 
     * and session messages are assigned.
     */
    public static function loginAttempts(Users $user, Login $loginModel): Login {
        if($user->login_attempts >= Env::get('MAX_LOGIN_ATTEMPTS', 5)) {
            $user->inactive = 1; 
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
     * @param bool $rememberMe Value obtained from remember me checkbox 
     * found in login form.  Default value is false.
     * @return void
     */
    public function loginUser(Users $usr, bool $rememberMe = false): void {
        $user = Users::findFirst([
            'conditions' => 'username = ?',
            'bind' => [$this->username]
        ]);

        if (!$user) {
            Logger::log("Failed login attempt: Username '{$usr->username}' not found.", 'warning');
        }

        if ($user->inactive == 1) {
            Logger::log("Failed login attempt: Inactive account for user ID {$user->id} ({$user->username}).", 'warning');
        }

        Session::set(Env::get('CURRENT_USER_SESSION_NAME'), $usr->id);
        Logger::log("User {$user->id} ({$user->username}) logged in successfully.", 'info');
        
        if($rememberMe) {
            $hash = Str::md5(uniqid() . rand(0, 100));
            $user_agent = Session::uagent_no_version();
            Cookie::set(Env::get('REMEMBER_ME_COOKIE_NAME'), $hash, Env::get('REMEMBER_ME_COOKIE_EXPIRY', 2592000));
            $fields = ['session'=>$hash, 'user_agent'=>$user_agent, 'user_id'=>$usr->id];
            Users::$_db->query("DELETE FROM user_sessions WHERE user_id = ? AND user_agent = ?", [$usr->id, $user_agent]);
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
    public static function loginUserFromCookie() {
        $userSession = UserSessions::getFromCookie();
        if($userSession && $userSession->user_id != '') {
            $user = Users::findById((int)$userSession->user_id);
            if($user) {
                $user->login();
            }
            return $user;
        }
        return;
    }

    /**
     * Logs user out.
     *
     * @return void
     */
    public static function logout(): void {
        $user = Users::currentUser();
        if($user) {
            $user->logout();
        }
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
        $user->confirm = $request->get('confirm');
        
        if($user->save()) {
            // PW change mode off.
            $user->reset_password = 0;
            $user->setChangePassword(false);    
            redirect('auth.login');
        }
    }

    /**
     * Sets ACL at registration.  If users table is empty the default 
     * value is Admin.  Otherwise, we set the value to "".
     *
     * @return string The value of the ACL we are setting upon 
     * registration of a user.
     */
    public static function setAclAtRegistration(): string {
        if(Users::findTotal() == 0) {
            return '["Admin"]';
        }
        return '[""]';
    }
}