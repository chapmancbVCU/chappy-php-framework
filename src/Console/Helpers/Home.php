<?php
namespace Console\Helpers;

use Console\Console;
use Console\Helpers\Stubs\Home\Controllers;
use Console\Helpers\Stubs\Home\Views;
use Console\Helpers\Tools;

/**
 * Provides ability to generate home controllers and index files.
 */
class Home extends Console {
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
            $path.'HomeController.php',
            Controllers::jsController(),
            'Home controller class'
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
            $path.'HomeController.php',
            Controllers::phpController(),
            'Home controller class'
        );
    }

    /**
     * Generates PHP version of index.php.
     *
     * @return int A value that indicates success, invalid, or failure.
     */
    public static function makeHomeViewPHP(): int {
        $path = self::VIEW_PATH.'home'.DS;
        Tools::pathExists($path);
        return Tools::writeFile(
            $path.'index.php',
            Views::makeIndexPHP(),
            'Home index view'
        );
    }
}