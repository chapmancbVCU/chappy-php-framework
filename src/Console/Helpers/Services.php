<?php
declare(strict_types=1);
namespace Console\Helpers;

class Services {
    protected static string $servicesPath = CHAPPY_BASE_PATH.DS.'app'.DS.'Services'.DS;

    /**
     * Creates directory for services if it does not exist.
     *
     * @return void
     */
    public static function servicesPath(): void {
        if(!is_dir(self::$servicesPath)) {
            mkdir(self::$servicesPath, 0755, true);
        }
    }
}