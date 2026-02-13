<?php
namespace Console\Commands;
 
use Console\Helpers\Migrate;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
/**
 * Supports ability to generate new controller class by typing make:controller.
 * More information can be found <a href="https://chapmancbvcu.github.io/chappy-php-starter/controllers#creating-a-controller">here</a>.
 */
class RestoreMigrationsCommand extends Command
{
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
                'Add CRUD functions'
            )
            ->addOption(
                'email_attachments',
                null,
                InputOption::VALUE_NONE,
                'Add CRUD functions'
            )
            ->addOption(
                'migrations',
                null,
                InputOption::VALUE_NONE,
                'Add CRUD functions'
            )
            ->addOption(
                'profile_images',
                null,
                InputOption::VALUE_NONE,
                'Add CRUD functions'
            )
            ->addOption(
                'user_sessions',
                null,
                InputOption::VALUE_NONE,
                'Add CRUD functions'
            )
            ->addOption(
                'users',
                null,
                InputOption::VALUE_NONE,
                'Add CRUD functions'
            );
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
        $tables = ['acl', 'email_attachments', 'migrations', 'profile_images', 'user_sessions', 'users'];
         
        foreach($tables as $table) {
            if($input->hasOption($table) && $input->getOption($table)) {
                return Migrate::generateMigrationByName($input);
            }
        }
        
        return Migrate::generateAllMigrations();
    }  
}
