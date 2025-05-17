<?php
declare(strict_types=1);
namespace Console\Helpers;

use PDO;
use Core\DB;
use Core\Lib\Utilities\Arr;
use Core\Lib\Utilities\Str;
use Core\Lib\Database\Migration;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;

/**
 * Helper class for migration related console commands.
 */
class Migrate {
    /**
     * Drops all migrations.
     *
     * @return int A value that indicates success, invalid, or failure.
     */
    public static function refresh(): int {
        $db = DB::getInstance();
        $driver = $db->getPDO()->getAttribute(PDO::ATTR_DRIVER_NAME);
    
        // ✅ Ensure SQLite foreign key constraints are disabled before dropping tables
        if ($driver === 'sqlite') {
            $db->query("PRAGMA foreign_keys = OFF;");
        }
    
        // ✅ Fetch all tables except SQLite system tables
        if ($driver === 'sqlite') {
            $stmt = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'");
            $tables = $stmt->results();
            $tableCount = count($tables);
        } else {
            $stmt = $db->query("SHOW TABLES");
            $tables = $stmt->results();
            $tableCount = count($tables);
        }
    
        if ($tableCount == 0) {
            Tools::info('Empty database. No tables to drop.', 'debug', 'red');
            return Command::FAILURE;
        }
    
        // ✅ Get all migration files
        $migrations = glob('database' . DS . 'migrations' . DS . '*.php');
    
        // ✅ Reverse loop to drop tables in correct order
        foreach (Arr::reverse($migrations) as $fileName) {
            $klass = Str::replace(['database' . DS . 'migrations' . DS, '.php'], '', $fileName);
            $klassNamespace = 'Database\\Migrations\\' . $klass;
    
            if (class_exists($klassNamespace)) {
                Tools::info("Dropping table from: {$klassNamespace}", 'debug', 'yellow');
                $mig = new $klassNamespace();
                $mig->down(); // Drop table
            } else {
                Tools::info("WARNING: Migration class '{$klassNamespace}' not found!", 'error', 'yellow');
            }
        }
    
        // ✅ Drop the migrations table and rebuild database
        if ($driver === 'sqlite') {
            $db->query("DROP TABLE IF EXISTS migrations;");
            $db->query("VACUUM;"); // Ensures SQLite properly resets database
        } else {
            $db->query("DROP TABLE IF EXISTS migrations;");
        }
    
        Tools::info('All tables have been dropped.', 'success', 'green');
        return Command::SUCCESS;
    }
    

    /**
     * Generates a migration class for creating a new table.
     *
     * @param InputInterface $input 
     * @return int A value that indicates success, invalid, or failure.
     */
    public static function makeMigration(InputInterface $input): int {
        $tableName = Str::lower($input->getArgument('table_name'));
        
        // Generate Migration class
        $fileName = "Migration".time();
        return Tools::writeFile(
            ROOT.DS.'database'.DS.'migrations'.DS.$fileName.'.php',
            self::migrationClass($fileName, $tableName),
            'Migration'
        );
    }

    /**
     * Generates a migration class for updating existing table.
     *
     * @param InputInterface $input 
     * @return int A value that indicates success, invalid, or failure.
     */
    public static function makeUpdateMigration(InputInterface $input): int {
        $tableName = Str::lower($input->getArgument('table_name'));
        
        // Generate Migration class
        $fileName = "Migration".time();
        return Tools::writeFile(
            ROOT.DS.'database'.DS.'migrations'.DS.$fileName.'.php',
            self::migrationUpdateClass($fileName, $tableName),
            'Migration'
        );
    }

    /**
     * Performs migration operation.
     *
     * @return integer A value that indicates success, invalid, or failure.
     */
    public static function migrate(): int {
        
        $db = DB::getInstance();
        
        if ($db->getPDO()->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite') {
            $db->query("PRAGMA foreign_keys=ON;");
        }
        $driver = $db->getPDO()->getAttribute(PDO::ATTR_DRIVER_NAME);
        $previousMigs = [];
        $migrationsRun = [];

        // Check if the migrations table exists
        if ($driver === 'sqlite') {
            // SQLite method to check if table exists
            $migrationTable = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='migrations'")->count();
        } else {
            // MySQL method
            $migrationTable = $db->query("SHOW TABLES LIKE 'migrations'")->count();
        }

        // If the migrations table exists, load previous migrations
        $batch = 1;
        if ($migrationTable > 0) {
            $batch = Migration::getNextBatch();
            $query = $db->query("SELECT migration FROM migrations")->results();
            foreach ($query as $q) {
                $previousMigs[] = $q->migration;
            }
        }

        // Get all migration files
        $migrations = glob('database' . DS . 'migrations' . DS . '*.php');

        foreach ($migrations as $fileName) {
            $klass = Str::replace(['database' . DS . 'migrations' . DS, '.php'], '', $fileName);
            
            if (!Arr::contains($previousMigs, $klass)) {
                $klassNamespace = 'Database\\Migrations\\' . $klass;
                
                if (class_exists($klassNamespace)) {
                    $mig = new $klassNamespace();
                    $mig->up();  // Run migration

                    // Store migration history and value for batch
                    $db->insert('migrations', [
                        'migration' => $klass,
                        'batch' => $batch 
                    ]); 
                    $migrationsRun[] = $klassNamespace;
                } else {
                    Tools::info("WARNING: Migration class '{$klassNamespace}' not found!", 'error', 'red');
                }
            }
        }

        // Output result
        if (sizeof($migrationsRun) == 0) {
            Tools::info('No new migrations to run.', 'debug', 'yellow');
        } else {
            Tools::info('Migrations completed successfully.', 'success', 'green');
        }

        return Command::SUCCESS;
    }

    /**
     * Generates a new Migration class for creating a new table.
     *
     * @param string $fileName The file name for the Migration class.
     * @param string $tableName The name of the table for the migration.
     * @return string The contents of the new Migration class.
     */
    public static function migrationClass(string $fileName, string $tableName): string {
        $tableName = Str::lower($tableName);
        return '<?php
namespace Database\Migrations;
use Core\Lib\Database\Schema;
use Core\Lib\Database\Blueprint;
use Core\Lib\Database\Migration;

/**
 * Migration class for the '.$tableName.' table.
 */
class '.$fileName.' extends Migration {
    /**
     * Performs a migration for a new table.
     *
     * @return void
     */
    public function up(): void {
        Schema::create(\''.$tableName.'\', function (Blueprint $table) {
            $table->id();

        });
    }

    /**
     * Undo a migration task.
     *
     * @return void
     */
    public function down(): void {
        Schema::dropIfExists(\''.$tableName.'\');
    }
}
';
    }

    /**
     * Generates a new Migration class for updating a table.
     *
     * @param string $fileName The file name for the Migration class.
     * @param string $tableName The name of the table for the migration.
     * @return string The contents of the new Migration class.
     */
    public static function migrationUpdateClass(string $fileName, string $tableName): string {
        $tableName = Str::lower($tableName);
        return '<?php
namespace Database\Migrations;
use Core\Lib\Database\Schema;
use Core\Lib\Database\Blueprint;
use Core\Lib\Database\Migration;

/**
 * Migration class for the '.$tableName.' table.
 */
class '.$fileName.' extends Migration {
    /**
     * Performs a migration for updating an existing table.
     *
     * @return void
     */
    public function up(): void {
        Schema::table(\''.$tableName.'\', function (Blueprint $table) {

        });
    }

    /**
     * Undo a migration task.
     *
     * @return void
     */
    public function down(): void {
        Schema::dropIfExists(\''.$tableName.'\');
    }
}
';
    }
}