<?php
namespace Console\Commands;

use Console\ConsoleCommand;
use Console\Helpers\Component;
use Console\Helpers\Auth;
use Console\Helpers\React;
use Symfony\Component\Console\Input\InputOption;

/**
 * Generates auth views.
 */
class AuthViewsCommand extends ConsoleCommand {
    /**
     * Configures the command.
     *
     * @return void
     */
    protected function configure(): void
    {
        $this->setName('generate:auth:views')
            ->setDescription('Generates auth views.')
            ->setHelp('php console generate:auth:views')
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
            return React::authComponents();
        }
        
        Auth::makeLoginViewPHP();
        Auth::makeRegisterViewPHP();
        Auth::makeResetPasswordViewPHP();
        Component::makeProfileDetailsComponent();
        
        return self::SUCCESS;
    }
}
