<?php
declare(strict_types=1);
namespace Console\Helpers;

use Console\Console;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Supports ability to manage logs.
 */
class Log extends Console {
    /**
     * Path for log files.
     */
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
        if(unlink($path)) console_info($fileName.' succesfully cleared');
    }

    /**
     * Deletes all logs.
     *
     * @return void
     */
    public static function deleteAll(): void {
        self::deleteApp();
        self::deletePHPUnit();
        self::deleteCLI();
    }

    /**
     * Deletes app.log.
     *
     * @return void
     */
    public static function deleteApp(): void {
        self::delete('app.log');
    }

    /**
     * Asks user to confirm if they want to delete log files.
     *
     * @param string $logType The type of log to be deleted.
     * @param InputInterface $input The Symfony InputInterface object.
     * @param OutputInterface $output The Symfony OutputInterface object.
     * @return bool True if user confirms, otherwise we return false.
     */
    public static function deleteConfirm(string $logType, InputInterface $input, OutputInterface $output): bool {
        if($logType === "All") $message = "Are you sure you want to all of the logs?";
        else $message = "Are you sure you want to delete the {$logType} log?";
        return self::confirm($message, $input, $output);
    }

    /**
     * Asks user which log they want to delete.
     *
     * @param InputInterface $input The Symfony InputInterface object.
     * @param OutputInterface $output The Symfony OutputInterface object.
     * @return string The log type selected by the user.
     */
    public static function deletePrompt(InputInterface $input, OutputInterface $output): string {
        $message = "Which log do you want to delete (default: App)?";
        $options = ['App', 'CLI', 'PHPUnit', 'All'];
        return self::choice($message, $options, $input, $output, $options[0]);
    }

    /**
     * Deletes cli.log.
     *
     * @return void
     */
    public static function deleteCLI(): void {
        self::delete('cli.log');
    }

    /**
     * Deletes phpunit.log.
     *
     * @return void
     */
    public static function deletePHPUnit(): void {
        self::delete('phpunit.log');
    }
}
