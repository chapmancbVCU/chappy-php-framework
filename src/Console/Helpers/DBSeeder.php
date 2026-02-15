<?php
declare(strict_types=1);
namespace Console\Helpers;

use Core\Lib\Utilities\Str;
use Database\Seeders\DatabaseSeeder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
/**
 * Supports operations related to database seeding.
 */
class DBSeeder {
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
    protected \$modelName = $modelName::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            
        ];
    }
}
PHP;
    }

    /**
     * Creates a new factory class.
     *
     * @param InputInterface $input The Symfony InputInterface object.
     * @return int A value that indicates success, invalid, or failure.
     */
    public static function makeFactory(InputInterface $input): int {
        $factoryName = Str::ucfirst($input->getArgument('factory-name'));

        return Tools::writeFile(
            ROOT.DS.'database'.DS.'factories'.DS.$factoryName.'Factory.php',
            self::factory($factoryName),
            "The {$factoryName} factory "
        );
    }

    /**
     * Creates a class for seeding a database.
     *
     * @param InputInterface $input The Symfony InputInterface object.
     * @return int A value that indicates success, invalid, or failure.
     */
    public static function makeSeeder(InputInterface $input): int {
        $seederName = Str::ucfirst($input->getArgument('seeder-name'));

        return Tools::writeFile(
            ROOT.DS.'database'.DS.'seeders'.DS.$seederName.'TableSeeder.php',
            self::seeder($seederName),
            'Seeder'
        );
    }
    
    /**
     * Runs command for seeding database.
     *
     * @return int A value that indicates success, invalid, or failure.
     */
    public static function seed(): int {
        $seeder = new DatabaseSeeder();
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

use Faker\Factory as Faker;
use Core\Lib\Database\Seeder;

// Import your model
use App\Models\\{$ucSeederName};

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
        \$faker = Faker::create();
        
        // Set number of records to create.
        \$numberOfRecords = 10;
        \$i = 0;
        while(\$i < \$numberOfRecords) {
            \${$lcSeederName} = new {$ucSeederName}();
            

            if(\${$ucSeederName}->save()) {
                \$i++;
            }
        }
        console_info("Seeded {$lcSeederName} table.");
    }
}
PHP;
    }
}