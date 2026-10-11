<?php
namespace Console\Helpers;

use Console\Console;
use Console\Helpers\Stubs\Auth\Controllers;

class Auth extends Console {
    public const CONTROLLER_PATH = CHAPPY_BASE_PATH.DS.'app'.DS.'Controllers'.DS;
    public const VIEW_PATH = ROOT.DS.'resources'.DS.'views'.DS;

    /**
     * Generates js version of the controller.
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
     * Generates js version of the controller.
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
}