<?php
declare(strict_types=1);
namespace Core\Lib\Logging;

use Core\Lib\Utilities\Env;

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
     * Flag that is used to prevent invalid DB_LOG_PARAMS mode warnings per request.
     *
     * @var bool
     */
    private static bool $didWarnInvalidDbLogParams = false;

    /**
     * Determines whether an array is a list (0..n-1 keys).
     *
     * @param array $arr
     * @return bool
     */
    private static function isList(array $arr): bool {
        return array_is_list($arr);
    }

    /**
     * Encodes data to JSON for logs safely.
     *
     * @param mixed $data
     * @return string
     */
    private static function jsonForLog(mixed $data): string {
        $json = json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        if ($json === false) {
            // Fallback that won't throw and won't leak content
            return '[unencodable data]';
        }

        return $json;
    }

    /**
     * Formats query parameters for logging, based on the DB_LOG_PARAMS mode.
     *
     * Supported modes (via Env::get('DB_LOG_PARAMS')):
     * - **none**   (default): logs only parameter count and types/lengths (no values).
     * - **masked**: logs redacted values using safeParams().
     * - **full**  : logs full raw parameter values (not recommended outside local/dev).
     *
     * This is designed to prevent sensitive data (passwords, tokens, emails, etc.)
     * from being written to logs in production while still preserving useful debugging
     * context (execution timing, SQL, parameter shape).
     *
     * @param array $params Parameters bound to the prepared SQL statement.
     * @return string A log-safe string representation of the parameters.
     */
    public static function formatParamsForLog(array $params): string {
        $rawMode = Env::get('DB_LOG_PARAMS', 'none');
        $mode = self::normalizeParamLogMode(is_string($rawMode) ? $rawMode : null, 'none');

        $unsafe = Env::get('DB_LOG_UNSAFE', false);
        if ($mode === 'full' && !$unsafe) {
            $mode = 'masked';
        }

        return match ($mode) {
            'none' => self::paramSummary($params),

            'full' => self::jsonForLog($params),

            'masked' => self::jsonForLog(
                self::isList($params) ? self::redact($params) : self::redactAssoc($params)
            ),

            default => self::jsonForLog(
                self::isList($params) ? self::redact($params) : self::redactAssoc($params)
            ),
        };
    }

    /**
     * Normalizes DB_LOG_PARAMS to a safe, supported value.
     *
     * Accepts common .env formatting such as quoted values ('full', "masked")
     * and ignores leading/trailing whitespace. If the value is not recognized,
     * it falls back to a safe default and emits a warning.
     *
     * Allowed values: none|masked|full
     *
     * @param string|null $raw Raw config value (e.g. from Env::get()).
     * @param string $default Default mode to use if $raw is invalid.
     * @return string Normalized mode: 'none', 'masked', or 'full'.
     */
    private static function normalizeParamLogMode(?string $raw, string $default = 'none'): string {
        $mode = strtolower(trim(trim($raw ?? $default), "\"'"));
        $allowed = ['none', 'masked', 'full'];

        if (!in_array($mode, $allowed, true)) {
            if (!self::$didWarnInvalidDbLogParams) {
                warning("Invalid DB_LOG_PARAMS='{$raw}'. Using '{$default}'. Allowed: none|masked|full");
                self::$didWarnInvalidDbLogParams = true;
            }
            return $default;
        }

        return $mode;
    }

    /**
     * Produces a safe "shape" summary of query parameters without logging values.
     *
     * Example:
     * `count=3 types=[int,string(12),null]`
     *
     * @param array $params
     * @return string
     */
    public static function paramSummary(array $params): string {
        $types = array_map(function ($p) {
            if (is_string($p)) return "string(" . strlen($p) . ")";
            if (is_int($p)) return "int";
            if (is_float($p)) return "float";
            if (is_bool($p)) return "bool";
            if (is_null($p)) return "null";
            if (is_array($p)) return "array(" . count($p) . ")";
            if (is_object($p)) return "object(" . get_class($p) . ")";
            if (is_resource($p)) return "resource";

            return gettype($p);
        }, $params);

        return "count=" . count($params) . " types=[" . implode(',', $types) . "]";
    }

    /**
     * Redact an associative array (key-aware). Use this for request data and
     * other structured payloads.
     */
    public static function redactAssoc(array $data): array {
        if (self::isList($data)) {
            $redacted = self::redact($data);
            return $redacted;
        }

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
            // JWT-ish
            preg_match('/eyJ[a-zA-Z0-9_-]+\.[a-zA-Z0-9_-]+\.[a-zA-Z0-9_-]+/', $s)
            // Bearer token anywhere (case-insensitive)
            || preg_match('/\bbearer\b/i', $s)
            // Long base64-ish tokens: require at least one digit and one letter to reduce "all letters" false positives
            || (
                strlen($s) >= 24
                && preg_match('/^[A-Za-z0-9+\/=_\-.]+$/', $s)
                && preg_match('/[A-Za-z]/', $s)
                && preg_match('/[0-9]/', $s)
        );


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
