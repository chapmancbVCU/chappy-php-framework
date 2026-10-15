<?php
declare(strict_types=1);
namespace Console\Commands;

use Console\ConsoleCommand;
use Console\Helpers\AdminDashboard;
use Console\Helpers\PHPHelper;
use Console\Helpers\React;
use Console\Helpers\View;
use Symfony\Component\Console\Input\InputOption;

/**
 * Generates admin dashboard controllers and views.
 */
class AdminDashboardCommand extends ConsoleCommand {
    /**
     * Configures the command.
     *
     * @return void
     */
    protected function configure(): void
    {
        $this->setName('generate:admin')
            ->setDescription('Generates admin dashboard controllers and views.')
            ->setHelp('php console generate:admin')
            ->addOption('react', null, InputOption::VALUE_NONE, 'React version');
    }

    /**
     * Executes the command
     *
     * @return int A value that indicates success, invalid, or failure.
     */
    protected function handle(): int
    {
        View::makeMenu('admin');
        View::makeLayout('admin', 'admin');
        if($this->getOption('react')) {
            AdminDashboard::makeControllerJS();
            return React::adminDashboardComponents();
        }
        AdminDashboard::makeControllerPHP();
        return PHPHelper::adminDashboardViews();
    }
}
