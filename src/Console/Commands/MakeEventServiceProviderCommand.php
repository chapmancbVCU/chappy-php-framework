<?php
namespace Console\Commands;

use Console\Helpers\Events;
use Core\Lib\Utilities\Str;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Generates a new event service provider class by running make:provider.
 * More information can be found <a href="https://chapmancbvcu.github.io/chappy-php-starter/events#make-provider">here</a>.
 */
class MakeEventServiceProviderCommand extends Command
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
            ->addArgument('provider-name', InputArgument::REQUIRED, 'Pass the name for the new event service provider');
    }

    /**
     * Executes the command
     *
     * @param InputInterface $input The input.
     * @param OutputInterface $output The output.
     * @return int A value that indicates success, invalid, or failure.
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $providerName = Str::ucfirst($input->getArgument('provider-name'));
        return Events::makeEventServiceProvider($providerName);
    }
}