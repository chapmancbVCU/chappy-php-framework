<?php
declare(strict_types=1);
namespace Core\Lib\Auth;

use Core\Lib\Contracts\Guard;
use Core\Lib\Contracts\Principal;
use Core\Lib\Hashing\BcryptHasher;

/**
 * Static entry point to the application's auth guard.  Lazily constructs
 * a single SessionGuard so every caller shares one resolved user per
 * request, and forwards the common guard operations (check, user, id) to
 * it.  A guard may also be injected via setGuard() to support testing.
 */
final class Auth {
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
                new ModelUserProvider(new BcryptHasher())
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
}