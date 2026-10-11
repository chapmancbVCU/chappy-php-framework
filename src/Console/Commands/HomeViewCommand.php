<?php
namespace Console\Commands;

use Console\ConsoleCommand;
use Console\Helpers\Home;
use Console\Helpers\React;
use Console\Helpers\Stubs\Home\Views;
use Symfony\Component\Console\Input\InputOption;

/**
 * Generates home controller.
 */
class HomeViewCommand extends ConsoleCommand {
    /**
     * Configures the command.
     *
     * @return void
     */
    protected function configure(): void
    {
        $this->setName('generate:home:view')
            ->setDescription('Generates home index view.')
            ->setHelp('php console generate:home:view')
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
            return React::homeComponent();
        }
        return Home::makeHomeViewPHP();
    }
}
