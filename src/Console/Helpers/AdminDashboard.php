<?php
namespace Console\Helpers;

use Console\Console;
use Console\Helpers\Stubs\AdminDashboard\Controllers;

/**
 * Provides ability to generate admin dashboard controllers and view files.
 */
class AdminDashboard extends Console {
    public const CONTROLLER_PATH = CHAPPY_BASE_PATH.DS.'app'.DS.'Controllers'.DS;

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
}