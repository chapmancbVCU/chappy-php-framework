<?php
declare(strict_types=1);
namespace Console\Helpers;

use Console\Console;
use Symfony\Component\Console\Command\Command;

/**
 * Collection of functions for bulk generation of views.
 */
class PHPHelper extends Console {
    /**
     * Calls generators for admindashboard views.
     *
     * @return int A value that indicates success, invalid, or failure.
     */
    public static function adminDashboardViews(): int {
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

        return Command::SUCCESS;
    }

    /**
     * Calls generators for auth views.
     *
     * @return int A value that indicates success, invalid, or failure.
     */
    public static function authViews(): int {
        Auth::makeLoginViewPHP();
        Auth::makeRegisterViewPHP();
        Auth::makeResetPasswordViewPHP();
        Component::makeProfileDetailsComponent();
        return Command::SUCCESS;
    }

    /**
     * calls generators for profile views.
     *
     * @return int A value that indicates success, invalid, or failure.
     */
    public static function profileViews(): int {
        Profile::makeIndexPHP();
        Profile::makeEditPHP();
        Profile::makeUpdatePasswordPHP();
        Component::makeProfileDetailsComponent();
        return Command::SUCCESS;
    }
}