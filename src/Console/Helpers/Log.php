<?php
declare(strict_types=1);
namespace Console\Helpers;
/**
 * Supports ability to manage logs.
 */
class Log {
    /**
     * Performs delete operation on log files
     *
     * @param string $message The message to be displayed.
     * @param string $path The full path to the log file to be deleted.
     * @return void
     */
    public static function delete(string $message, string $path): void {
        if(unlink($path)) Tools::info($message, 'green');
    }
}
