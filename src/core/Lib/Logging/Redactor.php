<?php
declare(strict_types=1);
namespace Core\Lib\Logging;

/**
 * Utility class responsible for sanitizing and redacting values before
 * they are written to log files.
 *
 * This class is primarily used by the Logger to prevent sensitive data
 * (password hashes, tokens, JWTs, long strings, emails, etc.) from being
 * written directly to logs.
 *
 * Supported behavior:
 * - Primitive values (int, float, bool, null) are returned unchanged.
 * - Arrays are recursively sanitized.
 * - Strings are inspected for common secret patterns and masked if needed.
 * - Long strings are truncated with length preserved.
 * - Emails have their local part masked.
 * - Unknown types are summarized by type.
 *
 * This keeps logs useful for debugging while minimizing the risk of
 * leaking credentials or personally identifiable information.
 */
final class Redactor {
    /**
     * Redacts or sanitizes a value for safe logging.
     *
     * Primitive values are returned as-is. Strings and arrays are inspected
     * and masked or summarized as appropriate.
     *
     * @param mixed $value The value to sanitize.
     * @return mixed The sanitized value suitable for logging.
     */
    public static function redact(mixed $value): mixed {
        if ($value === null || is_int($value) || is_float($value) || is_bool($value)) {
            return $value;
        }

        if (is_array($value)) {
            return self::redactArray($value);
        }

        if (!is_string($value)) {
            return '[type=' . gettype($value) . ']';
        }

        return self::redactString($value);
    }

    /**
     * Recursively sanitizes all values in an array.
     *
     * Each element is passed through the main redact() method to ensure
     * nested structures are handled consistently.
     *
     * @param array $arr The array to sanitize.
     * @return array The sanitized array.
     */
    private static function redactArray(array $arr): array {
        $out = [];
        foreach ($arr as $k => $v) {
            $out[$k] = self::redact($v);
        }
        return $out;
    }

    /**
     * Sanitizes a string value for logging.
     *
     * This method detects and masks:
     * - Password hashes (bcrypt/argon)
     * - Bearer tokens
     * - JWTs
     *
     * It also:
     * - Masks email usernames
     * - Truncates long strings while preserving length
     * - Preserves short, non-sensitive strings
     *
     * @param string $s The string to sanitize.
     * @return string The sanitized string.
     */
    private static function redactString(string $s): string {
        $len = strlen($s);

        // always redact password hashes + bearer/jwt
        if (str_starts_with($s, '$2y$') || str_starts_with($s, '$2a$') || str_starts_with($s, '$argon2')) {
            return "[REDACTED hash len={$len}]";
        }
        if (stripos($s, 'bearer ') !== false) {
            return "[REDACTED bearer len={$len}]";
        }
        if (preg_match('/^eyJ[a-zA-Z0-9_-]*\.[a-zA-Z0-9_-]+\.[a-zA-Z0-9_-]+$/', $s)) {
            return "[REDACTED jwt len={$len}]";
        }

        // paths: truncate, don't redact
        if (str_contains($s, '/') || str_contains($s, '\\')) {
            return $len > 96 ? substr($s, 0, 32) . "…[len={$len}]" : $s;
        }

        // email: mask user part
        if (filter_var($s, FILTER_VALIDATE_EMAIL)) {
            [$u, $d] = explode('@', $s, 2);
            return substr($u, 0, 2) . "…@{$d}";
        }

        // long strings: truncate
        return $len > 128 ? substr($s, 0, 32) . "…[len={$len}]" : $s;
    }
}
