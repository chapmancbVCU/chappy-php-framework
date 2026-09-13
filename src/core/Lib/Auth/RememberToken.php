<?php
declare(strict_types=1);
namespace Core\Lib\Auth;

/**
 * Generates and hashes remember-me tokens.  The raw token lives only in
 * the user's cookie; only its hash is ever stored in user_sessions.
 *
 * The pair works together: generate() mints a high-entropy raw token for
 * the cookie, and hash() derives the value stored and looked up in the
 * database.  Because hash() is deterministic, auto-login can hash an
 * incoming cookie token and match it against the stored hash with a single
 * indexed query — while a leaked user_sessions table yields only hashes,
 * not usable tokens.  SHA-256 (rather than a slow password hash) is
 * appropriate here precisely because the input is already high-entropy and
 * the lookup must stay a fast, deterministic equality match.
 */
final class RememberToken {
    /**
     * Generates a 256-bit token string.
     *
     * @return string A new 256-bit token (raw — this goes in the cookie).
     */
    public static function generate(): string {
        return bin2hex(random_bytes(32));
    }

    /**
     * Generates a sha256 hash.
     *
     * @param string $token The raw token from the cookie.
     * @return string The value to store/look up in user_sessions.session.
     */
    public static function hash(string $token): string {
        return hash('sha256', $token);
    }
}