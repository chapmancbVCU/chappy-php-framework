<?php
declare(strict_types=1);
namespace Console\Helpers;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

/**
 * Contains functions for miscellaneous tasks.
 */
class Tools {
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
        InputInterface $cmdInput, 
        OutputInterface $cmdOutput
    ): int {
        // Manual instantiation to avoid `getHelper()` issues
        $helper = new QuestionHelper(); 

        if (!$helper) {
            console_error('Helper could not be instantiated.');
            return Command::FAILURE;
        }

        // Check if directory exists
        if (!is_dir($directory)) {
            $question = new ConfirmationQuestion(
                "The directory '$directory' does not exist. Do you want to create it? (y/n) ", 
                false
            );

            if ($helper->ask($cmdInput, $cmdOutput, $question)) {
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
     * Checks if input is in dot notation.  If in dot notation the string is 
     * placed in an array where the first index is the directory name.  The 
     * second element is the file name.  The structure is shown below:
     * 
     * ["directory_name","file_name"]
     * 
     * 
     * If not in the <directory_name>.<file_name> an error message is 
     * displayed an a Command::FAILURE integer value is returned.
     *
     * @param string $inputName The name in <directory_name>.<file_name> format.
     * @param InputInterface $input The Symfony InputInterface object.
     * @return array|int An array containing the contents of the $inputName 
     * variable.  If $inputName is not in correct format then Command::FAILURE 
     * is returned.
     */
    public static function dotNotationVerify(string $inputName, InputInterface $input): array|int {
        $arr = explode(".", $input->getArgument($inputName));

        if (sizeof($arr) !== 2) {
            console_warning(
                'Issue parsing argument. Make sure your input is in the format: <directory_name>.<file_name>',
            );
            return Command::FAILURE;
        }
        return $arr;
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