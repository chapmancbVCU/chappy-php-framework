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
     * Array of values to always redact.
     */
    private const ALWAYS_REDACT_KEYS = [
        'password', 'password_confirmation', 'current_password', 'new_password',
        'pwd', 'pass', 'passphrase',
        'token', 'access_token', 'refresh_token', 'authorization',
        'api_key', 'secret', 'csrf_token',
        'remember_me', 'remember_token', 'session_id', 'phpsessid'
    ];

    /**
     * Produces a safe "shape" summary of query parameters without logging values.
     *
     * The summary includes:
     * - total parameter count
     * - parameter types
     * - string lengths and array sizes (when applicable)
     *
     * Example output:
     * `count=3 types=[int,string(12),null]`
     *
     * @param array $params Parameters bound to a prepared statement.
     * @return string A concise summary suitable for logs.
     */
    public static function paramSummary(array $params): string {
        $types = array_map(function ($p) {
            $t = gettype($p);
            if (is_string($p)) return "string(" . strlen($p) . ")";
            if (is_int($p)) return "int";
            if (is_float($p)) return "float";
            if (is_bool($p)) return "bool";
            if (is_null($p)) return "null";
            if (is_array($p)) return "array(" . count($p) . ")";
            return $t;
        }, $params);

        return "count=" . count($params) . " types=[" . implode(',', $types) . "]";
    }

    /**
     * Redact an associative array (key-aware). Use this for request data and
     * other structured payloads.
     */
    public static function redactAssoc(array $data): array {
        $out = [];

        foreach ($data as $key => $value) {
            $k = is_string($key) ? $key : (string)$key;
            $out[$key] = self::redactKeyValue($k, $value);
        }

        return $out;
    }

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
     * Redact a value using the key name as an additional signal.
     */
    private static function redactKeyValue(string $key, mixed $value): mixed { 
        $normalized = strtolower($key);

        $containsSensitive =
            str_contains($normalized, 'password') ||
            str_contains($normalized, 'token') ||
            str_contains($normalized, 'secret') ||
            str_contains($normalized, 'api_key') ||
            str_contains($normalized, 'apikey') ||
            str_contains($normalized, 'authorization') ||
            str_contains($normalized, 'csrf') ||
            str_contains($normalized, 'session') ||
            str_contains($normalized, 'cookie') ||
            str_contains($normalized, 'signature');

        if ($containsSensitive || in_array($normalized, self::ALWAYS_REDACT_KEYS, true)) {
            return '[REDACTED]';
        }

        // Recurse arrays with key-awareness if nested
        if (is_array($value)) {
            return self::redactAssoc($value);
        }

        return self::redact($value);
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
        if (preg_match('/^\$2y\$\d{2}\$.{53}$/', $s) || str_starts_with($s, '$argon2')) {
            return '[REDACTED hash]';
        }

        $looksLikeSecret =
            (strlen($s) >= 20 && preg_match('/^[A-Za-z0-9+\/=_\-.]+$/', $s))
            || preg_match('/\bbearer\b/i', $s)
            || preg_match('/eyJ[a-zA-Z0-9_-]+\.[a-zA-Z0-9_-]+\.[a-zA-Z0-9_-]+/', $s);

        if ($looksLikeSecret) {
            return '[REDACTED len=' . strlen($s) . ']';
        }

        if (filter_var($s, FILTER_VALIDATE_EMAIL)) {
            [$u, $d] = explode('@', $s, 2);
            $prefix = substr($u, 0, min(2, strlen($u)));
            return $prefix . '…@' . $d;
        }

        $len = strlen($s);
        if ($len > 64) {
            return substr($s, 0, 8) . '…[len=' . $len . ']';
        }

        return $s;
    }
}
