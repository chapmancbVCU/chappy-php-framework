<?php
declare(strict_types=1);
namespace Console\Helpers;

use Core\Lib\Utilities\Str;
use Symfony\Component\Console\Input\InputInterface;

/**
 * Supports commands related to Services.
 */
class Services {
    private const SERVICES_PATH = CHAPPY_BASE_PATH.DS.'app'.DS.'Services'.DS;

    /**
     * Creates new Services class.
     *
     * @param InputInterface $input The Symfony InputInterface object.
     * @return int A value that indicates success, invalid, or failure.
     */
    public static function makeService(InputInterface $input): int {
        Tools::pathExists(self::SERVICES_PATH);
        $serviceName = Str::ucfirst($input->getArgument('service-name'));
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