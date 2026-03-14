<?php
namespace Console\Commands;

use Console\Helpers\Testing\ThirdPartyTests;
use Core\Lib\Utilities\Str;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command for generating third party unit test builders.
 */
class MakeTestBuilderCommand extends Command {
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
     * @param InputInterface $input The input.
     * @param OutputInterface $output The output.
     * @return int A value that indicates success, invalid, or failure.
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $className = $input->getArgument('builder-name');
        $message = "Enter name for new test builder.";
        if($className) {
            ThirdPartyTests::argOptionValidate($className, $message, $input, $output, ['max:50']);
        } else {
            $className = ThirdPartyTests::prompt($message, $input, $output, ['max:50']);
        }
        return ThirdPartyTests::makeBuilder(Str::ucfirst($className)."Builder");
    }
}
