<?php
declare(strict_types=1);
namespace Console\Commands;

use Console\ConsoleCommand;
use Console\Helpers\PHPHelper;
use Console\Helpers\React;
use Console\Helpers\View;
use Symfony\Component\Console\Input\InputOption;

/**
 * Generates profile views.
 */
class ProfileViewsCommand extends ConsoleCommand {
    /**
     * Configures the command.
     *
     * @return void
     */
    protected function configure(): void
    {
        $this->setName('generate:profile:views')
            ->setDescription('Generates profile views.')
            ->setHelp('php console generate:profile:views')
            ->addOption('react', null, InputOption::VALUE_NONE, 'React version');
    }

    /**
     * Executes the command
     *
     * @return int A value that indicates success, invalid, or failure.
     */
    protected function handle(): int
    {
        View::makeMenu('main');
        View::makeLayout('default');
        if($this->getOption('react')) {
            return React::profileComponents();
        }
        return PHPHelper::profileViews();
    }
}
