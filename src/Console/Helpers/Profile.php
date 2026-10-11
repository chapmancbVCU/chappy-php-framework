<?php
declare(strict_types=1);
namespace Console\Helpers;

use Console\Console;
use Console\Helpers\Stubs\Profile\Controllers;
use Console\Helpers\Stubs\Profile\Views;

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

    /**
     * Generates PHP version of edit.php.
     *
     * @return int A value that indicates success, invalid, or failure.
     */
    public static function makeEditPHP(): int {
        $path = self::VIEW_PATH.'profile'.DS;
        Tools::pathExists($path);
        return Tools::writeFile(
            $path.'index.php',
            Views::makeEditPHP(),
            'Profile edit view'
        );
    }

    /**
     * Generates PHP version of index.php.
     *
     * @return int A value that indicates success, invalid, or failure.
     */
    public static function makeIndexPHP(): int {
        $path = self::VIEW_PATH.'profile'.DS;
        Tools::pathExists($path);
        return Tools::writeFile(
            $path.'index.php',
            Views::makeIndexPHP(),
            'Profile index view'
        );
    }
}