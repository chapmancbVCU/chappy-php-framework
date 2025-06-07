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
            ->addOption('feature', null, InputOption::VALUE_NONE, 'Create feature test')
            ->addOption('app', null, InputOption::VALUE_NONE, 'Make class extend ApplicationTestCase');
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
        
        if($input->getOption('feature')) {
            $type = 'Feature';
        } else {
            $type = 'Unit';
        }

        // Generate unit test class
        if($input->getOption('app')) {
            return Tools::writeFile(
                ROOT.DS.'tests'.DS.$type.DS.$testName.'.php',
                Test::makeAppTest($testName, $type),
                'Test'
            );
        } else {
            return Tools::writeFile(
                ROOT.DS.'tests'.DS.$type.DS.$testName.'.php',
                Test::makeTest($testName, $type),
                'Test'
            );
        }


        return Command::FAILURE;
    }
}
