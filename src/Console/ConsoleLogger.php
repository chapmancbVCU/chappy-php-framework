<?php
declare(strict_types=1);
namespace Console;

use Core\Lib\Utilities\Env;
use Core\Lib\Logging\Logger;
use ReflectionClass;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Supports all operations related to writing output to console.
 */
final class ConsoleLogger {
    /**
     * Supports ability to log information to console.
     *
     * @var OutputInterface|null
     */
    private static ?OutputInterface $output = null;

    /** Value for background black. */
    public const BG_BLACK = '40';
    /** Value for background red. */
    public const BG_RED = '41';
    /** Value for background green. */
    public const BG_GREEN = '42';
    /** Value for background yellow. */
    public const BG_YELLOW = '43';
    /** Value for background blue. */
    public const BG_BLUE = '44';
    /** Value for background magenta. */
    public const BG_MAGENTA = '45';
    /** Value for background cyan. */
    public const BG_CYAN = '46';
    /** Value for background light grey */
    public const BG_LIGHT_GREY = '47';

    /** Value for text black. */
    public const TEXT_BLACK = '0;30';
    /** Value for text white. */
    public const TEXT_WHITE = '1;37';
    /** Value for text dark grey. */
    public const TEXT_DARK_GREY = '1;30';
    /** Value for text red. */
    public const TEXT_RED = '0;31';
    /** Value for text green. */
    public const TEXT_GREEN = '0;32';
    /** Value for text brown. */
    public const TEXT_BROWN = '0;33';
    /** Value for text yellow. */
    public const TEXT_YELLOW = '1;33';
    /** Value for text blue. */
    public const TEXT_BLUE = '0;34';
    /** Value for text magenta. */
    public const TEXT_MAGENTA = '0;35';
    /** Value for text cyan. */
    public const TEXT_CYAN = '0;36';
    /** Value for text light cyan. */
    public const TEXT_LIGHT_CYAN = '1;36';
    /** Value for text light grey. */
    public const TEXT_LIGHT_GREY = '0;37';
    /** Value for text light red. */
    public const TEXT_LIGHT_RED = '1;31';
    /** Value for text light green. */
    public const TEXT_LIGHT_GREEN = '1;32';
    /** Value for text light blue. */
    public const TEXT_LIGHT_BLUE = '1;34';
    /** Value for text light magenta. */
    public const TEXT_LIGHT_MAGENTA = '1;35'; 

    /**
     * Performs actual action of writing output to console with specified 
     * background color, text color, and message.
     *
     * @param string $output The output string for the console.
     * @return void
     */
    private static function consoleLog(string $output): void {
        fwrite(STDOUT, $output);
        fflush(STDOUT);
    }

    /**
     * Fixes background color when invalid background color is set for 
     * originating message.
     *
     * @param string $level The severity level passed as a parameter to the 
     * self::log function.
     * @return string The correct background color based on severity level.
     */
    private static function fixBackgroundColor(string $level): string {
        $background = '';
        switch($level) {
            case Logger::ALERT;
                $background = self::BG_RED;
                break;
            case Logger::CRITICAL;
                $background = self::BG_MAGENTA;
                break;
            case Logger::DEBUG:
                $background = self::BG_BLUE;
                break;
            case Logger::EMERGENCY:
                $background = self::BG_RED;
                break;
            case Logger::ERROR:
                $background = self::BG_RED;
                break;
            case Logger::INFO:
                $background = self::BG_GREEN;
                break;
            case Logger::NOTICE:
                $background = self::BG_CYAN;
                break;
            case Logger::WARNING:
                $background = self::BG_YELLOW;
                break;
            default:
                $background = self::BG_GREEN;
                break;
        }
        return $background;
    }

    /**
     * Checks if value for background color or text color matches list of 
     * available constants.
     *
     * @param string $value The value of constant passed into info()
     * @param string $type The type of value to determine what type of constant.
     * @return bool True if $value matches a available constant for background 
     * color or text color.
     */
    private static function hasConstant(string $value, string $type): bool {
        $reflectionClass = new ReflectionClass(__CLASS__);
        $constants = $reflectionClass->getConstants();
        $constantKey = array_search($value, $constants);

        if($constantKey == false) {
            return false;
        }
        if(!str_contains($constantKey, $type)) {
            $output = "\e[0;37;41m\n\n   Console Error: You are using an incorrect constant value for type $type.\n\e[0m\n";
            self::consoleLog($output);
            return false;
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
    public static function log(
        string $message, 
        string $level = Logger::INFO, 
        string $background = self::BG_GREEN, 
        string $text = self::TEXT_LIGHT_GREY
    ): void {
        if(!Logger::shouldLog($level)) return;
        
        if (self::$output) {
            self::$output->writeln($message);
        }
        Logger::log($message, $level);

        // Check if text and background color is correct.
        if(!self::hasConstant($background, "BG")) {
            $output = "\e[0;37;41m\n\n   Invalid background color.  Setting default color for severity level.  We recommend using built-in constants.\n\e[0m\n";
            $background = self::fixBackgroundColor($level);
            self::consoleLog($output);
        }
        if(!self::hasConstant($text, "TEXT")) {
            $output = "\e[0;37;41m\n\n   Invalid text color.  Setting to default.  We recommend using built-in constants.\n\e[0m\n";
            $text = self::TEXT_LIGHT_GREY;
            self::consoleLog($output);
        }

        // Perform console logging
        $output = "\e[".$text.";".$background."m\n\n   ".$message."\n\e[0m\n";
        self::consoleLog($output);

        $loggingConfigLevel = Env::get("LOGGING");
        if(!Logger::verifyLoggingLevel($loggingConfigLevel)) {
            $criticalMessage = "Invalid log level set in config: You entered $loggingConfigLevel";
            $output = "\e[".$text.";".self::BG_MAGENTA."m\n\n   ".$criticalMessage."\n\e[0m\n";
            self::consoleLog($output);
        }

        if(!Logger::verifyLoggingLevel($level)) {
            $criticalMessage = "Invalid log level passed as a parameter: You entered $level";
            $output = "\e[".$text.";".self::BG_MAGENTA."m\n\n   ".$criticalMessage."\n\e[0m\n";
            self::consoleLog($output);
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
}