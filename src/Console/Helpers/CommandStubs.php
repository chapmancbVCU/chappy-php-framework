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

use Console\ConsoleCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * Undocumented class
 */
class FooCommand extends ConsoleCommand {
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
     * @return int A value that indicates success, invalid, or failure.
     */
    protected function handle(): int
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