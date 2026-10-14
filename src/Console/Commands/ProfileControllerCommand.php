<?php
declare(strict_types=1);
namespace Console\Commands;

use Console\ConsoleCommand;
use Console\Helpers\Profile;
use Symfony\Component\Console\Input\InputOption;

/**
 * Generates profile controller.
 */
class ProfileControllerCommand extends ConsoleCommand {
    /**
     * Configures the command.
     *
     * @return void
     */
    protected function configure(): void
    {
        $this->setName('generate:profile:controller')
            ->setDescription('Generates profile controller.')
            ->setHelp('php console generate:profile:controller')
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
            return Profile::makeControllerJS();
        }
        return Profile::makeControllerPHP();
    }
}
