<?php
namespace Console\Commands;

use Console\Helpers\Services;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Generates a new custom service by running make:service.
 * More information can be found <a href="https://chapmancbvcu.github.io/chappy-php-starter/services#user-services">here</a>.
 */
class MakeServiceCommand extends Command {
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
            ->addArgument('service-name', InputArgument::REQUIRED, 'Pass the name of the new service');
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
        return Services::makeService($input);
    }
}
