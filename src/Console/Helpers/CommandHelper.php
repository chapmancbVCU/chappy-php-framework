<?php
declare(strict_types=1);
namespace Console\Helpers;

use Console\Console;
use Console\Helpers\Tools;

/**
 * Supports commands related to building console commands and associated 
 * helper classes.
 */
class CommandHelper extends Console {
    /**
     * The path for user defined command classes.
     */
    private const COMMAND_PATH = ROOT.DS.'app'.DS.'Lib'.DS.'Console'.DS.'Commands'.DS;

    /**
     * The path for user defined command helper classes.
     */
    private const HELPER_PATH = ROOT.DS.'app'.DS.'Lib'.DS.'Console'.DS.'Helpers'.DS;
    
    /**
     * Generates new class that extends Command.
     *
     * @param string $commandName The name for the new command class.
     * @return int A value that indicates success, invalid, or failure.
     */
    public static function makeCommand(string $commandName): int {
        Tools::pathExists(self::COMMAND_PATH);
        $fullPath = self::COMMAND_PATH.$commandName.'Command.php';
        return Tools::writeFile($fullPath, CommandStubs::commandTemplate($commandName), 'Command');
    }

    /**
     * Generates new class that contains functions that support multiple 
     * console commands.
     *
     * @param string $helperName The name for the new helper class.
     * @return int A value that indicates success, invalid, or failure.
     */
    public static function makeHelper(string $helperName): int {
        Tools::pathExists(self::HELPER_PATH);
        $fullPath = self::HELPER_PATH.$helperName.'.php';
        return Tools::writeFile($fullPath, CommandStubs::helperTemplate($helperName), 'Helper');
    }
}