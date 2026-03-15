<?php
namespace Console\Commands;

use Console\Helpers\Testing\PHPUnitTestBuilder;
use Core\Lib\Utilities\Str;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Supports ability to generate new PHPUnit test file by running make:test.
 * More information can be found <a href="https://chapmancbvcu.github.io/chappy-php-starter/php_unit#creating-tests">here</a>.
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
            ->addArgument('test-name', InputArgument::OPTIONAL, 'Pass the test\'s name.')
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
        $testName = $input->getArgument('test-name');
        $feature = $input->getOption('feature');
        $message = "Enter name for new test case class.";

        if($testName) {
            PHPUnitTestBuilder::argOptionValidate($testName, $message, $input, $output, ['max:150']);
        } else {
            $testName = PHPUnitTestBuilder::prompt($message, $input, $output, ['max:150']);
            $feature = PHPUnitTestBuilder::featurePrompt($feature, $input, $output);
        }

        return PHPUnitTestBuilder::makeTest(Str::ucfirst($testName), $feature);
    }
}
