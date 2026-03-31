<?php
namespace Console\Commands;

use Console\ConsoleCommand;
use Console\Helpers\DBSeeder;
use Symfony\Component\Console\Input\InputOption;

/**
 * Runs the command for seeding database with random data.
 * More information can be found <a href="https://chapmancbvcu.github.io/chappy-php-starter/database_seeders#running-seeder">here</a>.
 */
class SeedCommand extends ConsoleCommand {
    /**
     * Configures the command.
     *
     * @return void
     */
    protected function configure(): void
    {
        $this->setName('seed:run')
            ->setDescription("Runs command to seed database")
            ->addOption('seeder', null, InputOption::VALUE_REQUIRED, 'Specify name of a seeder class', false)
            ->setHelp('run seed:run');
    }

    /**
     * Executes the command
     *
     * @return int A value that indicates success, invalid, or failure.
     */
    protected function handle(): int
    {
        return DBSeeder::seed($this->input, $this->question());
    }
}
