<?php
namespace Console\Helpers;

use Console\Console;
use Console\Helpers\Stubs\AdminDashboard\Controllers;
use Console\Helpers\Stubs\AdminDashboard\Views;

/**
 * Provides ability to generate admin dashboard controllers and view files.
 */
class AdminDashboard extends Console {
    public const CONTROLLER_PATH = CHAPPY_BASE_PATH.DS.'app'.DS.'Controllers'.DS;
    public const VIEW_PATH = ROOT.DS.'resources'.DS.'views'.DS;
    /**
     * Generates PHP version of the controller.
     *
     * @return int A value that indicates success, invalid, or failure.
     */
    public static function makeControllerPHP(): int {
        $path = self::CONTROLLER_PATH;
        Tools::pathExists($path);
        return Tools::writeFile(
            $path.'AdmindashboardController.php',
            Controllers::phpController(),
            'AdmindashboardController class'
        );
    }

    /**
     * Generates JS version of the controller.
     *
     * @return int A value that indicates success, invalid, or failure.
     */
    public static function makeControllerJS(): int {
        $path = self::CONTROLLER_PATH;
        Tools::pathExists($path);
        return Tools::writeFile(
            $path.'AdmindashboardController.php',
            Controllers::jsController(),
            'AdmindashboardController class'
        );
    }

    /**
     * Generates PHP version of attachment details.
     *
     * @return int A value that indicates success, invalid, or failure.
     */
    public static function makeAttachmentDetailsViewPHP(): int {
        $path = self::VIEW_PATH.'admindashboard'.DS;
        Tools::pathExists($path);
        return Tools::writeFile(
            $path.'attachment_details.php',
            Views::makeAttachmentDetailsPHP(),
            'Attachment details view'
        );
    }

    /**
     * Generates PHP version of attachments form.
     *
     * @return int A value that indicates success, invalid, or failure.
     */
    public static function makeAttachmentsFormViewPHP(): int {
        $path = self::VIEW_PATH.'admindashboard'.DS;
        Tools::pathExists($path);
        return Tools::writeFile(
            $path.'attachments_form.php',
            Views::makeAttachmentsFormPHP(),
            'Attachments form view'
        );
    }

    /**
     * Generates PHP version of attachments.
     *
     * @return int A value that indicates success, invalid, or failure.
     */
    public static function makeAttachmentsViewPHP(): int {
        $path = self::VIEW_PATH.'admindashboard'.DS;
        Tools::pathExists($path);
        return Tools::writeFile(
            $path.'attachments.php',
            Views::makeAttachmentsPHP(),
            'Attachments view'
        );
    }

    /**
     * Generates PHP version of details.
     *
     * @return int A value that indicates success, invalid, or failure.
     */
    public static function makeDetailsViewPHP(): int {
        $path = self::VIEW_PATH.'admindashboard'.DS;
        Tools::pathExists($path);
        return Tools::writeFile(
            $path.'details.php',
            Views::makeDetailsPHP(),
            'Details view'
        );
    }

    /**
     * Generates PHP version of edit acl.
     *
     * @return int A value that indicates success, invalid, or failure.
     */
    public static function makeEditAclViewPHP(): int {
        $path = self::VIEW_PATH.'admindashboard'.DS;
        Tools::pathExists($path);
        return Tools::writeFile(
            $path.'edit_acl.php',
            Views::makeEditAclPHP(),
            'Edit ACLs view'
        );
    }

    /**
     * Generates PHP version of edit.
     *
     * @return int A value that indicates success, invalid, or failure.
     */
    public static function makeEditViewPHP(): int {
        $path = self::VIEW_PATH.'admindashboard'.DS;
        Tools::pathExists($path);
        return Tools::writeFile(
            $path.'edit.php',
            Views::makeEditPHP(),
            'Edit view'
        );
    }

    /**
     * Generates PHP version of index.
     *
     * @return int A value that indicates success, invalid, or failure.
     */
    public static function makeIndexViewPHP(): int {
        $path = self::VIEW_PATH.'admindashboard'.DS;
        Tools::pathExists($path);
        return Tools::writeFile(
            $path.'index.php',
            Views::makeIndexPHP(),
            'Index view'
        );
    }

    /**
     * Generates PHP version of manage acls.
     *
     * @return int A value that indicates success, invalid, or failure.
     */
    public static function makeManageAclsViewPHP(): int {
        $path = self::VIEW_PATH.'admindashboard'.DS;
        Tools::pathExists($path);
        return Tools::writeFile(
            $path.'manage_acls.php',
            Views::makeManageAclsPHP(),
            'Manage ACLs view'
        );
    }

    /**
     * Generates PHP version of set account status.
     *
     * @return int A value that indicates success, invalid, or failure.
     */
    public static function makeSetAccountStatusViewPHP(): int {
        $path = self::VIEW_PATH.'admindashboard'.DS;
        Tools::pathExists($path);
        return Tools::writeFile(
            $path.'set_account_status.php',
            Views::makeSetAccountStatusPHP(),
            'Set account status view'
        );
    }

    /**
     * Generates PHP version of set reset password.
     *
     * @return int A value that indicates success, invalid, or failure.
     */
    public static function makeSetResetPasswordViewPHP(): int {
        $path = self::VIEW_PATH.'admindashboard'.DS;
        Tools::pathExists($path);
        return Tools::writeFile(
            $path.'set_reset_password.php',
            Views::makeSetResetPasswordPHP(),
            'Set reset password status view'
        );
    }
}