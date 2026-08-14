<?php
declare(strict_types=1);
namespace Console\Helpers;

use Console\Console;
use Console\FrameworkQuestion;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Command\Command;
use Core\Lib\Utilities\Str;

/**
 * Helper class for generating migration classes.
 */
class MigrationBuilder extends Console {

    /**
     * The message to present to user when name of migration is being asked.
     */
    public const MIGRATION_PROMPT = "Enter name for new migration.";

    /**
     * Array of validators common for the make:migration command.
     */
    public const PROMPT_ATTRIBUTES = [
        'max:50', 'required', 'noSpecialChars', 'alpha', 
        'notReservedKeyword', 'notReservedSQLKeyword'
    ];
    
    /**
     * Generates new migration class if table-name argument is provided.  If rename or update 
     * flags are set then appropriate migration class is created.
     *
     * @param string $migrationName The name of the table for the new migration class.
     * @param mixed $renameOption Value/state of rename flag.
     * @param mixed $renameOption Value/state of update flag.
     * @param FrameworkQuestion $question Instance of FrameworkQuestion class.
     * @return int A value that indicates success, invalid, or failure.
     */
    public static function contents(
        string $migrationName, 
        mixed $renameOption, 
        mixed $updateOption, 
        FrameworkQuestion $question
        ): int {

        if($renameOption) {
            $renameOption = self::validateRenameOption($renameOption, $migrationName, $question);
            return self::makeRenameMigration($migrationName, $renameOption);
        }
            
        else if($updateOption) return self::makeUpdateMigration($migrationName);
        else return self::makeMigration($migrationName);
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
        $path = Migrate::MIGRATIONS_PATH."MDT20240808232014CreateAclTable.php";
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
        $path = Migrate::MIGRATIONS_PATH."MDT20250621195401CreateEmailAttachmentsTable.php";
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
        $path = Migrate::MIGRATIONS_PATH."MDT20240805010123CreateMigrationTable.php";
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
        $path = Migrate::MIGRATIONS_PATH."MDT20240821210722CreateProfileImagesTable.php";
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
        $path = Migrate::MIGRATIONS_PATH."MDT20241118175443CreateUserSessionsTable.php";
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
        $path = Migrate::MIGRATIONS_PATH."MDT20240805010157CreateUsersTable.php";
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
    public static function isBothFlagsSet(mixed $renameOption, mixed $updateOption): bool {
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
            Migrate::MIGRATIONS_PATH.$fileName.'.php',
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
            Migrate::MIGRATIONS_PATH.$fileName.'.php',
            MigrationStubs::migrationRenameClass($fileName, $from, $to),
            'Migration'
        );
    }

    /**
     * Generates a migration class for updating existing table.
     *
     * @param string $migrationName The name of the table the new migration 
     * will target.
     * @return int A value that indicates success, invalid, or failure.
     */
    public static function makeUpdateMigration(string $migrationName): int {
        $tableName = Str::lower($migrationName);
        
        // Generate Migration class
        $fileName = "MDT".self::fileNameTime()."Update".Str::ucfirst($migrationName)."Table";
        return Tools::writeFile(
            Migrate::MIGRATIONS_PATH.$fileName.'.php',
            MigrationStubs::migrationUpdateClass($fileName, $tableName),
            'Migration'
        );
    }

    /**
     * Handles question for which table a new migration will target.
     *
     * @param FrameworkQuestion $question Instance of FrameworkQuestion class.
     * @return string The name of the table the new migration will target.
     */
    public static function migrationNamePrompt(FrameworkQuestion $question): string {      
        return self::prompt(self::MIGRATION_PROMPT, $question, self::modifiedAttributes(['fieldName:table-name']), [], null, true);
    }

    /**
     * Prompts user for input when no argument and no options are set.
     *
     * @param FrameworkQuestion $question Instance of FrameworkQuestion class.
     * @param string $migrationName Name of migration to be created or 
     * renamed to.
     * @return int A value that indicates success, invalid, or failure.
     */
    public static function migrationTypePrompt(
        FrameworkQuestion $question,
        string $migrationName, 
    ): int {
        $choices = ['New Table (default)', 'Rename', 'Update'];
        $response = self::choice(self::MIGRATION_PROMPT, $choices, $question, $choices[0]);
        
        if($response == 'New Table (default)') return self::makeMigration($migrationName);
        if($response == 'Rename') return self::renameChoice($migrationName, $question);
        if($response == 'Update') return self::makeUpdateMigration($migrationName);
        return Command::FAILURE;
    }

    /**
     * Returns modified version of MigrationBuilder::PROMPT_ATTRIBUTES array.
     *
     * @param array $attributes The additional attributes you want to add.
     * @return array The modified const array.
     */
    private static function modifiedAttributes(array $attributes): array {
        return array_merge(self::PROMPT_ATTRIBUTES, $attributes);
    }

    /**
     * Prompts user to enter name for table to be renamed.  Used when 
     * user responds with the choice to rename.
     *
     * @param string $migrationName The new name for the table to be renamed.
     * @param FrameworkQuestion $question Instance of FrameworkQuestion class.
     * @return int A value that indicates success, invalid, or failure.
     */
    private static function renameChoice(string $migrationName, FrameworkQuestion $question): int {
        $message = "Provide name for original table";
        $response = self::prompt(
            $message, 
            $question, 
            self::modifiedAttributes(['fieldName:original-table', "different:{$migrationName}"]),
            [],
            null,
            true
        );

        return self::makeRenameMigration($response, $migrationName);
    }

    /**
     * Prompts user to enter name of table to be updated.  Used when user 
     * provides name of controller as an argument.
     *
     * @param FrameworkQuestion $question Instance of FrameworkQuestion class.
     * @param mixed $renameOption Value/state of rename flag.
     * @return int A value that indicates success, invalid, or failure.
     */
    public static function renamePrompt(FrameworkQuestion $question, mixed $renameOption): int {
        $message = "Enter name for original table";
        $response = self::prompt($message, $question, self::modifiedAttributes(['fieldName:original-table']), [], null, true);
        $renameOption = self::validateRenameOption($renameOption, $response, $question);  
        return self::makeRenameMigration($response, $renameOption);
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
     * @param FrameworkQuestion $question Instance of FrameworkQuestion class.
     * @return string The original value if validation passed.  The updated 
     * value if validation failed.
     */
    private static function validateRenameOption(
        string $to, 
        string $from, 
        FrameworkQuestion $question
    ): string {

        $to = Str::lower($to);
        $from = Str::lower($from);
        $message = "Provide name for new table.";
        $attributes = self::modifiedAttributes(['fieldName:original-name', "different:{$from}"]);
        self::argOptionValidate($to, $message, $question, $attributes);
        return $to;
    }
}