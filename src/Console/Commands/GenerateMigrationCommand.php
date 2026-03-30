<?php
namespace Console\Commands;

use Console\ConsoleCommand;
use Console\Helpers\Migrate;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Supports ability to generate new migration file.  
 * More information can be found <a href="https://chapmancbvcu.github.io/chappy-php-starter/database_operations#creating-a-new-table">here</a>.
 */
class GenerateMigrationCommand extends ConsoleCommand
{
    /**
     * Configures the command.
     *
     * @return void
     */
    protected function configure(): void
    {
        $this->setName('make:migration')
            ->setDescription('Generates a Database Migration!')
            ->setHelp('make:migration <table_name>, --rename flag to rename table or --update flag for update table migration')
            ->addArgument('table-name', InputArgument::OPTIONAL, 'Pass the table\'s name.')
            ->addOption('update', null, InputOption::VALUE_NONE, 'Update table')
            ->addOption('rename', null, InputOption::VALUE_REQUIRED, 'Rename table', false);
    }
 
    /**
     * Executes the command
     *
     * @return int A value that indicates success, invalid, or failure.
     */
    protected function handle(): int
    {
        $tableName = $this->getArgument('table-name');
        if($tableName) {
            Migrate::argOptionValidate(
                $tableName,
                Migrate::MIGRATION_PROMPT,
                $this->question()
            );
        }

        [$renameOption, $updateOption] = Migrate::setFlags($this->input);
        $bothFlagsSet = Migrate::isBothFlagsSet($renameOption, $updateOption);

        if($bothFlagsSet) return self::FAILURE;

        // When tableName argument is provided.
        if($tableName) return Migrate::contents($tableName, $renameOption, $updateOption, $this->question());

        // tableName not provided with rename option set.
        if(!$tableName && $renameOption) return Migrate::renamePrompt($this->question(), $renameOption);

        // tableName not provided with update option set.
        $tableName = Migrate::migrationNamePrompt($this->question());
        if($updateOption) return Migrate::makeUpdateMigration($tableName);

        // Ask questions when tableName is not provided and no options set.
        return Migrate::migrationTypePrompt($this->question(), $tableName,);
    }
}
