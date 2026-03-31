<?php
namespace Console\Commands;

use Console\ConsoleCommand;
use Console\Helpers\React;

/**
 * Implements command for generating the auth page components. 
 * More information can be found <a href="https://chapmancbvcu.github.io/chappy-php-starter/controllers_and_views#view-commands">here</a>.
 */
class ReactAuthCommand extends ConsoleCommand {
    /**
     * Configures the command.
     *
     * @return void
     */
    protected function configure(): void
    {
        $this->setName('react:auth')
            ->setDescription('Generates auth page components.')
            ->setHelp('php console react:auth');
    }

    /**
     * Executes the command
     *
     * @return int A value that indicates success, invalid, or failure.
     */
    protected function handle(): int
    {
        return React::authComponents();
    }
}

