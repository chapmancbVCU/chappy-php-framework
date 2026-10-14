<?php
declare(strict_types=1);
namespace Console\Commands;

use Console\ConsoleCommand;

use Console\Helpers\{
    AdminDashboard,
    Auth,
    Component,
    Home,
    PHPHelper,
    Profile
};

/**
 * Generates PHP based experience.
 */
class PHPKitCommand extends ConsoleCommand {
    /**
     * Configures the command.
     *
     * @return void
     */
    protected function configure(): void
    {
        $this->setName('kit:php')
            ->setDescription('Generates PHP based experience.')
            ->setHelp('php console kit:php');
    }

    /**
     * Executes the command
     *
     * @return int A value that indicates success, invalid, or failure.
     */
    protected function handle(): int
    {
        AdminDashboard::makeControllerPHP();
        Auth::makeControllerPHP();
        Home::makeControllerPHP();
        Profile::makeControllerPHP();
        PHPHelper::adminDashboardViews();
        PHPHelper::authViews();
        Home::makeHomeViewPHP();
        PHPHelper::profileViews();
        Component::makeProfileDetailsComponent();
        return self::SUCCESS;
    }
}
