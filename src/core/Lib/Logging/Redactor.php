<?php
declare(strict_types=1);
namespace Core\Lib\Logging;

final class Redactor
{
    public static function redact(mixed $value): mixed
    {
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

    private static function redactArray(array $arr): array
    {
        $out = [];
        foreach ($arr as $k => $v) {
            $out[$k] = self::redact($v);
        }
        return $out;
    }

    private static function redactString(string $s): string
    {
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
