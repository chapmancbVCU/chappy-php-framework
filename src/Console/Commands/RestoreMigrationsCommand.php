<?php
namespace Console\Commands;

use Console\ConsoleCommand;
use Console\Helpers\Migrate;
use Symfony\Component\Console\Input\InputOption;

/**
 * Supports ability to generate migrations using flags or all if no flag is set.
 */
class RestoreMigrationsCommand extends ConsoleCommand {
    /**
     * Configures the command.
     *
     * @return void
     */
    protected function configure(): void
    {
        $this->setName('migrate:restore')
            ->setDescription('Restores all or a subset based on a flag that is set.')
            ->setHelp('php console migrate:restore or migrate:restore --<table-name>')
            ->addOption(
                'acl',
                null,
                InputOption::VALUE_NONE,
                'Generates acl table migration when set'
            )
            ->addOption(
                'email_attachments',
                null,
                InputOption::VALUE_NONE,
                'Generates email_attachments table migration when set'
            )
            ->addOption(
                'migrations',
                null,
                InputOption::VALUE_NONE,
                'Generates migrations table migration when set'
            )
            ->addOption(
                'profile_images',
                null,
                InputOption::VALUE_NONE,
                'Generates profile_images table migration when set'
            )
            ->addOption(
                'user_sessions',
                null,
                InputOption::VALUE_NONE,
                'Generates user_sessions table migration when set'
            )
            ->addOption(
                'users',
                null,
                InputOption::VALUE_NONE,
                'Generates users table migration when set'
            );
    }
 
    /**
     * Executes the command
     *
     * @return int A value that indicates success, invalid, or failure.
     */
    protected function handle(): int
    {
        $tables = ['acl', 'email_attachments', 'migrations', 'profile_images', 'user_sessions', 'users'];
         
        foreach($tables as $table) {
            if($this->hasOption($table) && $this->getOption($table)) {
                return Migrate::generateMigrationByName($this->input);
            }
        }
        
        return Migrate::generateAllMigrations();
    }  
}
