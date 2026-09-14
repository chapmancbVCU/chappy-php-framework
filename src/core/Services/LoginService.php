<?php
declare(strict_types=1);
namespace Core\Services;

use Core\Input;
use Core\Lib\Auth\LoginResult;
use Core\Models\Login;
use App\Models\Users;
use Core\Lib\Auth\Auth;
use Core\Lib\Mail\AccountDeactivatedMailer;

/**
 * Login policy: verifies credentials and applies the account rules
 * (password-reset, inactive, failed-attempt lockout) as a returned
 * LoginResult, leaving all HTTP behavior to the caller.  Replaces the
 * redirect-driven AuthService::login()/loginAttempts() flow.
 */
final class LoginService {
    /**
     * Attempts to authenticate the given username.  On success the user is
     * logged in via the guard and their failed-attempt counter reset; on
     * failure, attempts are tracked and the account locked past the
     * configured maximum.  No redirects or flash messages are issued — the
     * outcome is returned for the caller to act on.
     *
     * @param Input $request The login request (supplies the password).
     * @param Login $loginModel The login model; failure messages are added
     * to it, matching the prior behavior.
     * @param string $username The submitted username.
     * @param bool $mailer Whether to send the account-deactivated email
     * when an account crosses into the locked state.
     * @return LoginResult The outcome of the attempt.
     */
    public static function attempt(
        Input $request,
        Login $loginModel,
        string $username,
        bool $mailer = false
    ): LoginResult {
        $user = Users::findByUserName($username);

        // Invalid credentials — unknown user or wrong password.
        if(!$user || !password_verify($request->get('password'), $user->password)) {
            if($user) {
                return self::registerFailedAttempt($user, $loginModel, $mailer);
            }

            $loginModel->addErrorMessage('There is an error with your username or password');
            warning('User failed to log in');
            return LoginResult::invalid();
        }

        // Valid credentials — apply account-state precedence.
        if($user->reset_password == 1) {
            return LoginResult::needsReset($user);
        }
        if($user->inactive == 1) {
            return LoginResult::inactive();
        }

        // Success
        $remember = $loginModel->getRememberMeChecked();
        $user->login_attempts = 0;
        $user->save();
        Auth::guard()->login($user, $remember);
        return LoginResult::success($user);
    }

    /**
     * Tracks a failed login attempt: locks the account once the configured
     * maximum is reached, optionally emails on the transition into the
     * locked state, records the appropriate failure message, and
     * increments the attempt counter.  Ports AuthService::loginAttempts()
     * verbatim, returning a LoginResult in place of the Login model.
     *
     * @param Users $user The user who failed to authenticate.
     * @param Login $loginModel The login model; the failure message is
     * added to it when the account is not yet locked.
     * @param bool $mailer Whether to send the deactivation email on the
     * transition into the locked state.
     * @return LoginResult INVALID while attempts remain, LOCKED once the
     * account has been locked.
     */
    private static function registerFailedAttempt(
        Users $user,
        Login $loginModel,
        bool $mailer = false
    ): LoginResult {
        $previousInactiveState = $user->inactive;
        $max = env('MAX_LOGIN_ATTEMPTS', 5);

        if($user->login_attempts >= $max) $user->inactive = 1;

        if($previousInactiveState == 0 && $user->inactive == 1 && $mailer == true) {
            AccountDeactivatedMailer::sendTo($user);
        }

        $locked = $user->login_attempts >= $max;
        if($locked) {
            $loginModel->addErrorMessage('There is an error with your username or password.');
        }

        $user->login_attempts = $user->login_attempts + 1;
        $user->save();
        return $locked ? LoginResult::locked() : LoginResult::invalid();
    }
}