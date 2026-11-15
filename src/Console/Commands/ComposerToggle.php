<?php
declare(strict_types=1);
namespace Console\Commands;

use Console\ConsoleCommand;
use Console\Helpers\DevTools;
use Console\Helpers\Tools;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Command\Command;
/**
 * Generates composer toggle scripts.
 */
class ComposerToggle extends ConsoleCommand {
    /**
     * Configures the command.
     *
     * @return void
     */
    protected function configure(): void
    {
        $this->setName('dev:composer')
            ->setDescription('Generates tools to allow framework developers to switch between local and remote repo.')
            ->setHelp('php console dev:composer')
            ->addOption('win', null, InputOption::VALUE_NONE, 'Windows .bat version');
    }

    /**
     * Executes the command
     *
     * @return int A value that indicates success, invalid, or failure.
     */
    protected function handle(): int
    {
        if(Tools::isProduction()) {
            console_alert("Cannot deploy this tool in a production environment");
            return Command::SUCCESS;
        }

        $win = $this->getOption('win');
        DevTools::composerFiles();
        if($win) return DevTools::toggleWin();
        return DevTools::toggle();
    }
}
