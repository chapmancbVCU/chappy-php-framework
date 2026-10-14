<?php
declare(strict_types=1);
namespace Console\Commands;

use Console\ConsoleCommand;
use Console\Helpers\PHPHelper;
use Console\Helpers\Profile;
use Console\Helpers\React;
use Symfony\Component\Console\Input\InputOption;

/**
 * Generates profile controllers and views.
 */
class ProfileCommand extends ConsoleCommand {
    /**
     * Configures the command.
     *
     * @return void
     */
    protected function configure(): void
    {
        $this->setName('generate:profile')
            ->setDescription('Generates profile controllers and views.')
            ->setHelp('php console generate:profile')
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
            Profile::makeControllerJS();
            return React::profileComponents();
        }
        Profile::makeControllerPHP();
        return PHPHelper::profileViews();
    }
}
