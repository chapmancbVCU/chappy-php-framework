<?php
namespace Console\Commands;

use Console\ConsoleCommand;
use Console\Helpers\React;

/**
 * Implements command for generating the home/Index.jsx page component. 
 * More information can be found <a href="https://chapmancbvcu.github.io/chappy-php-starter/controllers_and_views#view-commands">here</a>.
 */
class ReactHomeCommand extends ConsoleCommand {
    /**
     * Configures the command.
     *
     * @return void
     */
    protected function configure(): void
    {
        $this->setName('react:home')
            ->setDescription('Generates home/Index.jsx page component.')
            ->setHelp('php console react:home');
    }

    /**
     * Executes the command
     *
     * @return int A value that indicates success, invalid, or failure.
     */
    protected function handle(): int
    {
        return React::homeComponent();
    }
}
