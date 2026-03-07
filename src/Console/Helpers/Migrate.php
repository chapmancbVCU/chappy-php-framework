<?php
declare(strict_types=1);
namespace Console\Helpers;

use Console\Console;
use Console\ConsoleLogger;
use Console\FrameworkQuestion;
use PDO;
use Core\DB;
use Core\Lib\Utilities\Arr;
use Core\Lib\Utilities\Str;
use Core\Lib\Database\Migration;
use Console\Helpers\MigrationStatus;
use Core\Lib\Logging\Logger;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Helper class for migration related console commands.
 */
class Migrate extends Console {
    /**
     * Path to database migration files.
     */
    public const MIGRATIONS_PATH = ROOT.DS.'database'.DS.'migrations'.DS;

    /**
     * The message to present to user when name of migration is being asked.
     */
    public const MIGRATION_PROMPT = "Enter name for new migration.";

    /**
     * Generates new migration class if table-name argument is provided.  If rename or update 
     * flags are set then appropriate migration class is created.
     *
     * @param string $migrationName The name of the table for the new migration class.
     * @param mixed $renameOption Value/state of rename flag.
     * @param mixed $renameOption Value/state of update flag.
     * @param InputInterface $input The Symfony InputInterface object.
     * @param OutputInterface $output The Symfony OutputInterface object.
     * @return int A value that indicates success, invalid, or failure.
     */
    public static function contents(
        string $migrationName, 
        mixed $renameOption, 
        mixed $updateOption, 
        InputInterface $input, 
        OutputInterface $output
        ): int {

        if($renameOption) {
            $renameOption = self::validateRenameOption($renameOption, $migrationName, $input, $output);
            return Migrate::makeRenameMigration($migrationName, $renameOption);
        }
            
        else if($updateOption) return Migrate::makeUpdateMigration($migrationName);
        else return Migrate::makeMigration($migrationName);
    }
    
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
            console_info("All tables dropped successfully.");
            return Command::SUCCESS;
        } catch(\Exception $e) {
            console_error('Error dropping tables: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Ask user to confirm if they want to drop all tables.
     *
     * @param InputInterface $input The Symfony InputInterface object.
     * @param OutputInterface $output The Symfony OutputInterface object.
     * @return mixed The user's response.
     */
    public static function confirmDropAllTables(InputInterface $input, OutputInterface $output): mixed {
        $question = new FrameworkQuestion($input, $output);
        $message = "Are you sure you want to drop all tables? (y/n)";
        return $question->confirm($message);
    }

    /**
     * Generates time stamp for migrations in following format: yyyymmddhhmmss.
     *
     * @return string The migration timestamp.
     */
    public static function fileNameTime(): string {
        return date('YmdHis');
    }

    /**
     * Generates migration for acl table.
     *
     * @return int A value that indicates success, invalid, or failure.
     */
    public static function generateACLTableMigration(): int {
        $path = self::MIGRATIONS_PATH."MDT20240808232014CreateAclTable.php";
        return Tools::writeFile(
            $path,
            MigrationStubs::aclTableTemplate(),
            'ACL table migration'
        );
    }

    /**
     * Generates all migrations.
     *
     * @return int Command::SUCCESS
     */
    public static function generateAllMigrations(): int {
        self::generateMigrationsTableMigration();
        self::generateUsersTableMigration();
        self::generateACLTableMigration();
        self::generateProfileImagesTableMigration();
        self::generateUserSessionsTableMigration();
        self::generateEmailAttachmentsTableMigration();
        return Command::SUCCESS;
    }
    /**
     * Generates migration for email_attachments table.
     *
     * @return int A value that indicates success, invalid, or failure.
     */
    public static function generateEmailAttachmentsTableMigration(): int {
        $path = self::MIGRATIONS_PATH."MDT20250621195401CreateEmailAttachmentsTable.php";
        return Tools::writeFile(
            $path,
            MigrationStubs::emailAttachmentsTableTemplate(),
            'Email Attachments table migration'
        );
    }

    /**
     * Generates migration by name.
     *
     * @param InputInterface $input The Symfony InputInterface object.
     * @return int Command::SUCCESS
     */
    public static function generateMigrationByName(InputInterface $input): int {
        if($input->getOption('migrations')) self::generateMigrationsTableMigration();
        if($input->getOption('users')) self::generateUsersTableMigration();
        if($input->getOption('acl')) self::generateACLTableMigration();
        if($input->getOption('profile_images')) self::generateProfileImagesTableMigration();
        if($input->getOption('user_sessions')) self::generateUserSessionsTableMigration();
        if($input->getOption('email_attachments')) self::generateEmailAttachmentsTableMigration();
        return Command::SUCCESS;
    }

    /**
     * Generates migration for migration table.
     *
     * @return int A value that indicates success, invalid, or failure.
     */
    public static function generateMigrationsTableMigration(): int {
        $path = self::MIGRATIONS_PATH."MDT20240805010123CreateMigrationTable.php";
        return Tools::writeFile(
            $path,
            MigrationStubs::migrationTableTemplate(),
            'Migrations table migration'
        );
    }

    /**
     * Generates migration for profile_images table.
     *
     * @return int A value that indicates success, invalid, or failure.
     */
    public static function generateProfileImagesTableMigration(): int {
        $path = self::MIGRATIONS_PATH."MDT20240821210722CreateProfileImagesTable.php";
        return Tools::writeFile(
            $path,
            MigrationStubs::profileImagesTableTemplate(),
            'Profile Images table migration'
        );
    }

    /**
     * Generates migration for user_sessions table.
     *
     * @return int A value that indicates success, invalid, or failure.
     */
    public static function generateUserSessionsTableMigration(): int {
        $path = self::MIGRATIONS_PATH."MDT20241118175443CreateUserSessionsTable.php";
        return Tools::writeFile(
            $path,
            MigrationStubs::userSessionsTableTemplate(),
            'User Sessions table migration'
        );
    }

    /**
     * Generates migration for users table.
     *
     * @return int A value that indicates success, invalid, or failure.
     */
    public static function generateUsersTableMigration(): int {
        $path = self::MIGRATIONS_PATH."MDT20240805010157CreateUsersTable.php";
        return Tools::writeFile(
            $path,
            MigrationStubs::usersTableTemplate(),
            'Users table migration'
        );
    }

    /**
     * Determines if both rename and update flags are set.  If they are both 
     * set then a message is displayed and true is returned.
     *
     * @param mixed $renameOption Value/state of rename flag.
     * @param mixed $renameOption Value/state of update flag.
     * @return bool True if both flags are set.  Otherwise, we return false.
     */
    public static function isBothFlagsSet($renameOption, $updateOption): bool {
        if($updateOption && $renameOption) {
            console_warning('Cannot accept update and rename options at the same time.');
            return true;
        }

        return false;
    }
    
    /**
     * Generates a migration class for creating a new table.
     *
     * @param string $migrationName The name of the table the new migration 
     * will target.
     * @return int A value that indicates success, invalid, or failure.
     */
    public static function makeMigration(string $migrationName): int {
        $tableName = Str::lower($migrationName);
        
        // Generate Migration class
        $fileName = "MDT".self::fileNameTime()."Create".Str::ucfirst($tableName)."Table";
        return Tools::writeFile(
            self::MIGRATIONS_PATH.$fileName.'.php',
            MigrationStubs::migrationClass($fileName, $tableName),
            'Migration'
        );
    }

    /**
     * Generates a migration class for renaming an existing table.
     *
     * @param string $migrationName The name of the table the new migration 
     * will target.
     * @param mixed $renameOption Value/state of rename flag.
     * @return int A value that indicates success, invalid, or failure.
     */
    public static function makeRenameMigration(string $migrationName, mixed $renameOption): int {
        $from = Str::lower($migrationName);
        $to = Str::lower($renameOption);
        $fileName = "MDT".self::fileNameTime()."Rename".Str::ucfirst($from)."TableTo".Str::ucfirst($to);
        return Tools::writeFile(
            self::MIGRATIONS_PATH.$fileName.'.php',
            MigrationStubs::migrationRenameClass($fileName, $from, $to),
            'Migration'
        );
    }

    /**
     * Generates a migration class for updating existing table.
     *
     * @param @param string $migrationName The name of the table the new migration 
     * will target.
     * @return int A value that indicates success, invalid, or failure.
     */
    public static function makeUpdateMigration(string $migrationName): int {
        $tableName = Str::lower($migrationName);
        
        // Generate Migration class
        $fileName = "MDT".self::fileNameTime()."Update".Str::ucfirst($migrationName)."Table";
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
                    console_error("Migration class '{$klassNamespace}' not found!");
                }
            }
        }

        // Output result
        if (sizeof($migrationsRun) == 0) {
            console_notice('No new migrations to run.');
        } else {
            console_info('Migrations completed.  Check console logging for any warnings.');
        }

        return Command::SUCCESS;
    }

    /**
     * Handles question for which table a new migration will target.
     *
     * @param InputInterface $input The Symfony InputInterface object.
     * @param OutputInterface $output The Symfony OutputInterface object.
     * @return string The name of the table the new migration will target.
     */
    public static function migrationNamePrompt(InputInterface $input, OutputInterface $output): string {        
        return self::prompt(self::MIGRATION_PROMPT, $input, $output, 'table-name');
    }

    /**
     * Prompts user for input when no argument and no options are set.
     *
     * @param InputInterface $input The Symfony InputInterface object.
     * @param string $migrationName Name of migration to be created or 
     * renamed to.
     * @param OutputInterface $output The Symfony OutputInterface object.
     * @return int A value that indicates success, invalid, or failure.
     */
    public static function migrationTypePrompt(
        InputInterface $input, 
        string $migrationName, 
        OutputInterface $output
    ): int {
        $choices = ['New Table (default)', 'Rename', 'Update'];
        $response = self::choice(self::MIGRATION_PROMPT, $choices, $input, $output, $choices[0]);
        
        if($response == 'New Table (default)') return self::makeMigration($migrationName);
        if($response == 'Rename') return self::renameChoice($migrationName, $input, $output);
        if($response == 'Update') return self::makeUpdateMigration($migrationName);
        return Command::FAILURE;
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
            console_warning("Step must be an integer or set to false");
            return Command::FAILURE;
        }

        $db = DB::getInstance();
        $driver = $db->getPDO()->getAttribute(PDO::ATTR_DRIVER_NAME);
        $tableCount = self::tableCount();
    
        if(is_int($step) && $step >= $tableCount) {
            console_warning('The number of steps must not be greater than or equal to the number of tables.');
            return Command::FAILURE;
        }

        if($tableCount == 0) {
            console_notice('Empty database. No tables to drop.');
            return Command::SUCCESS;
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
                console_error("Migration class '{$klassNamespace}' not found!");
            }
        }
    
        // ✅ Drop the migrations table and rebuild database
        if ($driver === 'sqlite') {
            $db->query("DROP TABLE IF EXISTS migrations;");
            $db->query("VACUUM;"); // Ensures SQLite properly resets database
        } else {
            $db->query("DROP TABLE IF EXISTS migrations;");
        }
    
        console_info('All tables have been dropped.');
        return Command::SUCCESS;
    }

    /**
     * Prompts user to enter name for table to be renamed.  Used when 
     * user responds with the choice to rename.
     *
     * @param string $migrationName The new name for the table to be renamed.
     * @param InputInterface $input The Symfony InputInterface object.
     * @param OutputInterface $output The Symfony OutputInterface object.
     * @return int A value that indicates success, invalid, or failure.
     */
    private static function renameChoice(string $migrationName, InputInterface $input, OutputInterface $output): int {
        $message = "Provide name for original table";
        $response = self::prompt($message, $input, $output, 'original-table', 50, $migrationName);
        return self::makeRenameMigration($response, $migrationName);
    }

    /**
     * Prompts user to enter name of table to be updated.  Used when user 
     * provides name of controller as an argument.
     *
     * @param InputInterface $input The Symfony InputInterface object.
     * @param OutputInterface $output The Symfony OutputInterface object.
     * @param mixed $renameOption Value/state of rename flag.
     * @return int A value that indicates success, invalid, or failure.
     */
    public static function renamePrompt(InputInterface $input, OutputInterface $output, mixed $renameOption): int {
        $message = "Enter name for original table";
        $response = self::prompt($message, $input, $output, 'original-table');
        $renameOption = self::validateRenameOption($renameOption, $response, $input, $output);  
        return self::makeRenameMigration($response, $renameOption);
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
            console_error('Please enter value for batch to roll back');
            return Command::FAILURE;
        }

        if(!$batch) {
            $batch = DB::getInstance()->query('SELECT batch FROM migrations ORDER BY id DESC LIMIT 1')->first()->batch;
        } else if(!self::batchExists((int)$batch)){
            console_warning("The batch value of $batch does not exist");
            return Command::FAILURE;
        }

        if(self::tableCount() == 0) {
            console_notice('Empty database. No tables to drop.');
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
        
        console_info("Completed roll back for batch $batch");
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
            console_warning('Please enter number of steps to roll back');
            return Command::FAILURE;
        }
        return self::refresh((int)$step);
    }

    /**
     * Generates an array containing values for rename and update flags.
     *
     * @param InputInterface $input The Symfony InputInterface object.
     * @return array The contents of the rename and update flags.
     */
    public static function setFlags(InputInterface $input): array {
        return [
            $renameOption = $input->getOption('rename'),
            $updateOption = $input->getOption('update')
        ];
    }

    /**
     * Reports migration status.
     *
     * @return int A value that indicates success, invalid, or failure.
     */
    public static function status(): int {
        $migrationFiles = glob('database' . DS . 'migrations' . DS . '*.php');
        if(sizeof($migrationFiles) == 0) {
            console_notice("There are no existing migrations");
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

        ConsoleLogger::log("Name ................................. Batch / Status", Logger::INFO, ConsoleLogger::BG_BLUE);
        foreach($migrationStatus as $status) {
            $name = $status->getName();
            $batch = $status->getBatch();
            $state = $status->getStatus();
            if($status->getStatus() == 'Ran') {
                console_info("$name: ........................ [$batch] $state");
            } else {
                console_notice("$name ......................... Pending");
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
        console_debug("Dropping table from: {$klassNamespace}");
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

    /**
     * Validate rename option value.  If validation fails the user is asked to 
     * resolve the issue.
     *
     * Validates the following conditions:
     * 1) required
     * 2) noSpecialChars
     * 3) alpha
     * 4) notReservedKeyword
     * 5) max
     * 6) different
     * 
     * @param string $to The new name of the table.
     * @param string $from The original name of the table.
     * @param InputInterface $input The Symfony InputInterface object.
     * @param OutputInterface $output The Symfony OutputInterface object.
     * @return string The original value if validation passed.  The updated 
     * value if validation failed.
     */
    private static function validateRenameOption(
        string $to, 
        string $from, 
        InputInterface $input, 
        OutputInterface $output
    ): string {

        $to = Str::lower($to);
        $from = Str::lower($from);
        $message = "Provide name for new table.";
        self::argOptionValidate($to, $message, $input, $output, 'original-name', 50, $from);
        return $to;
    }
}