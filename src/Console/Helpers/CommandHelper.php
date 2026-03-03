<?php
declare(strict_types=1);
namespace Console\Helpers;

use Console\Console;
use Console\FrameworkQuestion;
use Console\Helpers\Tools;
use Core\Lib\Utilities\Str;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

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
     * @param InputInterface $input The Symfony InputInterface object.
     * @param OutputInterface $output The Symfony OutputInterface object.
     * @return int A value that indicates success, invalid, or failure.
     */
    public static function makeCommand(InputInterface $input, OutputInterface $output): int {
        $commandName = $input->getArgument('command-name');
        if(!$commandName) {
            $question = new FrameworkQuestion($input, $output);
            $message = "Enter name for new Command class.";
            $commandName = $question->ask($message);
        }

        self::getInstance()->required()
            ->noSpecialChars()
            ->alpha()
            ->notReservedKeyword()
            ->max(255)
            ->validate($commandName);

        $commandName = Str::ucfirst($commandName);
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