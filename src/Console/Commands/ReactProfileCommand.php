<?php
namespace Console\Commands;

use Console\ConsoleCommand;
use Console\Helpers\React;

/**
 * Implements command for generating the profile page components. 
 * More information can be found <a href="https://chapmancbvcu.github.io/chappy-php-starter/controllers_and_views#view-commands">here</a>.
 */
class ReactProfileCommand extends ConsoleCommand {
    /**
     * Configures the command.
     *
     * @return void
     */
    protected function configure(): void
    {
        $this->setName('react:profile')
            ->setDescription('Generates profile page components.')
            ->setHelp('php console react:profile');
    }

    /**
     * Executes the command
     *
     * @return int A value that indicates success, invalid, or failure.
     */
    protected function handle(): int
    {
        return React::profileComponents();
    }
}
