<?php
declare(strict_types=1);
namespace Console\Commands;

use Console\ConsoleCommand;
use Console\Helpers\PHPHelper;
use Console\Helpers\React;
use Console\Helpers\View;
use Symfony\Component\Console\Input\InputOption;

/**
 * Generates admin dashboard views.
 */
class AdminDashboardViewCommand extends ConsoleCommand {
    /**
     * Configures the command.
     *
     * @return void
     */
    protected function configure(): void
    {
        $this->setName('generate:admin:view')
            ->setDescription('Generates admin dashboard views.')
            ->setHelp('php console generate:admin:view')
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
            return React::adminDashboardComponents();
        }
        return PHPHelper::adminDashboardViews();
    }
}
