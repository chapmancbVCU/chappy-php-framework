<?php
declare(strict_types=1);
namespace Core\Lib\Utilities;

/**
 * Class Env
 *
 * Handles loading and retrieving environment variables from a `.env` file.
 *
 * @package Core\Lib\Utilities
 */
class Env {
    /**
     * @var array Holds the loaded environment variables.
     */
    protected static array $variables = [];

    /**
     * Loads environment variables from a `.env` file.
     *
     * This method reads the given file, parses the key-value pairs,
     * and stores them in the `$variables` array. It also ensures
     * that boolean and numeric values are converted appropriately.
     *
     * @param string $file Path to the `.env` file.
     * @return void
     */
    public static function load(string $file): void {
        if (!file_exists($file)) {
            return;
        }

        $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);

            // Skip empty lines and comments
            if ($line === '' || Str::startsWith($line, '#')) {
                continue;
            }

            // Ensure the line contains '=' before exploding
            if (!Str::contains($line, '=')) {
                error_log("Invalid .env line: $line"); // Debugging
                continue;
            }

            list($key, $value) = explode('=', $line, 2);

            // Trim whitespace
            $key = trim($key);
            $value = trim($value);

            // Do NOT overwrite if already set via environment or phpunit.xml
            if (getenv($key) !== false || isset($_ENV[$key]) || isset($_SERVER[$key])) {
                continue;
            }

            // Remove surrounding quotes from value if present
            $value = preg_replace('/^["\'](.*)["\']$/', '$1', $value) ?? $value;

            // Convert boolean and numeric values correctly
            if (Str::lower($value) === 'true') {
                $value = true;
            } elseif (Str::lower($value) === 'false') {
                $value = false;
            } elseif (is_numeric($value)) {
                $value = $value + 0; // Converts "12" to 12, "30.5" to 30.5, etc.
            }

            static::$variables[$key] = $value;
            putenv("$key=$value");
            $_ENV[$key] = $value;
        }
    }

    /**
     * Retrieves an environment variable.
     *
     * Looks up the requested key in the loaded environment variables.
     * Returns the corresponding value or the specified default value
     * if the key does not exist.
     *
     * @param string $key The environment variable key.
     * @param mixed $default The default value to return if the key is not found.
     * @return mixed The value of the environment variable or the default value.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        // Priority: actual $_ENV > loaded config > default
        return $_ENV[$key] ?? self::$config[$key] ?? $default;
    }

}
