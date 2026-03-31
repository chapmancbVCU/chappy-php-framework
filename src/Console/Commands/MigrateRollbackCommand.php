<?php
namespace Console\Commands;

use Console\ConsoleCommand;
use Console\Helpers\Migrate;
use Console\Helpers\Tools;
use Symfony\Component\Console\Input\InputOption;

/**
 * Supports ability to roll back database migrations. 
 * More information can be found <a href="https://chapmancbvcu.github.io/chappy-php-starter/database_operations#rollback">here</a>.
 */
class MigrateRollbackCommand extends ConsoleCommand
{
    /**
     * Configures the command.
     *
     * @return void
     */
    protected function configure(): void
    {
        $this->setName('migrate:rollback')
            ->setDescription('Performs roll back operation')
            ->setHelp('php console migrate:rollback --step=<number> or --batch=<batch_number>')
            ->addOption(
                'step',
                null,
                InputOption::VALUE_REQUIRED,
                'Number of steps to roll back',
                false
            )
            ->addOption(
                'batch',
                null,
                InputOption::VALUE_REQUIRED,
                'Particular batch to roll back',
                false
            );
    }
 
    /**
     * Executes the command
     *
     * @return int A value that indicates success, invalid, or failure.
     */
    protected function handle(): int
    {
        if(Tools::isProduction() && !Migrate::confirmMigrationInProduction($this->question())) {
            console_info("Cancelling operation.");
            return self::SUCCESS;
        }

        $step = $this->getOption('step');
        $batch = $this->getOption('batch');

        if($step && $batch) {
            console_warning("Can't perform step and batch operations at the same time");
            return self::FAILURE;
        }

        $status = 1;
        if($step === false && $batch === false) {
            $status = Migrate::rollback();
        } else if($step || $step === '') {
            $status = Migrate::rollbackStep($step);
        } else if($batch || $batch === '') {
            $status = Migrate::rollback($batch);
        }

        if($status == self::FAILURE) {
            return $status;
        }
        return self::SUCCESS;
    }
}