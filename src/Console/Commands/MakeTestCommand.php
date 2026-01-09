<?php
namespace Console\Commands;

use Console\Helpers\Testing\PHPUnitRunner;
use Core\Lib\Utilities\Str;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Supports ability to generate new PHPUnit test file by running make:test.
 * More information can be found <a href="https://chapmancbvcu.github.io/chappy-php-starter/unit_tests#creating-tests">here</a>.
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
        return PHPUnitRunner::makeTest($testName, $input);
    }
}
