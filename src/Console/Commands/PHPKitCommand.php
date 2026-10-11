<?php
namespace Console\Commands;

use Console\ConsoleCommand;
use Symfony\Component\Console\Input\InputOption;

use Console\Helpers\{
    AdminDashboard,
    Auth,
    Component,
    Home,
    Profile
};

/**
 * Generates auth controller.
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
            ->setHelp('php console kit:php')
            ->addOption('react', null, InputOption::VALUE_NONE, 'React version');
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

        AdminDashboard::makeAttachmentDetailsViewPHP();
        AdminDashboard::makeAttachmentsFormViewPHP();
        AdminDashboard::makeAttachmentsViewPHP();
        AdminDashboard::makeDetailsViewPHP();
        AdminDashboard::makeEditAclViewPHP();
        AdminDashboard::makeEditViewPHP();
        AdminDashboard::makeIndexViewPHP();
        AdminDashboard::makeManageAclsViewPHP();
        AdminDashboard::makeSetAccountStatusViewPHP();
        AdminDashboard::makeSetResetPasswordViewPHP();

        Auth::makeLoginViewPHP();
        Auth::makeRegisterViewPHP();
        Auth::makeResetPasswordViewPHP();

        Home::makeHomeViewPHP();

        Profile::makeIndexPHP();
        Profile::makeEditPHP();
        Profile::makeUpdatePasswordPHP();
        
        Component::makeProfileDetailsComponent();
        return self::SUCCESS;
    }
}
