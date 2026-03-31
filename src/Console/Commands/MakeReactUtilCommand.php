<?php
namespace Console\Commands;

use Console\ConsoleCommand;
use Console\Helpers\React;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Implements command for making a new JavaScript utility by running react:util.
 * More information can be found <a href="https://chapmancbvcu.github.io/chappy-php-starter/react_utils#overview">here</a>.
 */
class MakeReactUtilCommand extends ConsoleCommand {
    /**
     * Configures the command.
     *
     * @return void
     */
    protected function configure(): void
    {
        $this->setName('react:util')
            ->setDescription('Generates a new React.js supporting utility')
            ->setHelp('php console react:util <component_name>')
            ->addArgument('utility-name', InputArgument::REQUIRED, 'Pass the name for the new React.js utility');
    }

    /**
     * Executes the command
     *
     * @return int A value that indicates success, invalid, or failure.
     */
    protected function handle(): int
    {
        $utilityName = $this->getArgument('utility-name');
        $message = "Enter name for new JavaScript utility";
        if($utilityName) {
            React::argOptionValidate($utilityName, $message, $this->question(), ['max:50']);
        } else {
            $utilityName = React::prompt($message, $this->question(), ['max:50']);
        }
        return React::makeUtility($utilityName);
    }
}
