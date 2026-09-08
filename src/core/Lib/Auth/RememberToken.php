<?php
declare(strict_types=1);
namespace Core\Lib\Auth;

/**
 * Generates and hashes remember-me tokens.  The raw token lives only in
 * the user's cookie; only its hash is ever stored.
 */
final class RememberToken {
    /**
     * Generates a 256-bit token string.
     * @return string A new 256-bit token (raw — this goes in the cookie).
     */
    public static function generate(): string {
        return bin2hex(random_bytes(32));
    }

    /**
     * Generates a sha256 hash.
     * @param string $token The raw token from the cookie.
     * @return string The value to store/look up in user_sessions.session.
     */
    public static function hash(string $token): string {
        return hash('sha256', $token);
    }
}