<?php
namespace Console\Commands;

use Console\ConsoleCommand;
use Console\Helpers\Log;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Supports ability to delete log file.
 * More information can be found <a href="https://chapmancbvcu.github.io/chappy-php-starter/debugging_and_logs#clear-logs">here</a>.
 */
class RemoveLogsCommand extends ConsoleCommand {
    /**
     * Configures the command.
     *
     * @return void
     */
    protected function configure(): void
    {
        $this->setName('log:clear')
            ->setDescription('Removes log file')
            ->setHelp('Run php console log:clear to remove log files.');
    }

    /**
     * Executes the command
     *
     * @param InputInterface $input The input.
     * @param OutputInterface $output The output.
     * @return int A value that indicates success, invalid, or failure.
     */
    protected function handle(): int
    {
        $logType = Log::deletePrompt($this->question());
        if(Log::deleteConfirm($logType, $this->question())) {
            $method = 'delete'.$logType;
            Log::$method();
        }

        return COMMAND::SUCCESS;
    }
}
