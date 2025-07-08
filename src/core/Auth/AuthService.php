<?php
namespace core\Auth;
use Core\Input;
use App\Models\Users;
use Core\Models\Login;
use Core\Lib\Utilities\Env;
use Core\Lib\Logging\Logger;

class AuthService {
    /**
     * Processes login attempts
     *
     * @param Input $request The request for the login
     * @param Login $loginModel The login model
     * @param string $username The user to be logged in
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
            $user->login($remember);
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
    public static function loginAttempts($user, $loginModel) {
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
}