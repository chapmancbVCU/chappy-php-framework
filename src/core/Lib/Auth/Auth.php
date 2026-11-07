<?php
declare(strict_types=1);
namespace Core\Lib\Auth;

use Core\Exceptions\FrameworkRuntimeException;
use Core\Lib\Contracts\Guard;
use Core\Lib\Contracts\Principal;
use Core\Lib\Hashing\BcryptHasher;
use Core\Lib\Utilities\Config;
use Core\Models\User;

/**
 * Static entry point to the application's auth guard.  Lazily constructs
 * a single SessionGuard so every caller shares one resolved user per
 * request, and forwards the common guard operations (check, user, id) to
 * it.  A guard may also be injected via setGuard() to support testing.
 */
final class Auth {
    /**
     * The user model used when config/auth.php does not set user_model.
     */
    public const DEFAULT_USER_MODEL = 'App\\Models\\Users';

    /**
     * The shared guard instance for the current request, or null until
     * first constructed.
     *
     * @var Guard|null
     */
    private static ?Guard $guard = null;

    /**
     * Determines whether a user is currently authenticated.
     *
     * @return bool True if a user is authenticated, otherwise false.
     */
    public static function check(): bool {
        return self::guard()->check();
    }

    /**
     * Returns the shared guard, constructing it on first use.
     */
    public static function guard(): Guard {
        if(self::$guard === null) {
            self::$guard = new SessionGuard(
                new ModelUserProvider(new BcryptHasher(), self::userModel())
            );
        }
        return self::$guard;
    }

    /**
     * Gets the identifier of the currently authenticated user.
     *
     * @return mixed The authenticated user's identifier, or null if no
     * user is authenticated.
     */
    public static function id() {
        return self::guard()->id();

    }

    /**
     * Sets the shared guard instance, replacing any existing one.  Intended
     * primarily for testing, where a guard backed by a fake provider or
     * session can be injected.  Passing null resets the guard so the next
     * call to guard() constructs a fresh default instance.
     *
     * @param Guard|null $guard The guard to use, or null to reset.
     * @return void
     */
    public static function setGuard(?Guard $guard): void {
        self::$guard = $guard;
    }

    /**
     * Gets the currently authenticated user.
     *
     * @return Principal|null The authenticated user, or null if no user
     * is authenticated.
     */
    public static function user(): ?Principal {
        return self::guard()->user();
    }

    /**
     * Resolves the application's user model class.  Framework code uses this 
     * instead of importing App\Models\Users so Core does not depend on the 
     * application.  Reads auth.user_model from config/auth.php and falls back 
     * to App\Models\Users.
     *
     * @return class-string<User> The fully qualified user model class.
     * @throws FrameworkRuntimeException If the configured class does not 
     * exist or does not extend Core\Models\User.
     */
    public static function userModel(): string {
        $model = Config::get('auth.user_model', self::DEFAULT_USER_MODEL);

        if(!is_string($model) || !class_exists($model)) {
            throw new FrameworkRuntimeException(
                "User model '" . (is_string($model) ? $model : gettype($model)) . "' not found. " .
                "Set auth.user_model in config/auth.php to an existing class."
            );
        }

        if(!is_a($model, User::class, true)) {
            throw new FrameworkRuntimeException(
                "User model '{$model}' must extend " . User::class . "."
            );
        }

        return $model;
    }
}
