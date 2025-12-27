<?php
declare(strict_types=1);
namespace Console\Helpers;

use Core\Exceptions\Console\ConsoleException;
use Core\Lib\Logging\Logger;
use ReflectionClass;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

/**
 * Contains functions for miscellaneous tasks.
 */
class Tools {
    private static ?OutputInterface $output = null;

    public const BG_BLACK = '40';
    public const BG_RED = '41';
    public const BG_GREEN = '42';
    public const BG_YELLOW = '43';
    public const BG_BLUE = '44';
    public const BG_MAGENTA = '45';
    public const BG_CYAN = '46';
    public const BG_LIGHT_GREY = '47';

    public const TEXT_BLACK = '0;30';
    public const TEXT_WHITE = '1;37';
    public const TEXT_DARK_GREY = '1;30';
    public const TEXT_RED = '0;31';
    public const TEXT_GREEN = '0;32';
    public const TEXT_BROWN = '0;33';
    public const TEXT_YELLOW = '1;33';
    public const TEXT_BLUE = '0;34';
    public const TEXT_MAGENTA = '0;35';
    public const TEXT_CYAN = '0;36';
    public const TEXT_LIGHT_CYAN = '1;36';
    public const TEXT_LIGHT_GREY = '0;37';
    public const TEXT_LIGHT_RED = '1;31';
    public const TEXT_LIGHT_GREEN = '1;32';
    public const TEXT_LIGHT_BLUE = '1;34';
    public const TEXT_LIGHT_MAGENTA = '1;35'; 

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
            self::info('Helper could not be instantiated.', Logger::DEBUG, self::BG_RED);
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
                self::info("Directory created: $directory", Logger::INFO, self::BG_BLUE);
                return Command::SUCCESS;
            } else {
                self::info('Operation canceled.', Logger::DEBUG, self::BG_BLUE);
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
        $viewArray = explode(".", $input->getArgument($inputName));

        if (sizeof($viewArray) !== 2) {
            self::info(
                'Issue parsing argument. Make sure your input is in the format: <directory_name>.<file_name>',
                Logger::DEBUG,
                self::BG_RED
            );
            return Command::FAILURE;
        }
        return $viewArray;
    }

    /**
     * Checks if value for background color or text color matches list of 
     * available constants.
     *
     * @param string $value The value of constant passed into info()
     * @param string $type The type of value to determine what type of constant.
     * @return bool True if $value matches a available constant for background 
     * color or text color.
     * 
     * @throws ConsoleException Thrown when text and background colors are 
     * passed into info() in the wrong order.  Prints message indicating issue.
     */
    private static function hasConstant(string $value, string $type) {
        $reflectionClass = new ReflectionClass('Console\Helpers\Tools');
        $constants = $reflectionClass->getConstants();
        $constantKey = array_search($value, $constants);

        if(!str_contains($constantKey, $type)) {
            throw new ConsoleException("You are using an incorrect constant value for type $type.");
        }
        
        return in_array($value, $constants);
    }

    /**
     * Generates output messages for console commands.
     *
     * @param string $message The message we want to show.
     * @param string $level The level of severity for log file.  The valid 
     * levels are info, debug, warning, error, critical, alert, and emergency.
     * @param string $background The background color.  This function 
     * supports black, red, green, yellow, blue, magenta, cyan, and 
     * light-grey
     * @param string $text The color of the text.  This function supports 
     * black, white, dark-grey, red, green, brown, blue, magenta, cyan, 
     * light-cyan, light-grey, light-red, light green, light-blue, and 
     * light-magenta.
     * @return void
     */
    public static function info(string $message, string $level = Logger::INFO, string $background = self::BG_GREEN, string $text = self::TEXT_LIGHT_GREY): void {
        if (self::$output) {
            self::$output->writeln($message);
        }
        
        Logger::log($message, $level);

        // Perform console logging
        if (self::hasConstant($background, "BG") && self::hasConstant($text, "TEXT")) {
            $output = "\e[".$text.";".$background."m\n\n   ".$message."\n\e[0m\n";
            fwrite(STDOUT, $output);
            fflush(STDOUT);
        } else {
            $output = "\e[0;37;41m\n\n   Invalid background or text color.\n\e[0m\n";
            fwrite(STDOUT, $output);
            fflush(STDOUT);
        }
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
     * Enables output for symfony.
     *
     * @param OutputInterface $output Symfony output.
     * @return void
     */
    public static function setOutput(OutputInterface $output): void {
        self::$output = $output;
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
            self::info(ucfirst($name) . ' successfully created', Logger::INFO);
            return Command::SUCCESS;
        } else {
            self::info(ucfirst($name) . ' already exists', Logger::DEBUG, self::BG_RED, self::BG_BLACK);
            return Command::FAILURE;
        }
    }
}