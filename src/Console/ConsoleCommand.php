<?php
declare(strict_types=1);
namespace Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class ConsoleCommand extends Command {
    protected InputInterface $input;
    protected OutputInterface $output;
    //protected FrameworkQuestion $question;

    protected function execute(InputInterface $input, OutputInterface $output): int {
        $this->input = $input;
        $this->output = $output;
        //$this->question = new FrameworkQuestion($input, $output);
        return $this->handle();
    }

    abstract protected function handle(): int;

    protected function question(): FrameworkQuestion {
        return new FrameworkQuestion($this->input, $this->output);
    }
}