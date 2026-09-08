<?php
declare(strict_types=1);
namespace Core\Lib\Auth;

use Core\Lib\Contracts\Guard;
use Core\Lib\Contracts\Principal;
use Core\Lib\Hashing\BcryptHasher;

/**
 * Static entry point to the application's auth guard.  Lazily constructs
 * a single SessionGuard so every caller shares one resolved user per request.
 */
final class Auth {
    private static ?Guard $guard = null;

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

    public static function id() {
        return self::guard()->id();

    }

    public static function setGuard(?Guard $guard): void {
        self::$guard = $guard;
    }

    public static function user(): ?Principal {
        return self::guard()->user();
    }
}