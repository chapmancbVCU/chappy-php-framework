<?php
use Core\Lib\Logging\Logger;
use Console\Helpers\Tools;

/*
 * Contains globals for logging.
 */

/**
 * Performs operations for adding content to log files using the alert 
 * severity level.
 *
 * @param string $message The description of an event that is being 
 * written to a log file.
 * @param string $level Describes the severity of the message.
 * @return void
 */
if(!function_exists('alert')) {
    function alert(string $message) {
        Logger::log($message, Logger::ALERT);
    }
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
if(!function_exists('console')) {
    function console(
        string $message, 
        string $level = Logger::INFO, 
        string $background = Tools::BG_GREEN, 
        string $text = Tools::TEXT_LIGHT_GREY
    ): void {
        Tools::info($message, $level, $background, $text);
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
        Tools::info($message, Logger::ALERT, Tools::BG_RED);
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
        Tools::info($message, Logger::CRITICAL, Tools::BG_MAGENTA);
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
        Tools::info($message, Logger::DEBUG, Tools::BG_BLUE);
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
        Tools::info($message, Logger::EMERGENCY, Tools::BG_RED);
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
        Tools::info($message, Logger::ERROR, Tools::BG_RED);
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
        Tools::info($message, Logger::INFO, Tools::BG_GREEN);
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
        Tools::info($message, Logger::NOTICE, Tools::BG_CYAN);
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
        Tools::info($message, Logger::WARNING, Tools::BG_YELLOW);
    }
}

/**
 * Performs operations for adding content to log files using the critical 
 * severity level.
 *
 * @param string $message The description of an event that is being 
 * written to a log file.
 * @param string $level Describes the severity of the message.
 * @return void
 */
if(!function_exists('critical')) {
    function critical(string $message) {
        Logger::log($message, Logger::CRITICAL);
    }
}

/**
 * Performs operations for adding content to log files using the debug 
 * severity level.
 *
 * @param string $message The description of an event that is being 
 * written to a log file.
 * @param string $level Describes the severity of the message.
 * @return void
 */
if(!function_exists('debug')) {
    function debug(string $message) {
        Logger::log($message, Logger::DEBUG);
    }
}

/**
 * Performs operations for adding content to log files using the emergency 
 * severity level.
 *
 * @param string $message The description of an event that is being 
 * written to a log file.
 * @param string $level Describes the severity of the message.
 * @return void
 */
if(!function_exists('emergency')) {
    function emergency(string $message) {
        Logger::log($message, Logger::EMERGENCY);
    }
}

/**
 * Performs operations for adding content to log files using the error 
 * severity level.
 *
 * @param string $message The description of an event that is being 
 * written to a log file.
 * @param string $level Describes the severity of the message.
 * @return void
 */
if(!function_exists('error')) {
    function error(string $message) {
        Logger::log($message, Logger::ERROR);
    }
}

/**
 * Performs operations for adding content to log files using the info 
 * severity level.
 *
 * @param string $message The description of an event that is being 
 * written to a log file.
 * @param string $level Describes the severity of the message.
 * @return void
 */
if(!function_exists('info')) {
    function info(string $message) {
        Logger::log($message, Logger::INFO);
    }
}

/**
 * Performs operations for adding content to log files using the notice 
 * severity level.
 *
 * @param string $message The description of an event that is being 
 * written to a log file.
 * @param string $level Describes the severity of the message.
 * @return void
 */
if(!function_exists('notice')) {
    function notice(string $message) {
        Logger::log($message, Logger::NOTICE);
    }
}

/**
 * Performs operations for adding content to log files using the warning 
 * severity level.
 *
 * @param string $message The description of an event that is being 
 * written to a log file.
 * @param string $level Describes the severity of the message.
 * @return void
 */
if(!function_exists('warning')) {
    function warning(string $message) {
        Logger::log($message, Logger::WARNING);
    }
}