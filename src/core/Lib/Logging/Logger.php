<?php
declare(strict_types=1);
namespace Core\Lib\Logging;

use Core\Exceptions\Logger\LoggerException;
use Core\Exceptions\Logger\LoggerLevelException;
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

    /** Path to log files. */
    private const string LOG_FILE_PATH = CHAPPY_BASE_PATH . DS . 'storage' . DS . 'logs' . DS; 

    /**
     * Full path and name of current log file.
     * @var string
     */
    private static string $logFile;

    /**
     * Initializes the log file based on the environment (CLI or Web).
     */
    private static function init(): void {
        if (!defined('ROOT')) {
            throw new LoggerException("ROOT constant is not defined.");
        }

        // Determine log file location
        self::$logFile = self::LOG_FILE_PATH .
            (defined('PHPUNIT_RUNNING') ? 'phpunit.log' :
            (php_sapi_name() === 'cli' ? 'cli.log' : 'app.log'));

    }

    /**
     * Performs operations for adding content to log files.
     *
     * @param string $message The description of an event that is being 
     * written to a log file.
     * @param string $level Describes the severity of the message.
     * @return void
     * 
     * @throws LoggerLevelException If invalid severity level is provided an
     * exception is thrown.  Exception message is presented to user and 
     * written to log file.
     */
    public static function log(string $message, string $level = self::INFO): void {
        $loggingConfigLevel = Env::get("LOGGING");
        if(!self::verifyLoggingLevel()) {
            $message = "Invalid log level set in config: You entered $loggingConfigLevel -> " . $message;
            $level = self::ERROR;
        }

        if((Env::get('DEBUG') == 'E_WARNING') && ($level != self::INFO || $level != self::DEBUG || $level != self::NOTICE)) {
            return;
        }

        $reflectionClass = new ReflectionClass(__CLASS__);
        $constants = $reflectionClass->getConstants();
        $constantKey = array_search($level, $constants);
        if($constantKey === false) {
            $message = "Invalid log level set in config: You entered {$level} -> " . $message;
            $level = self::ERROR;
        }

        if (!isset(self::$logFile)) {
            self::init();
        }

        // Get the caller's file and line number
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
        $caller = $backtrace[1] ?? null; // Use index 1 to get the actual caller

        $file = $caller['file'] ?? 'Unknown File';
        $line = $caller['line'] ?? 'Unknown Line';

        // Dynamically determine the base path
        $basePath = defined('ROOT') ? ROOT : dirname(__DIR__, 3); 

        // Trim base path from filename
        $shortFile = Str::replace($basePath, '', $file);
        $shortFile = ltrim($shortFile, '/'); // Remove leading slash if present

        $date = date('Y-m-d H:i:s');
        $logMessage = "[$date - GMT] [$level] [$shortFile:$line] $message" . PHP_EOL;
        $logDir = dirname(self::$logFile);

        // Debug: Check directory existence
        if (!is_dir($logDir)) {
            mkdir($logDir, 0775, true);
        }

        // Debug: Check directory permissions
        if (!is_writable($logDir)) {
            throw new LoggerException(
                "Error: Log directory is not writable. Current permissions: " . substr(sprintf('%o', fileperms($logDir)), -4)
            );
            //die("Error: Log directory is not writable. Current permissions: " . substr(sprintf('%o', fileperms($logDir)), -4));
        }

        // Debug: Check file existence
        if (!file_exists(self::$logFile)) {
            touch(self::$logFile);
            chmod(self::$logFile, 0775);
        }

        // Debug: Check if file is writable
        if (!is_writable(self::$logFile)) {
            throw new LoggerException("Error: Log file is not writable.");
            // die("Error: Log file is not writable.");
        }

        // Write to log file
        $result = file_put_contents(self::$logFile, $logMessage, FILE_APPEND | LOCK_EX);

        if ($result === false) {
            throw new LoggerException("Error: Unable to write to log file.");
        }
    }

    public static function verifyLoggingLevel() {
        $level = Env::get('LOGGING');
        $isValid = false;
        switch ($level) {
            case self::ALERT:
                $isValid = true;
                break;
            case self::CRITICAL:
                $isValid = true;
                break;
            case self::DEBUG:
                $isValid = true;
                break;
            case self::EMERGENCY:
                $isValid = true;
                break;
            case self::ERROR:
                $isValid = true;
                break;
            case self::INFO:
                $isValid = true;
                break;
            case self::NOTICE:
                $isValid = true;
                break;
            case self::WARNING:
                $isValid = true;
                break;
            default:
                $isValid = false;
                break;
        }

        return $isValid;
    }
}
