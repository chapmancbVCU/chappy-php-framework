<?php
declare(strict_types=1);
namespace Console\Commands;

use Console\ConsoleCommand;
use Console\Helpers\Auth;
use Symfony\Component\Console\Input\InputOption;

/**
 * Generates auth controller.
 */
class AuthControllerCommand extends ConsoleCommand {
    /**
     * Configures the command.
     *
     * @return void
     */
    protected function configure(): void
    {
        $this->setName('generate:auth:controller')
            ->setDescription('Generates auth controller.')
            ->setHelp('php console generate:auth:controller')
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
            return Auth::makeControllerJS();
        }
        return Auth::makeControllerPHP();
    }
}
