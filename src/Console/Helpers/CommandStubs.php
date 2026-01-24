<?php
declare(strict_types=1);
namespace Console\Helpers;

/**
 * Contains stubs for command and command helper templates.
 */
class CommandStubs {
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
}