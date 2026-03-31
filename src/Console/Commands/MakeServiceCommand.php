<?php
namespace Console\Commands;

use Console\ConsoleCommand;
use Console\Helpers\Services;
use Core\Lib\Utilities\Str;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Generates a new custom service by running make:service.
 * More information can be found <a href="https://chapmancbvcu.github.io/chappy-php-starter/services#user-services">here</a>.
 */
class MakeServiceCommand extends ConsoleCommand {
    /**
     * Configures the command.
     *
     * @return void
     */
    protected function configure(): void
    {
        $this->setName('make:service')
            ->setDescription('Generates a new service class')
            ->setHelp('php console make:mailer <service_name>')
            ->addArgument('service-name', InputArgument::OPTIONAL, 'Pass the name of the new service');
    }

    /**
     * Executes the command
     *
     * @return int A value that indicates success, invalid, or failure.
     */
    protected function handle(): int
    {
        $serviceName = $this->getArgument('service-name');
        $message = "Enter name for new service.";
        if($serviceName) {
            Services::argOptionValidate($serviceName, $message, $this->question(), ['max:50']);
        } else {
            $serviceName = Services::prompt($message, $this->question(), ['max:50']);
        }
        return Services::makeService(Str::ucfirst($serviceName));
    }
}
