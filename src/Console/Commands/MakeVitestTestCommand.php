<?php
namespace Console\Commands;

use Console\ConsoleCommand;
use Console\Helpers\Testing\VitestTestBuilder;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * Creates test for React.js or JavaScript files.  Use flags to determine which one
 * to generate.  More information can be found 
 * <a href="https://chapmancbvcu.github.io/chappy-php-starter/vitest#creating-tests">here</a>.
 */
class MakeVitestTestCommand extends ConsoleCommand
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
     * @return int A value that indicates success, invalid, or failure.
     */
    protected function handle(): int
    {
        $testName = $this->getArgument('test-name');
        $suite = VitestTestBuilder::suite($this->input);
        
        $message = "Enter new name for new test file.";
        if($testName) {
            VitestTestBuilder::argOptionValidate($testName, $message, $this->question(), ['max:150']);
        } else {
            $testName = VitestTestBuilder::prompt($message, $this->question(), ['max:150']);
            $suite = VitestTestBuilder::suiteChoice($suite, $this->question());
        }

        return VitestTestBuilder::makeTest($testName, $suite);
    }
}
