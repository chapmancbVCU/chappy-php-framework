<?php
declare(strict_types=1);
namespace Core\Lib\Auth;

use Core\Lib\Contracts\Guard;
use Core\Lib\Contracts\Principal;
use Core\Lib\Hashing\BcryptHasher;

final class Auth {
    private static ?Guard $guard = null;

    public static function guard(): Guard {
        if(self::$guard === null) {
            self::$guard = new SessionGuard(
                new ModelUserProvider(new BcryptHasher())
            );
        }
        return self::$guard;
    }

    public static function user(): ?Principal {
        return self::guard()->user();
    }

    public static function check(): bool {
        return self::guard()->check();
    }

    public static function id() {
        return self::guard()->id();
    }

    public static function setGuard(?Guard $guard): void {
        self::$guard = $guard;
    }
}