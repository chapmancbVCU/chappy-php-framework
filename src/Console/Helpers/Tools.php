<?php
declare(strict_types=1);
namespace Console\Helpers;

use Console\Console;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;

/**
 * Contains functions for miscellaneous tasks.
 */
class Tools extends Console {
    /**
     * Returns dashed border.
     *
     * @return string A dashed border.
     */
    public static function border(): string {
        return '--------------------------------------------------';
    }

    /**
     * Creates a directory.  It checks if it already exists.  If not, user is asked to confirm the want to create a new directory.
     *
     * @param string $directory The full path for the directory to be created.
     * @param InputInterface $input The Symfony InputInterface object.
     * @param OutputInterface $output The Symfony OutputInterface object.
     * @return int A value that indicates success, invalid, or failure.
     */
    public static function createDirWithPrompt(
        string $directory, 
        InputInterface $input, 
        OutputInterface $output
    ): int {
        // Check if directory exists
        if (!is_dir($directory)) {
            $message = "The directory '$directory' does not exist. Do you want to create it? (y/n)";
            if (self::confirm($message, $input, $output)) {
                self::pathExists($directory, 0755, true);
                console_info("Directory created: $directory");
                return Command::SUCCESS;
            } else {
                console_info('Operation canceled.');
                return Command::FAILURE;
            }
        }
        return Command::SUCCESS;
    }

    /**
     * Checks if parameter provided is equal to Command::FAILURE.
     *
     * @param mixed $param The value to be tested.
     * @return bool True if value is equal to Command::FAILURE.  Otherwise, we 
     * return false.
     */
    public static function isFailure(mixed $param): bool {
        return ($param === Command::FAILURE) ? true : false;
    }

    /**
     * Checks if application is in production mode.
     *
     * @return bool True if in production, otherwise we return false.
     */
    public static function isProduction(): bool {
        return env('APP_ENV') === 'production' ? true : false;
    }

    /**
     * Tests if a path exits and creates it if necessary.
     *
     * @param string $path The path to check if it exists.
     * @param int $permissions The permissions for the directory.
     * @param bool $recursive Optional.  Specifies if the recursive mode 
     * is set.
     * @return void
     */
    public static function pathExists(string $path, int $permissions = 0755, bool $recursive = true): void {
        if(!is_dir($path)) {
            mkdir($path, $permissions, $recursive);
        }
    }

    /**
     * Generates files for console commands
     *
     * @param string $path Where the file will be written.
     * @param string $content The contents of the file to be created.
     * @param string $name The name of the file, class, or other relevant information.
     * @return int A value that indicates success, invalid, or failure.
     */
    public static function writeFile(string $path, string $content, string $name): int {
        if(!file_exists($path)) {
            $resp = file_put_contents($path, $content);
            console_info(ucfirst($name) . ' successfully created');
            return Command::SUCCESS;
        } else {
            console_warning(ucfirst($name) . ' already exists');
            return Command::FAILURE;
        }
    }
}