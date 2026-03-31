<?php
namespace Console\Commands;

use Console\ConsoleCommand;
use Symfony\Component\Console\Input\InputArgument;
use Console\Helpers\DBSeeder;

/**
 * Supports operations for the make:factory command.  Use this command to make a new factory.
 * More information can be found <a href="https://chapmancbvcu.github.io/chappy-php-starter/database_seeders#factory-class">here</a>.
 */
class MakeFactoryCommand extends ConsoleCommand {
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
            ->addArgument('factory-name', InputArgument::OPTIONAL, 'Pass the name of the factory class you want to create');
    }

    /**
     * Executes the command
     *
     * @return int A value that indicates success, invalid, or failure.
     */
    protected function handle(): int
    {
        $factoryName = $this->getArgument('factory-name');
        $message = "Enter name for new factory.";
        if($factoryName) {
            DBSeeder::argOptionValidate($factoryName, $message, $this->question(), ['max:50']);
        } else {
            $factoryName = DBSeeder::prompt($message, $this->question(), ['max:50']);
        }
        return DBSeeder::makeFactory($factoryName);
    }
}
