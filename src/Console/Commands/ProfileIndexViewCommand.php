<?php
namespace Console\Commands;

use Console\ConsoleCommand;
use Console\Helpers\Profile;
use Console\Helpers\React;
use Symfony\Component\Console\Input\InputOption;

/**
 * Generates home index view.
 */
class ProfileIndexViewCommand extends ConsoleCommand {
    /**
     * Configures the command.
     *
     * @return void
     */
    protected function configure(): void
    {
        $this->setName('generate:profile:index')
            ->setDescription('Generates profile index view.')
            ->setHelp('php console generate:profile:index')
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
        return Profile::makeIndexPHP();
    }
}
