<?php
declare(strict_types=1);
namespace Core\Lib\Logging;
use Core\Lib\Utilities\Env;
use Core\Lib\Utilities\Str;
/**
 * Supports the ability to produce logging.
 */
class Logger {
    private static string $logFile;// = ROOT.DS.'storage'.DS.'logs'.DS.'app.log'; 

    /**
     * Initializes the log file based on the environment (CLI or Web).
     */
    private static function init(): void {
        if (!defined('ROOT')) {
            throw new \Exception("ROOT constant is not defined.");
        }

        // Determine log file location
        self::$logFile = CHAPPY_BASE_PATH . DS . 'storage' . DS . 'logs' . DS .
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
     */
    public static function log(string $message, string $level = 'info'): void {
        if (!Env::get('DEBUG', false)) {
            return; // Skip logging if DEBUG is disabled
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
            die("Error: Log directory is not writable. Current permissions: " . substr(sprintf('%o', fileperms($logDir)), -4));
        }

        // Debug: Check file existence
        if (!file_exists(self::$logFile)) {
            touch(self::$logFile);
            chmod(self::$logFile, 0775);
        }

        // Debug: Check if file is writable
        if (!is_writable(self::$logFile)) {
            die("Error: Log file is not writable.");
        }

        // Write to log file
        $result = file_put_contents(self::$logFile, $logMessage, FILE_APPEND | LOCK_EX);

        if ($result === false) {
            die("Error: Unable to write to log file.");
        }
        
    }
}
