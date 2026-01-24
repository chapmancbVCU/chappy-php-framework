<?php
declare(strict_types=1);
namespace Console\Helpers;

use Console\Helpers\Tools;
use Core\Lib\Utilities\Str;
use Symfony\Component\Console\Input\InputInterface;

/**
 * Supports commands related to building console commands and associated 
 * helper classes.
 */
class CommandHelper {
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
     * @param InputInterface $input The Symfony InputInterface object.
     * @return int A value that indicates success, invalid, or failure.
     */
    public static function makeCommand(InputInterface $input): int {
        $commandName = Str::ucfirst($input->getArgument('command-name'));
        Tools::pathExists(self::COMMAND_PATH);
        $fullPath = self::COMMAND_PATH.$commandName.'Command.php';
        return Tools::writeFile($fullPath, CommandStubs::commandTemplate($commandName), 'Command');
    }

    /**
     * Generates new class that contains functions that support multiple 
     * console commands.
     *
     * @param InputInterface $input The Symfony InputInterface object.
     * @return int A value that indicates success, invalid, or failure.
     */
    public static function makeHelper(InputInterface $input): int {
        $helperName = Str::ucfirst($input->getArgument('helper-name'));
        Tools::pathExists(self::HELPER_PATH);
        $fullPath = self::HELPER_PATH.$helperName.'.php';
        return Tools::writeFile($fullPath, CommandStubs::helperTemplate($helperName), 'Helper');
    }
}