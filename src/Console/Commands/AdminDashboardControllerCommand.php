<?php
declare(strict_types=1);
namespace Console\Commands;

use Console\ConsoleCommand;
use Console\Helpers\AdminDashboard;
use Symfony\Component\Console\Input\InputOption;

/**
 * Generates admin dashboard controller.
 */
class AdminDashboardControllerCommand extends ConsoleCommand {
    /**
     * Configures the command.
     *
     * @return void
     */
    protected function configure(): void
    {
        $this->setName('generate:admin:controller')
            ->setDescription('Generates admin dashboard controller.')
            ->setHelp('php console generate:admin:controller')
            ->addOption('react', null, InputOption::VALUE_NONE, 'React version');
    }

    /**
     * Executes the command
     *
     * @return int A value that indicates success, invalid, or failure.
     */
    protected function handle(): int
    {
        if($this->getOption('react')) {
            return AdminDashboard::makeControllerJS();
        }
        return AdminDashboard::makeControllerPHP();
    }
}
