<?php
namespace Console\Commands;

use Console\ConsoleCommand;
use Console\Helpers\React;

/**
 * Implements command for generating the error/NotFound.jsx page component. 
 * More information can be found <a href="https://chapmancbvcu.github.io/chappy-php-starter/controllers_and_views#view-commands">here</a>.
 */
class ReactErrorCommand extends ConsoleCommand {
    /**
     * Configures the command.
     *
     * @return void
     */
    protected function configure(): void
    {
        $this->setName('react:error')
            ->setDescription('Generates error/NotFound.jsx page component.')
            ->setHelp('php console react:error');
    }

    /**
     * Executes the command
     *
     * @return int A value that indicates success, invalid, or failure.
     */
    protected function handle(): int
    {
        return React::errorNotFoundComponent();
    }
}
