<?php
declare(strict_types=1);
namespace Core;

use Core\Cookie;
use Core\Session;
use Core\FormHelper;
use Core\Lib\Utilities\Env;
use Core\Services\AuthService;

/**
 * Supports session management
 */
class SessionManager {
    /**
     * Checks if session exists and logs user in.  Logs user out if account 
     * status is inactive.
     *
     * @return void
     */
    public static function initialize(): void {
        if (!Session::exists(Env::get('CURRENT_USER_SESSION_NAME')) && Cookie::exists(Env::get('REMEMBER_ME_COOKIE_NAME'))) {
            $user = AuthService::loginUserFromCookie();
            
            if ($user) {
                if ($user->inactive == 1) {
                    $user->logout();
                    warning("Inactive user attempted auto-login: User ID {$user->id}");
                } else {
                    Session::set(Env::get('CURRENT_USER_SESSION_NAME'), $user->id);
                    info("User auto-logged in via Remember Me: User ID {$user->id}");
                }
            }
        }

        // Generate csrf token.
        FormHelper::generateToken();
    }
}
