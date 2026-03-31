<?php
namespace Console\Commands;

use Console\ConsoleCommand;
use Symfony\Component\Console\Input\InputArgument;
use Console\Helpers\DBSeeder;
use Core\Lib\Utilities\Str;
use Symfony\Component\Console\Input\InputOption;

/**
 * Supports operations for the make:seeder command.  Use this command to make a database seeder.
 * More information can be found <a href="https://chapmancbvcu.github.io/chappy-php-starter/database_seeders#seeder-class">here</a>.
 */
class MakeSeederCommand extends ConsoleCommand {
    /**
     * Configures the command.
     *
     * @return void
     */
    protected function configure(): void
    {
        $this->setName('make:seeder')
            ->setDescription('Generates a new Seeder class')
            ->setHelp('php console make:seeder ClassName')
            ->addArgument('seeder-name', InputArgument::OPTIONAL, 'Pass the name of the seeder class you want to create')
            ->addOption('factory', null, InputOption::VALUE_NONE, 'Enter name for a factory');
    }

    /**
     * Executes the command
     *
     * @return int A value that indicates success, invalid, or failure.
     */
    protected function handle(): int
    {
        $seederName = $this->getArgument('seeder-name');
        $factory = $this->getOption('factory');
        $message = "Enter name for new database seeder.";
        if($seederName) {
            DBSeeder::argOptionValidate($seederName, $message, $this->question(), ['max:50']);
        } else {
            $seederName = DBSeeder::prompt($message, $this->question(), ['max:50']);
            $factory = DBSeeder::factoryPrompt($factory, $this->question());
        }
        // dd($factory);
        $seederName = Str::ucfirst($seederName);
        if($factory) {
            DBSeeder::makeFactory($seederName);
        }
        return DBSeeder::makeSeeder($seederName);
    }
}
