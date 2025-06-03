<?php
declare(strict_types=1);
namespace Console\Helpers;
use Console\Helpers\Tools;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;

/**
 * Supports unit test related console commands.
 */
class Test {
    /**
     * The template for a new TestCase class.
     *
     * @param string $testName The name of the TestCase class.
     * @param string $type The type of test.
     * @return string The contents for the new TestCase class.
     */
    public static function makeTest(string $testName, $type): string {
        return '<?php
namespace Tests\\'.$type.';
use PHPUnit\Framework\TestCase;

/**
 * Unit tests
 */
class '.$testName.' extends TestCase {
    
}
';
    }

    /**
     * Runs the unit test contained in the TestCase class
     *
     * @param InputInterface $input Input obtained from the console used to 
     * set name of unit test we want to run.
     * @param OutputInterface $output The results of the test.
     * @return int A value that indicates success, invalid, or failure.
     */
    public static function runTest(InputInterface $input, OutputInterface $output): int {
        $testName = $input->getArgument('testname');
        $command = 'php vendor/bin/phpunit tests'.DS.'Unit'.DS.$testName.'.php';
        $output->writeln(Tools::border());
        $output->writeln(sprintf('Running command: '.$command));
        $output->writeln(Tools::border());
        $output->writeln(shell_exec($command));
        $output->writeln(Tools::border());
        return Command::SUCCESS;
    }
}