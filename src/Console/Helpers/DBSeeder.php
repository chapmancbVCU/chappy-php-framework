<?php
declare(strict_types=1);
namespace Console\Helpers;

use Console\Console;
use Core\Lib\Utilities\Str;
use Database\Seeders\DatabaseSeeder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Supports operations related to database seeding.
 */
class DBSeeder extends Console {
    /**
     * Path to all user created factory classes.
     */
    private const FACTORY_PATH = ROOT.DS.'database'.DS.'factories'.DS;

    /**
     * Namespace for seeder classes to be accessible outside this class.
     */
    private const SEEDER_NAMESPACE = "Database\\Seeders\\";
    
    /**
     * Path to all seeder classes.
     */
    private const SEEDER_PATH = ROOT.DS.'database'.DS.'seeders'.DS;

    /**
     * Returns contents for new factory class.
     *
     * @param string $modelName The name of the model the new factory will
     * target.
     * @return string The contents of the new factory class.
     */
    public static function factory(string $modelName): string {
        return <<<PHP
<?php
namespace Database\Factories;

use App\Models\\{$modelName};
use Core\Lib\Database\Factory;

class {$modelName}Factory extends Factory {
    protected string \$modelName = $modelName::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected function definition(): array
    {
        return [
            
        ];
    }
}
PHP;
    }

    /**
     * Asks user if they want to create factory with DB seeder if seeder-name argument is not set.
     *
     * @param mixed $factory The --factory flag.
     * @param InputInterface $input The Symfony InputInterface object.
     * @param OutputInterface $output The Symfony OutputInterface object.
     * @return mixed Original value if --factory flag is provided.  If user confirms the string 
     * "factory" is returned which matches value of --factory flag if provided.  When user answers 
     * now to question then null is returned.
     */
    public static function factoryPrompt(
        mixed $factory, 
        InputInterface $input, 
        OutputInterface $output
    ): mixed {
        if($factory) return $factory;
        $message = "Do you want to create a factory for this seeder? (y/n)";
        if(self::confirm($message, $input, $output)) return "factory";
        return null;
    }

    /**
     * Creates a new factory class.
     *
     * @param string $factoryName The name for the new factory class.
     * @return int A value that indicates success, invalid, or failure.
     */
    public static function makeFactory(string $factoryName): int {
        Tools::pathExists(self::FACTORY_PATH);
        $factoryName = Str::ucfirst($factoryName);
        return Tools::writeFile(
            self::FACTORY_PATH.$factoryName.'Factory.php',
            self::factory($factoryName),
            "The {$factoryName}Factory class"
        );
    }

    /**
     * Creates a class for seeding a database.
     *
     * @param string $seederName The name for the new seeder class.
     * @return int A value that indicates success, invalid, or failure.
     */
    public static function makeSeeder(string $seederName): int {
        Tools::pathExists(self::SEEDER_PATH);

        return Tools::writeFile(
            self::SEEDER_PATH.$seederName.'TableSeeder.php',
            self::seeder($seederName),
            "The {$seederName}TableSeeder class"
        );
    }
    
    /**
     * Runs command for seeding database.
     *
     * @param InputInterface $input The Symfony InputInterface object.
     * @return int A value that indicates success, invalid, or failure.
     */
    public static function seed(InputInterface $input): int {
        $seederOption = $input->getOption('seeder');
        $classname = self::SEEDER_NAMESPACE.$seederOption;
        $seeder = ($seederOption) ? new $classname() : new DatabaseSeeder();
        $seeder->run();
        console_info('Database seeding complete!.  If you see only this message then uncomment your seeders.');
        return Command::SUCCESS;
    }

    /**
     * Returns a string containing contents of a new Seeder class.
     *
     * @param string $seederName The name of the Seeder class.
     * @return string The contents of the seeder class.
     */
    public static function seeder(string $seederName): string {
        $lcSeederName = Str::lcfirst($seederName);
        $ucSeederName = Str::ucfirst($seederName);
        return <<<PHP
<?php
namespace Database\Seeders;


use Core\Lib\Database\Seeder;
use Database\Factories\\{$ucSeederName}Factory;

/**
 * Seeder for {$lcSeederName} table.
 * 
 * @return void
 */
class {$ucSeederName}TableSeeder extends Seeder {
    /**
     * Runs the database seeder
     *
     * @return void
     */
    public function run(): void {
        
    }
}
PHP;
    }
}