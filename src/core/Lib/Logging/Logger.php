<?php
declare(strict_types=1);
namespace Core\Lib\Logging;

use Core\Lib\Utilities\Env;
use Core\Lib\Utilities\Str;
use ReflectionClass;

/**
 * Supports the ability to produce logging.
 */
class Logger {
    /** Constant for alert level emergency. */
    public const EMERGENCY = 'emergency';
    /** Constant for alert level alert. */
    public const ALERT = 'alert';
    /** Constant for alert level critical. */
    public const CRITICAL = 'critical';
    /** Constant for alert level error. */
    public const ERROR = 'error';
    /** Constant for alert level warning. */
    public const WARNING = 'warning';
    /** Constant for alert level notice. */
    public const NOTICE = 'notice';
    /** Constant for alert level info. */
    public const INFO = 'info';
    /** Constant for alert level debug. */
    public const DEBUG = 'debug';

    /**
     * Associative array of levels mapped to integers based on severity.
     */
    private const LEVELS = [
        self::EMERGENCY => 600,
        self::ALERT     => 550,
        self::CRITICAL  => 500,
        self::ERROR     => 400,
        self::WARNING   => 300,
        self::NOTICE    => 250,
        self::INFO      => 200,
        self::DEBUG     => 100,
    ];

    /** Path to log files. */
    private const string LOG_FILE_PATH = CHAPPY_BASE_PATH . DS . 'storage' . DS . 'logs' . DS; 

    /**
     * Full path and name of current log file.
     * @var string
     */
    private static string $logFile;

    /**
     * Performs system logging if we cannot write to log file.  We also dump 
     * the message to the console/view.
     *
     * @param string $message The message to be logged.
     * @return void
     */
    private static function emergencyFallback(string $message): void {
        $loggingMessage = "CHAPPY LOGGER EMERGENCY {$message}";
        error_log($loggingMessage);
        dump($loggingMessage);
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

        return match ($mode) {
            'none'   => self::paramSummary($params),
            'full'   => json_encode($params),
            default  => json_encode(self::safeParams($params))
        };
    }

    /**
     * Packages together the actual formatted log message with 
     *
     * @param string $level The severity level of the message.
     * @param string $message The original message.
     * @return string The formatted message for log file.
     */
    private static function generateLogMessage(string $level, string $message, string $file, int $line): string {
        $basePath = defined('ROOT') ? ROOT : dirname(__DIR__, 3); 

        $shortFile = Str::replace($basePath, '', $file);
        $shortFile = ltrim($shortFile, '/');

        $date = date('Y-m-d H:i:s');
        
        return "[$date - GMT] [$level] [$shortFile:$line] $message" . PHP_EOL;
    }

    /**
     * Initializes the log file based on the environment (CLI or Web).
     */
    private static function init(): void {
        if (!defined('ROOT')) {
            self::emergencyFallback("ROOT constant is not defined.");
        }

        // Determine log file location
        self::$logFile = self::LOG_FILE_PATH .
            (defined('PHPUNIT_RUNNING') ? 'phpunit.log' :
            (php_sapi_name() === 'cli' ? 'cli.log' : 'app.log'));

    }

    /**
     * Checks if log directory is writeable.  If not writable we write message 
     * to PHP log file and dump message to console or view.
     *
     * @param string $logDir Path for the log directory
     * @return void
     */
    private static function isLogDirWritable(string $logDir): void {
        if (!is_writable($logDir)) {
            self::emergencyFallback(
                "Log directory is not writable. Current permissions: " . substr(sprintf('%o', fileperms($logDir)), -4)
            );
        }
    }

    /**
     * Checks if log file is writable.  If not writable we write message 
     * to PHP log file and dump message to console or view.
     *
     * @return void
     */
    private static function isLogFileWritable(): void {
        if (!is_writable(self::$logFile)) {
            self::emergencyFallback("Log file is not writable.");
        }
    }

    /**
     * Performs operations for adding content to log files.
     *
     * @param string $message The description of an event that is being 
     * written to a log file.
     * @param string $level Describes the severity of the message.
     * @return void
     */
    public static function log(string $message, string $level = self::INFO): void {
        $loggingConfigLevel = Env::get("LOGGING");
        if(!self::verifyLoggingLevel($loggingConfigLevel)) {
            $message = "Invalid log level set in config: You entered $loggingConfigLevel -> " . $message;
            $level = self::CRITICAL;
        }

        if(!self::shouldLog($level)) return;

        if(!self::verifyLoggingLevel($level)) {
            $message = "Invalid log level passed as a parameter: You entered {$level} -> " . $message;
            $level = self::CRITICAL;
        }

        if (!isset(self::$logFile)) self::init();
        
        // Checks to ensure we can log to file.
        $logDir = dirname(self::$logFile);
        self::logDirExists($logDir);
        self::isLogDirWritable($logDir);
        self::logFileExists();
        self::isLogFileWritable();

        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
        $caller = $backtrace[1] ?? null;
        $file = $caller['file'] ?? 'Unknown File';
        $line = $caller['line'] ?? 'Unknown Line';

        $logMessage = self::generateLogMessage($level, $message, $file, $line);
        $result = file_put_contents(self::$logFile, $logMessage, FILE_APPEND | LOCK_EX);

        if ($result === false) {
            self::emergencyFallback("Unable to write to log file.");
        }
    }

    /**
     * Checks if log directory exists.  If not, then we create it.
     *
     * @param string $logDir Path for the log directory.
     * @return void
     */
    private static function logDirExists(string $logDir): void {
        if (!is_dir($logDir)) {
            mkdir($logDir, 0775, true);
        }
    }

    /**
     * Checks if log file exists.  If not, then we create it.
     *
     * @return void
     */
    private static function logFileExists(): void {
        if (!file_exists(self::$logFile)) {
            touch(self::$logFile);
            chmod(self::$logFile, 0775);
        }
    }

    /**
     * Normalizes and validates the DB_LOG_PARAMS mode from configuration.
     *
     * Accepts: none, masked, full (case-insensitive, ignores surrounding quotes/whitespace).
     * Falls back to $default if invalid.
     *
     * @param string|null $raw     Raw value from env/config.
     * @param string      $default Default mode if invalid.
     * @return string One of: 'none', 'masked', 'full'.
     */
    private static function normalizeParamLogMode(?string $raw, string $default = 'none'): string {
        $mode = $raw ?? $default;

        $mode = trim($mode);
        $mode = trim($mode, "\"'"); // handles 'full' or "full"

        $mode = strtolower($mode);

        $allowed = ['none', 'masked', 'full'];

        if (!in_array($mode, $allowed, true)) {
            // Mis-config shouldn't break execution; fall back safely.
            warning("Invalid DB_LOG_PARAMS='{$raw}'. Using '{$default}'. Allowed: none|masked|full");
            return $default;
        }

        return $mode;
    }
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
    private static function paramSummary(array $params): string {
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
     * Returns a redacted copy of query parameters suitable for logging.
     *
     * This method attempts to prevent common secret leakage by:
     * - Redacting token-like strings (base64-ish, JWT-ish, Bearer tokens)
     * - Truncating long strings to a short prefix + length indicator
     * - Masking email usernames (optional behavior included)
     * - Summarizing arrays/objects rather than dumping them
     *
     * Note: This is a best-effort sanitizer for logs. For maximum safety,
     * prefer DB_LOG_PARAMS=none in production.
     *
     * @param array $params Parameters bound to a prepared statement.
     * @return array A sanitized array of parameters safe to JSON encode for logs.
     */
    private static function safeParams(array $params): array {
        return array_map(function ($p) {
            if (is_null($p) || is_int($p) || is_float($p) || is_bool($p)) {
                return $p;
            }

            if (is_string($p)) {
                $s = $p;

                // common secret patterns
                $looksLikeSecret =
                    strlen($s) >= 20 && preg_match('/^[A-Za-z0-9+\/=_\-.]+$/', $s) // tokens/base64-ish
                    || str_contains($s, 'Bearer ')
                    || preg_match('/eyJ[a-zA-Z0-9_-]+\.[a-zA-Z0-9_-]+\.[a-zA-Z0-9_-]+/', $s); // JWT-ish

                if ($looksLikeSecret) {
                    return '[REDACTED len=' . strlen($s) . ']';
                }

                // general string masking
                $len = strlen($s);
                if ($len > 64) {
                    return substr($s, 0, 8) . '…[len=' . $len . ']';
                }

                // emails can be masked too if desired
                if (filter_var($s, FILTER_VALIDATE_EMAIL)) {
                    [$u, $d] = explode('@', $s, 2);
                    return substr($u, 0, 2) . '…@' . $d;
                }

                return $s;
            }

            // arrays/objects shouldn't usually be here; summarize
            if (is_array($p)) {
                return '[array count=' . count($p) . ']';
            }

            return '[type=' . gettype($p) . ']';
        }, $params);
    }

    /**
     * Logs to file based on level.  If level is info the anything with a 
     * severity level greater or equal to is logged.
     *
     * @param string $level The level passed as a parameter to the log 
     * function.
     * @return bool True we will log based on level.  Otherwise, we return 
     * false.
     */
    public static function shouldLog(string $level): bool {
        $configLevel = Env::get("LOGGING");

        if (!isset(self::LEVELS[$configLevel]) || !isset(self::LEVELS[$level])) {
            return true; // fail-soft: don’t block logs due to config typos
        }

        // Log if the message is as severe or more severe than config threshold
        return self::LEVELS[$level] >= self::LEVELS[$configLevel];
    }

    /**
     * Tests if the PSR-3 logging level that is provided is valid
     *
     * @param string $level The PSR-3 level to be tested.
     * @return bool True if the level is valid.  Otherwise, we return false.
     */
    public static function verifyLoggingLevel(string $level): bool {
        $reflectionClass = new ReflectionClass(__CLASS__);
        $constants = $reflectionClass->getConstants();
        $constantKey = array_search($level, $constants);
        if($constantKey === false) {
            return false;
        }
        return true;
    }
}
