<?php
declare(strict_types=1);
namespace Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

final class FrameworkQuestion {
    public $helper;
    private InputInterface $input;
    private OutputInterface $output;

    public function __construct(InputInterface $input, OutputInterface $output) {
        $this->helper = new QuestionHelper();
        $this->input = $input;
        $this->output = $output;

        if(!$this->helper) {
            console_error("Helper could not be instantiated.");
        }
    }

    public function ask(string $message) {
        $question = new ConfirmationQuestion(
            "<fg=green> {$message} <fg=cyan>></> ",
            false
        );
        
        return $this->helper->ask($this->input, $this->output, $question);
    }
}