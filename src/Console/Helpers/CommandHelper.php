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
    private const COMMAND_PATH = ROOT.DS.'app'.DS.'Lib'.DS.'Console'.DS.'Commands'.DS;
    private const HELPER_PATH = ROOT.DS.'app'.DS.'Lib'.DS.'Console'.DS.'Helpers'.DS;
    /**
     * Creates template for new command class.
     *
     * @param string $commandName The name of the class.
     * @return string The contents for the new command class.
     */
    public static function commandTemplate(string $commandName): string {
        return <<<PHP
<?php
namespace App\Lib\Console\Commands;
 
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * Undocumented class
 */
class {$commandName}Command extends Command {
    /**
     * Configures the command.
     *
     * @return void
     */
    protected function configure(): void
    {
        \$this->setName('my-command');
    }

    /**
     * Executes the command
     *
     * @param InputInterface \$input The input.
     * @param OutputInterface \$output The output.
     * @return int A value that indicates success, invalid, or failure.
     */
    protected function execute(InputInterface \$input, OutputInterface \$output): int
    {
        //
    }
}
PHP;
    }

    /**
     * Creates new helper class.
     *
     * @param string $helperName The name of the helper class.
     * @return string The contents of the helper class.
     */
    public static function helperTemplate(string $helperName): string {
        return <<<PHP
<?php
namespace App\Lib\Console\Helpers;

use Symfony\Component\Console\Command\Command;

/**
 * 
 */
class {$helperName} {

}
PHP;
    }

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
        return Tools::writeFile($fullPath, self::commandTemplate($commandName), 'Command');
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
        return Tools::writeFile($fullPath, self::helperTemplate($helperName), 'Helper');
    }
}