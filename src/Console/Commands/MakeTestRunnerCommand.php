<?php
namespace Console\Commands;

use Console\Helpers\Testing\ThirdPartyTests;
use Console\Helpers\Tools;
use Core\Lib\Utilities\Str;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command for generating new unit test runner.
 */
class MakeTestRunnerCommand extends Command {
    /**
     * Configures the command.
     *
     * @return void
     */
    protected function configure(): void
    {
        $this->setName('make:test:runner')
            ->setDescription('Generates a test runner for a 3rd party suite')
            ->setHelp('php console make:view <directory_name>.<view_name>')
            ->addArgument('runner-name', InputArgument::REQUIRED, 'Pass name of directory and runner');
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
        $builderArray = Tools::dotNotationVerify('runner-name', $input);
        if($builderArray == Command::FAILURE) return Command::FAILURE;

        $filePath = ThirdPartyTests::THIRD_PARTY_TEST_PATH.'Suites'.DS.Str::ucfirst($builderArray[0]).DS;
        $isDirMade = Tools::createDirWithPrompt($filePath, $input, $output);
        
        if($isDirMade == Command::FAILURE) return Command::FAILURE;

        $className = Str::ucfirst($builderArray[1]);
        return ThirdPartyTests::makeRunner($filePath, $className);
    }
}
