<?php
declare(strict_types=1);
namespace Console\Helpers;

use Console\Console;

/**
 * Supports commands related to Services.
 */
class Services extends Console {
    /**
     * Path to service classes.
     */
    private const SERVICES_PATH = CHAPPY_BASE_PATH.DS.'app'.DS.'Services'.DS;

    /**
     * Creates new Services class.
     *
     * @param string $serviceName The name for the new service.
     * @return int A value that indicates success, invalid, or failure.
     */
    public static function makeService(string $serviceName): int {
        Tools::pathExists(self::SERVICES_PATH);
        $servicePath = self::SERVICES_PATH . $serviceName . 'Service.php';
        return Tools::writeFile(
            $servicePath, 
            self::servicesTemplate($serviceName), 
            "The service $serviceName"
        );
    }

    /**
     * Template for new Service class.
     *
     * @param string $serviceName The name of the Service class.
     * @return string The template for the new Service class.
     */
    public static function servicesTemplate(string $serviceName): string {
        return <<<PHP
<?php
namespace App\Services;

use App\Models\\{$serviceName};

/**
 * Service that supports the {$serviceName} model.
 */
class {$serviceName}Service {

}
PHP;
    }
}