<?php
declare(strict_types=1);
namespace Console\Helpers;

use PDO;
use Core\DB;
use Core\Lib\Utilities\Arr;
use Core\Lib\Utilities\Str;
use Core\Lib\Database\Migration;
use Console\Helpers\MigrationStatus;
use Core\Exceptions\Console\ConsoleException;
use Core\Lib\Logging\Logger;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;

/**
 * Helper class for migration related console commands.
 */
class Migrate {
    /**
     * Path to database migration files.
     */
    public const MIGRATIONS_PATH = ROOT.DS.'database'.DS.'migrations'.DS;

    /**
     * Test if a particular batch of migrations exists.
     *
     * @param int $batch The batch value we want to test if it exists.
     * @return bool true if exists, otherwise we return false.
     */
    private static function batchExists(int $batch): bool {
        $db = DB::getInstance();
        $sql = "SELECT * FROM migrations WHERE batch = ? ORDER BY id DESC LIMIT 1";
        if($db->query($sql, ['bind' => $batch])->first() == null) {
            return false;
        }
        return true;
    }

    /**
     * Drops all tables from the database without using down function.
     *
     * @return int A value that indicates success, invalid, or failure.
     */
    public static function dropAllTables(): int {
        $db = DB::getInstance();
        $driver = $db->getPDO()->getAttribute(PDO::ATTR_DRIVER_NAME);
    
        try {
            if($driver === 'sqlite') {
                $tables = $db->query("SELECT name FROM sqlite_master WHERE type='table'")->results();
                foreach($tables as $row) {
                    $table = $row->name;
                    if($table !== 'sqlite_sequence') {
                        $db->query("DROP TABLE IF EXISTS \"$table\"");
                    }
                }
            } else {
                $tables = $db->query("SHOW TABLES")->results();
                foreach($tables as $row) {
                    $table = array_values((array) $row)[0];
                    $db->query("DROP TABLE IF EXISTS `$table`");
                }
            }
            Tools::info("All tables dropped successfully.", Logger::DEBUG);
            return Command::SUCCESS;
        } catch(ConsoleException $e) {
            Tools::info('Error dropping tables: ' . $e->getMessage(), Logger::ERROR);
            return Command::FAILURE;
        }
    }

    /**
     * Generates file/class name for migration.
     *
     * @return string The file/class name of the migration.
     */
    public static function fileName(): string {
        return "Migration".time();
    }

    /**
     * Generates a migration class for creating a new table.
     *
     * @param InputInterface $input The Symfony InputInterface object.
     * @return int A value that indicates success, invalid, or failure.
     */
    public static function makeMigration(InputInterface $input): int {
        $tableName = Str::lower($input->getArgument('table_name'));
        
        // Generate Migration class
        $fileName = self::fileName();
        return Tools::writeFile(
            self::MIGRATIONS_PATH.$fileName.'.php',
            MigrationStubs::migrationClass($fileName, $tableName),
            'Migration'
        );
    }

    /**
     * Generates a migration class for renaming an existing table.
     *
     * @param InputInterface $input The Symfony InputInterface object.
     * @return int A value that indicates success, invalid, or failure.
     */
    public static function makeRenameMigration(InputInterface $input): int {
        $from = Str::lower($input->getArgument('table_name'));
        $to = Str::lower($input->getOption('rename'));

        // Generate Migration class
        $fileName = "Migration".time();
        return Tools::writeFile(
            self::MIGRATIONS_PATH.$fileName.'.php',
            MigrationStubs::migrationRenameClass($fileName, $from, $to),
            'Migration'
        );
    }

    /**
     * Generates a migration class for updating existing table.
     *
     * @param InputInterface $input The Symfony InputInterface object.
     * @return int A value that indicates success, invalid, or failure.
     */
    public static function makeUpdateMigration(InputInterface $input): int {
        $tableName = Str::lower($input->getArgument('table_name'));
        
        // Generate Migration class
        $fileName = "Migration".time();
        return Tools::writeFile(
            self::MIGRATIONS_PATH.$fileName.'.php',
            MigrationStubs::migrationUpdateClass($fileName, $tableName),
            'Migration'
        );
    }

    /**
     * Performs migration operation.
     *
     * @return int A value that indicates success, invalid, or failure.
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
                    Tools::info("WARNING: Migration class '{$klassNamespace}' not found!", Logger::ERROR, Tools::BG_RED);
                }
            }
        }

        // Output result
        if (sizeof($migrationsRun) == 0) {
            Tools::info('No new migrations to run.', Logger::DEBUG, Tools::BG_YELLOW);
        } else {
            Tools::info('Migrations completed.  Check console logging for any warnings.', Logger::INFO);
        }

        return Command::SUCCESS;
    }

    /**
     * Performs refresh operation.
     * 
     * @param bool|int $step The number of individual migrations to roll 
     * back.  When set to false all tables are dropped one by one.
     * @return int A value that indicates success, invalid, or failure.
     */
    public static function refresh(bool|int $step = false): int {
        if($step === true && !is_int($step)) {
            Tools::info("Step must be an integer or set to false", Logger::ERROR, Tools::BG_YELLOW);
            return Command::FAILURE;
        }

        $db = DB::getInstance();
        $driver = $db->getPDO()->getAttribute(PDO::ATTR_DRIVER_NAME);
        $tableCount = self::tableCount();
    
        if(is_int($step) && $step >= $tableCount) {
            Tools::info('The number of steps must not be greater than or equal to the number of tables.', Logger::ERROR, Tools::BG_YELLOW);
            return Command::FAILURE;
        }

        if($tableCount == 0) {
            Tools::info('Empty database. No tables to drop.', Logger::INFO, Tools::BG_RED);
            return Command::FAILURE;
        }
    
        // ✅ Get all migration files
        $migrations = glob('database' . DS . 'migrations' . DS . '*.php');
    
        // ✅ Reverse loop to drop tables in correct order
        foreach (Arr::reverse($migrations) as $fileName) {
            $klass = Str::replace(['database' . DS . 'migrations' . DS, '.php'], '', $fileName);
            $klassNamespace = 'Database\\Migrations\\' . $klass;
    
            if (class_exists($klassNamespace)) {
                $step = self::step($klassNamespace, $step);
                if(is_int($step) && $step <= 0) return Command::SUCCESS; 
            } else {
                Tools::info("WARNING: Migration class '{$klassNamespace}' not found!", Logger::ERROR, Tools::BG_YELLOW);
            }
        }
    
        // ✅ Drop the migrations table and rebuild database
        if ($driver === 'sqlite') {
            $db->query("DROP TABLE IF EXISTS migrations;");
            $db->query("VACUUM;"); // Ensures SQLite properly resets database
        } else {
            $db->query("DROP TABLE IF EXISTS migrations;");
        }
    
        Tools::info('All tables have been dropped.', Logger::DEBUG);
        return Command::SUCCESS;
    }

    /**
     * Performs roll back operation
     *
     * @param string|bool|int $batch The batch number.  If false we assume 
     * that we want to roll back latest batch of migrations.
     * @return int A value that indicates success, invalid, or failure.
     */
    public static function rollback(string|int|bool $batch = false): int {
        // Fail immediately if no batch value is set.
        if($batch === '') {
            Tools::info('Please enter value for batch to roll back', Logger::ERROR, Tools::BG_RED);
            return Command::FAILURE;
        }

        if(!$batch) {
            $batch = DB::getInstance()->query('SELECT batch FROM migrations ORDER BY id DESC LIMIT 1')->first()->batch;
        } else if(!self::batchExists((int)$batch)){
            Tools::info("The batch value of $batch does not exist", Logger::ERROR, Tools::BG_RED);
            return Command::FAILURE;
        }

        if(self::tableCount() == 0) {
            Tools::info('Empty database. No tables to drop.', Logger::DEBUG, Tools::BG_RED);
            return Command::FAILURE;
        }

        // Get all migration files and records with batch matching number
        $migrations = glob('database' . DS . 'migrations' . DS . '*.php');
        $db = DB::getInstance();
        $existingMigrations = $db->query("SELECT * FROM migrations WHERE batch = ?", ['bind' => $batch])->results();
        
        // Perform roll back with loop in reverse to avoid dropping migrations table.
        foreach(Arr::reverse($migrations) as $fileName) {
            $className = Str::replace(['database' . DS . 'migrations' . DS, '.php'], '', $fileName);
            foreach(Arr::reverse($existingMigrations) as $migration) {
                $classNamespace = 'Database\\Migrations\\' . $className;
                if($migration->migration === $className && class_exists($classNamespace) && $migration->id != 1) {
                    $mig = new $classNamespace();
                    $mig->down();
                    $db->delete('migrations', $migration->id);
                    break;
                }
            }
        }
        
        Tools::info("Completed roll back for batch $batch");
        return Command::SUCCESS;
    }

    /**
     * Perform step roll back.
     *
     * @param string|int $step
     * @return int A value that indicates success, invalid, or failure.
     */
    public static function rollbackStep(string|int $step): int {
        if($step === '') {
            Tools::info('Please enter number of steps to roll back', Logger::ERROR, Tools::BG_RED);
            return Command::FAILURE;
        }
        return self::refresh((int)$step);
    }

    /**
     * Reports migration status.
     *
     * @return int A value that indicates success, invalid, or failure.
     */
    public static function status(): int {
        $migrationFiles = glob('database' . DS . 'migrations' . DS . '*.php');
        if(sizeof($migrationFiles) == 0) {
            Tools::info("There are no existing migrations", Logger::ERROR, Tools::BG_YELLOW);
        }

        $migrationStatus = [];

        $db = DB::getInstance();
        $existingMigrations = $db->query("SELECT * FROM migrations")->results();
        $found = false;
        foreach ($migrationFiles as $migrationFile) {
            $className = basename($migrationFile, '.php');

            $found = false;
            foreach ($existingMigrations as $existingMigration) {
                if ($className === $existingMigration->migration) {
                    $migrationStatus[] = new MigrationStatus(
                        (string)$existingMigration->batch,
                        $existingMigration->migration,
                        true
                    );
                    $found = true;
                    break;
                }
            }

            if (!$found) {
                $migrationStatus[] = new MigrationStatus("", $className, false);
            }
        }

        Tools::info("Name ................................. Batch / Status", Logger::INFO, Tools::BG_BLUE);
        foreach($migrationStatus as $status) {
            $name = $status->getName();
            $batch = $status->getBatch();
            $state = $status->getStatus();
            if($status->getStatus() == 'Ran') {
                Tools::info("$name: ........................ [$batch] $state");
            } else {
                Tools::info("$name ......................... Pending", Logger::INFO, Tools::BG_YELLOW);
            }
        }
        return Command::SUCCESS;
    }

    /**
     * Drops table one at a time.
     *
     * @param string $klassNamespace The name of the migration class.
     * @param bool|int $step The number of individual migrations to roll 
     * back.  When set to false all tables are dropped one by one.
     * @return bool|int $step The number of remaining steps to perform with 
     * respect to rolling back migrations.  Boolean value of false is returned 
     * when no number of steps is provided.
     */
    private static function step(string $klassNamespace, bool|int $step = false): bool|int {
        $db = DB::getInstance();
        Tools::info("Dropping table from: {$klassNamespace}", Logger::DEBUG, Tools::BG_YELLOW);
        $mig = new $klassNamespace();
        if(!$step && !is_int($step)) {
            $mig->down();
        } else {
            $mig->down();
            $latest = $db->query("SELECT * FROM migrations ORDER BY id DESC LIMIT 1")->first();
            $db->delete('migrations', $latest->id);
            $step--;
        }
        return $step;
    }

    /**
     * Determines number of tables in database before performing migration 
     * operations.
     *
     * @return int The number of tables in the database.
     */
    private static function tableCount(): int {
        $db = DB::getInstance();
        $driver = $db->getPDO()->getAttribute(PDO::ATTR_DRIVER_NAME);

        // Fetch all tables except SQLite system tables
        if ($driver === 'sqlite') {
            //Ensure SQLite foreign key constraints are disabled before dropping tables
            $db->query("PRAGMA foreign_keys = OFF;");
            $stmt = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'");
        } else {
            $stmt = $db->query("SHOW TABLES");
        }
        return count($stmt->results());
    }
}