<?php
namespace Console\Commands;

use Console\ConsoleCommand;
use Console\Helpers\Events;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Generates a new event service provider class by running make:provider.
 * More information can be found <a href="https://chapmancbvcu.github.io/chappy-php-starter/events#make-provider">here</a>.
 */
class MakeEventServiceProviderCommand extends ConsoleCommand
{
    /**
     * Configures the command.
     *
     * @return void
     */
    protected function configure(): void
    {
        $this->setName('make:provider')
            ->setDescription('Generates a new event service provider')
            ->setHelp('php console make:provider <provider-name>')
            ->addArgument('provider-name', InputArgument::OPTIONAL, 'Pass the name for the new event service provider');
    }

    /**
     * Executes the command
     *
     * @return int A value that indicates success, invalid, or failure.
     */
    protected function handle(): int
    {
        $providerName = $this->getArgument('provider-name');
        $message = "Enter name for event service provider.";

        if($providerName) {
            Events::argOptionValidate($providerName, $message, $this->question(), ['max:50']);
        } else {
            $providerName = Events::prompt($message, $this->question(), ['max:50']);
        }
        return Events::makeEventServiceProvider($providerName);
    }
}