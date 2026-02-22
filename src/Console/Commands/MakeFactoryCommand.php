<?php
namespace Console\Commands;
 
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Console\Helpers\DBSeeder;
use Core\Lib\Utilities\Str;

/**
 * Supports operations for the make:factory command.  Use this command to make a new factory.
 * More information can be found <a href="https://chapmancbvcu.github.io/chappy-php-starter/database_seeders#factory-class">here</a>.
 */
class MakeFactoryCommand extends Command {
    /**
     * Configures the command.
     *
     * @return void
     */
    protected function configure(): void
    {
        $this->setName('make:factory')
            ->setDescription('Generates a new Factory class')
            ->setHelp('php console make:factory ClassName')
            ->addArgument('factory-name', InputArgument::REQUIRED, 'Pass the name of the factory class you want to create');
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
        $factoryName = Str::ucfirst($input->getArgument('factory-name'));
        return DBSeeder::makeFactory($factoryName);
    }
}
