<?php
declare(strict_types=1);
namespace Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Console\ConsoleIO;

/**
 * Extends Command class and provides $input and $output as instance 
 * variables of their respective classes.  Also contains helper for returning 
 * an instance of the FrameworkQuestion class.
 */
abstract class ConsoleCommand extends Command {
    use ConsoleIO;
    
    /**
     * The Symfony InputInterface object.
     *
     * @var InputInterface
     */
    protected InputInterface $input;

    /**
     * The Symfony OutputInterface object.
     *
     * @var OutputInterface
     */
    protected OutputInterface $output;

    /**
     * Implements execute from parent class.
     *
     * @param InputInterface $input The Symfony InputInterface object.
     * @param OutputInterface $output The Symfony OutputInterface object.
     * @return int A value that indicates success, invalid, or failure.
     */
    protected function execute(InputInterface $input, OutputInterface $output): int {
        $this->input = $input;
        $this->output = $output;
        return $this->handle();
    }

    /**
     * Executes logic for command.
     *
     * @return int A value that indicates success, invalid, or failure.
     */
    abstract protected function handle(): int;

    /**
     * Returns instance of the FrameworkQuestion class.
     *
     * @return FrameworkQuestion The instance of the FrameworkQuestion class.
     */
    protected function question(): FrameworkQuestion {
        return new FrameworkQuestion($this->input, $this->output);
    }
}