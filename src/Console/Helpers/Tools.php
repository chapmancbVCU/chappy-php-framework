<?php
declare(strict_types=1);
namespace Console\Helpers;

use Core\Lib\Utilities\Arr;
use Core\Lib\Utilities\Env;
use Core\Lib\Utilities\Str;
use Core\Lib\Logging\Logger;
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
            self::info('Helper could not be instantiated.', 'debug', 'red');
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
                self::info("Directory created: $directory", 'blue');
                return Command::SUCCESS;
            } else {
                self::info('Operation canceled.', 'debug', 'blue');
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
            Tools::info(
                'Issue parsing argument. Make sure your input is in the format: <directory_name>.<file_name>',
                'debug',
                'red'
            );
            return Command::FAILURE;
        }
        return $viewArray;
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
    public static function info(string $message, string $level = Logger::INFO, ?string $background = null, ?string $text = null): void {
        if (self::$output) {
            self::$output->writeln($message);
        }

        // Load default colors from .env if not provided
        $background = $background ?? Env::get('BACKGROUND_COLOR', 'green'); // Default: green
        $text = $text ?? Env::get('TEXT_COLOR', 'light-grey'); // Default: light-grey
        $backgroundColor = [
            'black' => '40', 'red' => '41', 'green' => '42', 'yellow' => '43',
            'blue' => '44', 'magenta' => '45', 'cyan' => '46', 'light-grey' => '47'
        ];

        $textColor = [
            'black' => '0;30', 'white' => '1;37', 'dark-grey' => '1;30', 'red' => '0;31',
            'green' => '0;32', 'brown' => '0;33', 'yellow' => '1;33', 'blue' => '0;34',
            'magenta' => '0;35', 'cyan' => '0;36', 'light-cyan' => '1;36', 'light-grey' => '0;37',
            'light-red' => '1;31', 'light-green' => '1;32', 'light-blue' => '1;34', 'light-magenta' => '1;35'
        ];
        
        Logger::log($message, $level);

        // Perform console logging
        if (Arr::exists($backgroundColor, $background) && Arr::exists($textColor, $text)) {
            $output = "\e[".$textColor[$text].";".$backgroundColor[$background]."m\n\n   ".$message."\n\e[0m\n";
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
            Tools::info(ucfirst($name) . ' successfully created', Logger::INFO);
            return Command::SUCCESS;
        } else {
            Tools::info(ucfirst($name) . ' already exists', Logger::DEBUG, 'red');
            return Command::FAILURE;
        }
    }
}