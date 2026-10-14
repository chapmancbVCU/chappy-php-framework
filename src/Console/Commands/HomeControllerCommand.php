<?php
declare(strict_types=1);
namespace Console\Commands;

use Console\ConsoleCommand;
use Console\Helpers\Home;
use Symfony\Component\Console\Input\InputOption;

/**
 * Generates home controller.
 */
class HomeControllerCommand extends ConsoleCommand {
    /**
     * Configures the command.
     *
     * @return void
     */
    protected function configure(): void
    {
        $this->setName('generate:home:controller')
            ->setDescription('Generates home controller.')
            ->setHelp('php console generate:home:controller')
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
            return Home::makeControllerJS();
        }
        return Home::makeControllerPHP();
    }
}
