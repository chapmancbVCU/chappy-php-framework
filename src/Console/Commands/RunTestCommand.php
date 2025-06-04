<?php
namespace Console\Commands;
use Core\Helper;
use Console\Helpers\Test;
use Console\Helpers\Tools;
use Core\Lib\Utilities\Str;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Supports ability to run a phpunit test with only the name of the test 
 * file is accepted as a required input.
 */
class RunTestCommand extends Command
{
    /**
     * Configures the command.
     *
     * @return void
     */
    protected function configure(): void
    {
        $this->setName('test')
            ->setDescription('Performs the phpunit test.')
            ->setHelp('php console test <test_file_name> without the .php extension.')
            ->addArgument('testname', InputArgument::OPTIONAL, 'Pass the test file\'s name.')
            ->addOption('unit', null, InputOption::VALUE_NONE, 'Run unit tests.')
            ->addOption('feature', null, InputOption::VALUE_NONE, 'Run feature tests.');
;
;
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
        // Get options and arguments
        $testArg = $input->getArgument('testname');
        $unit = $input->getOption('unit');
        $feature = $input->getOption('feature');

        if(Test::allTests($feature, $output, $testArg, $unit)) {
            Tools::info("All test have been completed");
            return Command::SUCCESS;
        }
        
        if($testArg && !$unit && !$feature) {
            $command = '';
            if(Str::contains($testArg, '::')) {
                // Run a specific function
                [$class, $method] = explode('::', $testArg);

                $path = Test::UNIT_PATH.$class.'.php';
                if(!file_exists($path)) { $path = Test::FEATURE_PATH.$class.'.php'; }

                if(file_exists($path)) {
                    $command .= escapeshellarg($path) . ' --filter ' . escapeshellarg($method);
                } else {
                    Tools::info("Test class file not found for '$class'", 'debug', 'yellow');
                }

            } elseif(file_exists(Test::UNIT_PATH.$testArg.'php')) {
                $command .= ' '.Test::UNIT_PATH.$testArg.'php';
            } elseif(file_exists(Test::FEATURE_PATH.$testArg.'php')) {
                $command .= ' '.Test::FEATURE_PATH.$testArg.'php';
            }
            Test::runTest($command, $output);
        }
        Tools::info("Selected tests have been completed");
        return Command::SUCCESS;
    }
}
