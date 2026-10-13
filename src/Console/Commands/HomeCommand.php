<?php
namespace Console\Commands;

use Console\ConsoleCommand;
use Console\Helpers\Home;
use Console\Helpers\React;
use Symfony\Component\Console\Input\InputOption;

/**
 * Generates home controllers and views.
 */
class HomeCommand extends ConsoleCommand {
    /**
     * Configures the command.
     *
     * @return void
     */
    protected function configure(): void
    {
        $this->setName('generate:home')
            ->setDescription('Generates home controllers and views.')
            ->setHelp('php console generate:home')
            ->addOption('react', null, InputOption::VALUE_NONE, 'React version');
    }

    /**
     * Executes the command
     *
     * @return int A value that indicates success, invalid, or failure.
     */
    protected function handle(): int
    {
        if($this->getOption('react')) {
            Home::makeControllerJS();
            return React::homeComponent();
        }
        Home::makeControllerPHP();
        return Home::makeHomeViewPHP();
    }
}
