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
            ->addOption('react', null, InputOption::VALUE_NONE, 'React version');
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
        return DevTools::deployComposerToggle();
    }
}
