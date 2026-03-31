<?php
namespace Console\Commands;

use Console\ConsoleCommand;
use Console\Helpers\Testing\ThirdPartyTests;
use Core\Lib\Utilities\Str;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Command for generating third party unit test builders.
 */
class MakeTestBuilderCommand extends ConsoleCommand {
    /**
     * Configures the command.
     *
     * @return void
     */
    protected function configure(): void
    {
        $this->setName('make:test:builder')
            ->setDescription('Generates a test builder for a 3rd party suite')
            ->setHelp('php console make:test:builder <builder-name>')
            ->addArgument('builder-name', InputArgument::OPTIONAL, 'Pass name of directory and builder');
    }

    /**
     * Executes the command
     *
     * @return int A value that indicates success, invalid, or failure.
     */
    protected function handle(): int
    {
        $className = $this->getArgument('builder-name');
        $message = "Enter name for new test builder.";
        if($className) {
            ThirdPartyTests::argOptionValidate($className, $message, $this->question(), ['max:50']);
        } else {
            $className = ThirdPartyTests::prompt($message, $this->question(), ['max:50']);
        }
        return ThirdPartyTests::makeBuilder(Str::ucfirst($className)."Builder");
    }
}
