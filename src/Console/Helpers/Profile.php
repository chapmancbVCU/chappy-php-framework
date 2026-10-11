<?php
declare(strict_types=1);
namespace Console\Helpers;

use Console\Console;
use Console\Helpers\Stubs\Profile\Controllers;

/**
 * Provides ability to generate profile controllers and view files.
 */
class Profile extends Console {
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
            $path.'ProfileController.php',
            Controllers::jsController(),
            'Profile'
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
            $path.'ProfileController.php',
            Controllers::phpController(),
            'Profile controller class'
        );
    }
}