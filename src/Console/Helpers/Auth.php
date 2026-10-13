<?php
namespace Console\Helpers;

use Console\Console;
use Console\Helpers\Stubs\Auth\Controllers;
use Console\Helpers\Stubs\Auth\Views;

/**
 * Provides ability to generate home controllers and views.
 */
class Auth extends Console {
    public const CONTROLLER_PATH = CHAPPY_BASE_PATH.DS.'app'.DS.'Controllers'.DS;
    public const VIEW_PATH = ROOT.DS.'resources'.DS.'views'.DS;

    /**
     * Generates JS version of the controller.
     *
     * @return int A value that indicates success, invalid, or failure.
     */
    public static function makeControllerJS(): int {
        $path = self::CONTROLLER_PATH;
        Tools::pathExists($path);
        return Tools::writeFile(
            $path.'AuthController.php',
            Controllers::jsController(),
            'Auth controller class'
        );
    }

    /**
     * Generates PHP version of the controller.
     *
     * @return int A value that indicates success, invalid, or failure.
     */
    public static function makeControllerPHP(): int {
        $path = self::CONTROLLER_PATH;
        Tools::pathExists($path);
        return Tools::writeFile(
            $path.'AuthController.php',
            Controllers::phpController(),
            'Auth controller class'
        );
    }

    /**
     * Generates PHP version of login.php.
     *
     * @return int A value that indicates success, invalid, or failure.
     */
    public static function makeLoginViewPHP(): int {
        $path = self::VIEW_PATH.'auth'.DS;
        Tools::pathExists($path);
        return Tools::writeFile(
            $path.'login.php',
            Views::makeLoginPHP(),
            'Login view'
        );
    }

    /**
     * Generates PHP version of register.php.
     *
     * @return int A value that indicates success, invalid, or failure.
     */
    public static function makeRegisterViewPHP(): int {
        $path = self::VIEW_PATH.'auth'.DS;
        Tools::pathExists($path);
        return Tools::writeFile(
            $path.'register.php',
            Views::makeRegisterPHP(),
            'Register view'
        );
    }

    /**
     * Generates PHP version of reset_password.php.
     *
     * @return int A value that indicates success, invalid, or failure.
     */
    public static function makeResetPasswordViewPHP(): int {
        $path = self::VIEW_PATH.'auth'.DS;
        Tools::pathExists($path);
        return Tools::writeFile(
            $path.'reset_password.php',
            Views::makeResetPasswordPHP(),
            'Reset password view'
        );
    }
}