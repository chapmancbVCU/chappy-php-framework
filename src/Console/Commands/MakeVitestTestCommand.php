<?php
namespace Console\Commands;

use Console\Helpers\Testing\VitestTestBuilder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Creates test for React.js or JavaScript files.  Use flags to determine which one
 * to generate.  More information can be found 
 * <a href="https://chapmancbvcu.github.io/chappy-php-starter/vitest#creating-tests">here</a>.
 */
class MakeVitestTestCommand extends Command
{
    /**
     * Configures the command.
     *
     * @return void
     */
    protected function configure(): void
    {
        $this->setName('react:make:test')
            ->setDescription('Generates a new test file!')
            ->setHelp('php console react:make:test <test_name>')
            ->addArgument('test-name', InputArgument::OPTIONAL, 'Pass the test\'s name.')
            ->addOption('unit', null, InputOption::VALUE_NONE, 'Create unit test')
            ->addOption('component', null, InputOption::VALUE_NONE, 'Create component test')
            ->addOption('view', null, InputOption::VALUE_NONE, 'Create view test');
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
        $suite = VitestTestBuilder::suite($input);
        
        $message = "Enter new name for new test file.";
        if($testName) {
            VitestTestBuilder::argOptionValidate($testName, $message, $input, $output, ['max:150']);
        } else {
            $testName = VitestTestBuilder::prompt($message, $input, $output, ['max:150']);
            $suite = VitestTestBuilder::suiteChoice($suite, $input, $output);
        }

        return VitestTestBuilder::makeTest($testName, $suite);
    }
}
