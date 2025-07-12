<?php
declare(strict_types=1);
namespace Console\Helpers;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;

/**
 * Supports commands related to Services.
 */
class Services {
    protected static string $servicesPath = CHAPPY_BASE_PATH.DS.'app'.DS.'Services'.DS;

    /**
     * Creates new Services class.
     *
     * @param InputInterface $inputThe input.
     * @return int A value that indicates success, invalid, or failure.
     */
    public static function makeService(InputInterface $input): int {
        return Command::SUCCESS;
    }

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

    /**
     * Template for new Service class.
     *
     * @param string $serviceName The name of the Service class.
     * @return string The template for the new Service class.
     */
    public static function servicesTemplate(string $serviceName): string {
        return '';
    }
}