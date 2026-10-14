<?php
declare(strict_types=1);
namespace Console\Commands;

use Console\ConsoleCommand;

use Console\Helpers\{
    AdminDashboard,
    Auth,
    React,
    Home,
    Profile
};

/**
 * Generates React.js based experience.
 */
class ReactKitCommand extends ConsoleCommand {
    /**
     * Configures the command.
     *
     * @return void
     */
    protected function configure(): void
    {
        $this->setName('kit:react')
            ->setDescription('Generates PHP based experience.')
            ->setHelp('php console kit:react');
    }

    /**
     * Executes the command
     *
     * @return int A value that indicates success, invalid, or failure.
     */
    protected function handle(): int
    {
        AdminDashboard::makeControllerJS();
        Auth::makeControllerJS();
        Home::makeControllerJS();
        Profile::makeControllerJS();
        React::adminDashboardComponents();
        React::authComponents();
        React::homeComponent();
        React::profileComponents();
        return self::SUCCESS;
    }
}
