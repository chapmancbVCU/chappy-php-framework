<?php
namespace core\Auth;
use Core\Input;
use App\Models\Users;
use Core\Models\Login;
use Core\Lib\Logging\Logger;

class AuthService {
    public static function attemptLogin(Input $request, Login $loginModel, string $username) : Login {
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
                $loginModel = Users::loginAttempts($user, $loginModel);
            }
            else {
                $loginModel->addErrorMessage('username','There is an error with your username or password');
                Logger::log('User failed to log in', 'warning');
            }
        }

        return $loginModel;
    }
}