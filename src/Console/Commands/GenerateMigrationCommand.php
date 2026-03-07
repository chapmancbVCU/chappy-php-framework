<?php
namespace Console\Commands;

use Console\Helpers\Migrate;
use Console\HasValidators;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Supports ability to generate new migration file.  
 * More information can be found <a href="https://chapmancbvcu.github.io/chappy-php-starter/database_operations#creating-a-new-table">here</a>.
 */
class GenerateMigrationCommand extends Command
{
    use HasValidators;

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
     * @param InputInterface $input The input.
     * @param OutputInterface $output The output.
     * @return int A value that indicates success, invalid, or failure.
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $tableName = $input->getArgument('table-name');
        if($tableName) {
            Migrate::argOptionValidate(
                $tableName,
                Migrate::MIGRATION_PROMPT,
                $input,
                $output,
                'table-name'
            );
        }

        [$renameOption, $updateOption] = Migrate::setFlags($input);
        $bothFlagsSet = Migrate::isBothFlagsSet($renameOption, $updateOption);

        if($bothFlagsSet) return Command::FAILURE;

        // When tableName argument is provided.
        if($tableName) return Migrate::contents($tableName, $renameOption, $updateOption, $input, $output);

        // tableName not provided with rename option set.
        if(!$tableName && $renameOption) return Migrate::renamePrompt($input, $output, $renameOption);

        // tableName not provided with update option set.
        $tableName = Migrate::migrationNamePrompt($input, $output);
        if($updateOption) return Migrate::makeUpdateMigration($tableName, $input);

        // Ask questions when tableName is not provided and no options set.
        return Migrate::migrationTypePrompt($input, $tableName, $output);
    }
}
