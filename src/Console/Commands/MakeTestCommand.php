<?php
namespace Console\Commands;
 
use Console\Helpers\Test;
use Console\Helpers\Tools;
use Core\Lib\Utilities\Str;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Supports ability to generate new test file.
 */
class MakeTestCommand extends Command
{
    /**
     * Configures the command.
     *
     * @return void
     */
    protected function configure(): void
    {
        $this->setName('make:test')
            ->setDescription('Generates a new test file!')
            ->setHelp('php console make:test <test_name>')
            ->addArgument('testname', InputArgument::REQUIRED, 'Pass the test\'s name.')
            ->addOption('feature', null, InputOption::VALUE_NONE, 'Create feature test');
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
        $testName = Str::ucfirst($input->getArgument('testname'));
        
        if(Test::testIfExists($testName)) {
            return Command::FAILURE;
        }

        if($input->getOption('feature')) {
            return Tools::writeFile(
                ROOT.DS.'tests'.DS.'Feature'.DS.$testName.'.php',
                Test::makeFeatureTest($testName),
                'Test'
            );
        } else {
            return Tools::writeFile(
                ROOT.DS.'tests'.DS.'Unit'.DS.$testName.'.php',
                Test::makeUnitTest($testName),
                'Test'
            );
        }

        return Command::FAILURE;
    }
}
