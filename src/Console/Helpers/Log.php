<?php
declare(strict_types=1);
namespace Console\Helpers;
/**
 * Supports ability to manage logs.
 */
class Log {
    private const LOG_PATH = ROOT.DS.'storage'.DS.'logs'.DS;

    /**
     * Performs delete operation on log files.
     *
     * @param string $fileName The name of the log file.
     * @return void
     */
    private static function delete(string $fileName): void {
        $path = self::LOG_PATH.$fileName;
        if(!file_exists($path)) return;
        if(unlink($path)) Tools::info($fileName.' succesfully cleared', 'green');
    }

    public static function deleteAllLogs() {
        self::deleteAppLog();
        self::deleteCliLog('cli.log');
        self::deletePHPUnitLog('phpunit.log',);
    }
    public static function deleteAppLog(): void {
        self::delete('app.log');
    }

    public static function deleteCliLog(): void {
        self::delete('cli.log');
    }

    public static function deletePHPUnitLog(): void {
        self::delete('phpunit.log');
    }
}
