<?php
namespace Console\Commands;
use Console\Helpers\Tools;
use Console\Helpers\Migrate;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Supports ability to roll back database migrations.
 */
class MigrateRollbackCommand extends Command
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
     * @param InputInterface $input The input.
     * @param OutputInterface $output The output.
     * @return int A value that indicates success, invalid, or failure.
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $step = $input->getOption('step');
        $batch = $input->getOption('batch');

        if($step && $batch) {
            Tools::info("Can't perform step and batch operations at the same time");
            return Command::FAILURE;
        }

        $status = 1;
        if($step === false && $batch === false) {
            $status = Migrate::rollback();
        } else if($step || $step === '') {
            $status = Migrate::rollbackStep($step);
        } else if($batch || $batch === '') {
            $status = Migrate::rollback($batch);
        }

        if($status == Command::FAILURE) {
            return $status;
        }
        return Command::SUCCESS;
    }
}