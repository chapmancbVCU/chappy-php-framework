<?php
use Core\Lib\Logging\Logger;
use Console\ConsoleLogger;
/*
 * Contains globals for logging.
 */

if(!function_exists('alert')) {
    /**
     * Performs operations for adding content to log files using the alert 
     * severity level.
     *
     * @param string $message The description of an event that is being 
     * written to a log file.
     * @return void
     */
    function alert(string $message) {
        Logger::log($message, Logger::ALERT);
    }
}

if(!function_exists('console')) {
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
    function console(
        string $message, 
        string $level = Logger::INFO, 
        string $background = ConsoleLogger::BG_GREEN, 
        string $text = ConsoleLogger::TEXT_LIGHT_GREY
    ): void {
        ConsoleLogger::log($message, $level, $background, $text);
    }
}

if(!function_exists('console_alert')) {
    /**
     * Generates an alert console message.
     *
     * @param string $message The message to be printed to the console.
     * @return void
     */
    function console_alert(string $message): void {
        ConsoleLogger::log($message, Logger::ALERT, ConsoleLogger::BG_RED);
    }
}

if(!function_exists('console_critical')) {
    /**
     * Generates a critical console message.
     *
     * @param string $message The message to be printed to the console.
     * @return void
     */
    function console_critical(string $message): void {
        ConsoleLogger::log($message, Logger::CRITICAL, ConsoleLogger::BG_MAGENTA);
    }
}

if(!function_exists('console_debug')) {
    /**
     * Generates a debug console message.
     *
     * @param string $message The message to be printed to the console.
     * @return void
     */
    function console_debug(string $message, ): void {
        ConsoleLogger::log($message, Logger::DEBUG, ConsoleLogger::BG_BLUE);
    }
}

if(!function_exists('console_emergency')) {
    /**
     * Generates an emergency console message.
     *
     * @param string $message The message to be printed to the console.
     * @return void
     */
    function console_emergency(string $message): void {
        ConsoleLogger::log($message, Logger::EMERGENCY, ConsoleLogger::BG_RED);
    }
}

if(!function_exists('console_error')) {
    /**
     * Generates an error console message.
     *
     * @param string $message The message to be printed to the console.
     * @return void
     */
    function console_error(string $message,): void {
        ConsoleLogger::log($message, Logger::ERROR, ConsoleLogger::BG_RED);
    }
}

if(!function_exists('console_info')) {
    /**
     * Generates an info console message.
     *
     * @param string $message The message to be printed to the console.
     * @return void
     */
    function console_info(string $message, ): void {
        ConsoleLogger::log($message, Logger::INFO, ConsoleLogger::BG_GREEN);
    }
}

if(!function_exists('console_notice')) {
    /**
     * Generates a notice console message.
     *
     * @param string $message The message to be printed to the console.
     * @return void
     */
    function console_notice(
        string $message): void {
        ConsoleLogger::log($message, Logger::NOTICE, ConsoleLogger::BG_CYAN);
    }
}

if(!function_exists('console_warning')) {
    /**
     * Generates a warning console message.
     *
     * @param string $message The message to be printed to the console.
     * @return void
     */
    function console_warning(string $message): void {
        ConsoleLogger::log($message, Logger::WARNING, ConsoleLogger::BG_YELLOW);
    }
}

if(!function_exists('critical')) {
    /**
     * Performs operations for adding content to log files using the critical 
     * severity level.
     *
     * @param string $message The description of an event that is being 
     * written to a log file.
     * @return void
     */
    function critical(string $message) {
        Logger::log($message, Logger::CRITICAL);
    }
}

if(!function_exists('debug')) {
    /**
     * Performs operations for adding content to log files using the debug 
     * severity level.
     *
     * @param string $message The description of an event that is being 
     * written to a log file.
     * @return void
     */
    function debug(string $message) {
        Logger::log($message, Logger::DEBUG);
    }
}

if(!function_exists('emergency')) {
    /**
     * Performs operations for adding content to log files using the emergency 
     * severity level.
     *
     * @param string $message The description of an event that is being 
     * written to a log file.
     * @return void
     */
    function emergency(string $message) {
        Logger::log($message, Logger::EMERGENCY);
    }
}

if(!function_exists('error')) {
    /**
     * Performs operations for adding content to log files using the error 
     * severity level.
     *
     * @param string $message The description of an event that is being 
     * written to a log file.
     * @return void
     */
    function error(string $message) {
        Logger::log($message, Logger::ERROR);
    }
}

if(!function_exists('info')) {
    /**
     * Performs operations for adding content to log files using the info 
     * severity level.
     *
     * @param string $message The description of an event that is being 
     * written to a log file.
     * @return void
     */
    function info(string $message) {
        Logger::log($message, Logger::INFO);
    }
}

if(!function_exists('notice')) {
    /**
     * Performs operations for adding content to log files using the notice 
     * severity level.
     *
     * @param string $message The description of an event that is being 
     * written to a log file.
     * @return void
     */
    function notice(string $message) {
        Logger::log($message, Logger::NOTICE);
    }
}

if(!function_exists('warning')) {
    /**
     * Performs operations for adding content to log files using the warning 
     * severity level.
     *
     * @param string $message The description of an event that is being 
     * written to a log file.
     * @return void
     */
    function warning(string $message) {
        Logger::log($message, Logger::WARNING);
    }
}