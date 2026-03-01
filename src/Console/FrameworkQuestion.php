<?php
declare(strict_types=1);
namespace Console;

use Core\Exceptions\FrameworkException;
use Symfony\Component\Console\Exception\MissingInputException;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

/**
 * Supports questions through the command line interface.
 */
final class FrameworkQuestion {
    /**
     * Instance of QuestionHelper class.
     *
     * @var QuestionHelper
     */
    private QuestionHelper $helper;

    /**
     * InputInterface instance created when parent command is ran.
     *
     * @var InputInterface
     */
    private InputInterface $input;

    /**
     * OutputInterface instance created when parent command is ran.
     *
     * @var OutputInterface
     */
    private OutputInterface $output;

    /**
     * Creates instance of FrameworkQuestion class.
     *
     * @param InputInterface $input The Symfony InputInterface object.
     * @param OutputInterface $output The Symfony OutputInterface object.
     */
    public function __construct(InputInterface $input, OutputInterface $output) {
        $this->helper = new QuestionHelper();
        $this->input = $input;
        $this->output = $output;
    }

    /**
     * Asks the user a question.  This function supports secret input 
     * and autocomplete.  An exception is thrown when both $secret and 
     * $anticipate are true.
     *
     * @param string $message The question to ask.
     * @param bool $secret Hides input when asking sensitive questions when 
     * set to true.
     * @param bool $anticipate Enables autocomplete when set to true.
     * @param array $suggestions An array of suggestions for when $anticipate 
     * is set to true.  An exception is thrown if this array is empty and 
     * $anticipate = true.
     * @param bool $trimmable When true trimmable is enabled.  Otherwise, we 
     * do not trim user input.  Default value is true.
     * @param ?int $timeout Set timeout when input is expected within a certain 
     * amount of time.  Default value is null.
     * @param string|bool|int|float|null $default The default value if the 
     * user does not provide an answer.
     * @return mixed The user answer.  Null is returned if there is a timeout 
     * set and input is not received within set amount of time.
     * @throws FrameworkException An an exception is thrown for the following 
     * two cases:
     * 1) Both $secret = true and $anticipate = true
     * 2) $suggestions is empty and $anticipate = true.
     */
    public function ask(
        string $message, 
        bool $secret = false, 
        bool $anticipate = false, 
        array $suggestions = [],
        bool $trimmable = true,
        ?int $timeout = null,
        string|bool|int|float|null $default = null
    ): mixed {

        if($anticipate && $secret) {
            throw new FrameworkException('Cannot have $anticipate and $suggestion set to true simultaneously');
        }

        if($anticipate && empty($suggestions)) {
            throw new FrameworkException('The $suggestions array cannot be empty when $anticipate = true');
        }

        $this->output->writeln('');
        $question = new Question(
            "<fg=green> {$message} <fg=cyan>></> ",
            $default
        );

        if($secret) {
            $question->setHidden(true);
            $question->setHiddenFallback(false);
        }
        
        if($anticipate) $question->setAutocompleterValues($suggestions);
        if(!$trimmable) $question->setTrimmable(false);

        if($timeout) {
            $question->setTimeout($timeout);
            try {
                return $this->promptUser($question);
            } catch (MissingInputException $e) {
                console_error("No input received within timeout period");
                return null;
            }
        }
        return $this->promptUser($question);
    }

    /**
     * Asks the user a question where there is a choice to be made.
     *
     * @param string $message The question to ask.
     * @param string|bool|int|float|null $default The default value if the 
     * user does not provide an answer.
     * @return mixed The user answer.
     */
    public function choice(string $message, array $choices, string|bool|int|float|null $default = null): mixed {
        $this->output->writeln('');
        $question = new ChoiceQuestion(
            "<fg=green> {$message} <fg=cyan>></> ",
            $choices,
            $default
        );
        
        $question->setErrorMessage(" Option %s is invalid.");
        return $this->promptUser($question);
    }
    
    /**
     * Asks a use to confirm based on question asked.
     *
     * @param string $message The question to ask.  It is advised to phrase it 
     * such that the user knows to answer y or n.
     * @param string|bool|int|float|null $default The default value if the 
     * user does not provide an answer.
     * @return mixed The user answer.
     */
    public function confirm(string $message, string|bool|int|float|null $default = true): mixed {
        $this->output->writeln('');
        $question = new ConfirmationQuestion(
            "<fg=green> {$message} <fg=cyan>></> ",
            $default
        );
        
        return $this->promptUser($question);
    }

    /**
     * Helper function for asking a question.
     *
     * @param Question $question Represents a question.
     * @return mixed The user answer.
     */
    private function promptUser(Question $question): mixed {
        return $this->helper->ask($this->input, $this->output, $question);
    }
}