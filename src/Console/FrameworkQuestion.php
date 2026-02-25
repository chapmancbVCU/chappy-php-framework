<?php
declare(strict_types=1);
namespace Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
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

    /**
     * Asks the user a question.
     *
     * @param string $message The question to ask.
     * @return mixed The user answer.
     */
    public function ask(string $message): mixed {
        $this->output->writeln('');
        $question = new Question(
            "<fg=green> {$message} <fg=cyan>></> ",
            false
        );
        
        return $this->helper->ask($this->input, $this->output, $question);
    }

    /**
     * Asks the user a question where there is a choice to be made.
     *
     * @param string $message The question to ask.
     * @return mixed The user answer.
     */
    public function choice(string $message, array $choices): mixed {
        $this->output->writeln('');
        $question = new ChoiceQuestion(
            "<fg=green> {$message} </> ",
            $choices,
            false
        );
        
        $question->setErrorMessage(" Option %s is invalid.");
        return $this->helper->ask($this->input, $this->output, $question);
    }
    
    /**
     * Asks a use to confirm based on question asked.
     *
     * @param string $message The question to ask.  It is advised to phrase it 
     * such that the user knows to answer y or n.
     * @return mixed The user answer.
     */
    public function confirm(string $message): mixed {
        $this->output->writeln('');
        $question = new ConfirmationQuestion(
            "<fg=green> {$message} <fg=cyan>></> ",
            false
        );
        
        return $this->helper->ask($this->input, $this->output, $question);
    }
}