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
        self::EMERGENCY => 0,
        self::ALERT     => 1,
        self::CRITICAL  => 2,
        self::ERROR     => 3,
        self::WARNING   => 4,
        self::NOTICE    => 5,
        self::INFO      => 6,
        self::DEBUG     => 7,
    ];

    /** Path to log files. */
    private const string LOG_FILE_PATH = CHAPPY_BASE_PATH . DS . 'storage' . DS . 'logs' . DS; 

    /**
     * Full path and name of current log file.
     * @var string
     */
    private static string $logFile;

    /**
     * Get the caller's file and line number
     *
     * @return array An array containing the file name and line number.
     */
    private static function debugBacktrace(): array {
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
        $caller = $backtrace[1] ?? null;

        return [
            $file = $caller['file'] ?? 'Unknown File',
            $line = $caller['line'] ?? 'Unknown Line'
        ];
    }

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
     * Packages together the actual formatted log message with 
     *
     * @param string $level The severity level of the message.
     * @param string $message The original message.
     * @return string The formatted message for log file.
     */
    private static function generateLogMessage(string $level, string $message): string {
        [$file, $line] = self::debugBacktrace();
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

        $logMessage = self::generateLogMessage($level, $message);
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
        return self::LEVELS[$level] <= self::LEVELS[$configLevel];
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
