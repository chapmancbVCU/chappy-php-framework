<?php
declare(strict_types=1);
namespace Console;

use Core\Exceptions\FrameworkException;
use Core\Exceptions\FrameworkRuntimeException;
use Symfony\Component\Console\Exception\MissingInputException;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Console\HasValidators;

/**
 * Supports questions through the command line interface.
 */
class FrameworkQuestion {
    use HasValidators;

    protected bool $anticipate = false;

    /**
     * Instance of QuestionHelper class.
     *
     * @var QuestionHelper
     */
    protected QuestionHelper $helper;

    /**
     * InputInterface instance created when parent command is ran.
     *
     * @var InputInterface
     */
    protected InputInterface $input;

    /**
     * OutputInterface instance created when parent command is ran.
     *
     * @var OutputInterface
     */
    protected OutputInterface $output;

    /**
     * Determines whether or not input is hidden.
     *
     * @var bool
     */
    protected bool $secret = false;

    /**
     * Timeout for prompt in seconds.
     *
     * @var integer|null
     */
    protected ?int $timeout = null;

    /**
     * Sets mode for trimmable.
     *
     * @var bool
     */
    protected bool $trimmable = true;

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
     * Used to turn on anticipate mode.  
     *
     * @param boolean|array $anticipate Anticipate mode is turned on when true is 
     * passed.  Otherwise, it is disabled.  Default value is true.
     * @return static
     */
    public function anticipate(bool|array $anticipate = true): static {
        if(is_array($anticipate) && sizeof($anticipate) == 0) $anticipate = true;
        
        $this->anticipate = $anticipate;
        return $this;
    }

    /**
     * Asks the user a question.  This function supports secret input 
     * and autocomplete.  An exception is thrown when both $secret and 
     * $anticipate are true.
     *
     * @param string $message The question to ask.
     * @param array $suggestions An array of suggestions for when $anticipate 
     * is set to true.  An exception is thrown if this array is empty and 
     * $anticipate = true.
     * @param string|bool|int|float|null $default The default value if the 
     * user does not provide an answer.
     * @return mixed The user answer.  Null is returned if there is a timeout 
     * set and input is not received within set amount of time.
     * 
     * @throws FrameworkException An an exception is thrown for the following 
     * two cases:
     * 1) Both $secret = true and $anticipate = true
     * 2) $suggestions is empty and $anticipate = true.
     */
    public function ask(
        string $message, 
        array $suggestions = [],
        string|bool|int|float|null $default = null
    ): mixed {

        if($this->anticipate && $this->secret) {
            throw new FrameworkException('Cannot have $anticipate and $suggestion set to true simultaneously.');
        }

        if($this->anticipate && empty($suggestions)) {
            throw new FrameworkException('The $suggestions array cannot be empty when anticipate is disabled.');
        }

        $this->output->writeln('');
        $question = new Question(
            "<fg=green> {$message} <fg=cyan>></> ",
            $default
        );

        if($this->secret) {
            $question->setHidden(true);
            $question->setHiddenFallback(false);
        }
        
        if($this->anticipate) $question->setAutocompleterValues($suggestions);
        if(!$this->trimmable) $question->setTrimmable(false);

        if($this->timeout) {
            $question->setTimeout($this->timeout);
            try {
                return $this->promptUser($question, $message);
            } catch (MissingInputException $e) {
                console_error($e->getMessage());
                die;
            }
        }
        return $this->promptUser($question);
    }

    /**
     * Checks state of anticipate, secret, and trimmable modes.  If used for 
     * for certain questions then the user is alerted before being given 
     * the chance to answer those questions.
     *
     * @return void
     */
    protected function checkModes(): void {
        if($this->anticipate) {
            throw new FrameworkRuntimeException("Anticipate mode not available for this question type.");
        }

        if($this->secret) {
            throw new FrameworkRuntimeException("Secret mode not available for this question type.");
        }

        if(!$this->trimmable) {
            throw new FrameworkRuntimeException("Trimmable mode cannot be disabled for this question type.");
        }
    }

    /**
     * Asks the user a question where there is a choice to be made.
     *
     * @param string $message The question to ask.
     * @param array $choices An array of choices.
     * @param string|bool|int|float|null $default The default value if the 
     * user does not provide an answer.
     * @return mixed The user answer.
     */
    public function choice(string $message, array $choices, string|bool|int|float|null $default = null): mixed {
        $this->checkModes();
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
     * Asks a user to confirm based on question asked.
     *
     * @param string $message The question to ask.  It is advised to phrase it 
     * such that the user knows to answer y or n.
     * @param string|bool|int|float|null $default The default value if the 
     * user does not provide an answer.
     * @return mixed The user answer.
     */
    public function confirm(string $message, string|bool|int|float|null $default = true): mixed {
        $this->checkModes();
        $this->output->writeln('');
        $question = new ConfirmationQuestion(
            "<fg=green> {$message} <fg=cyan>></> ",
            $default
        );
        
        return $this->promptUser($question);
    }

    /**
     * Disables trimmable.  Default value is false.
     *
     * @param bool|array $trimmable Disables trimmable if false is provided.  Otherwise, 
     * trimmable is set to true.
     * @return static
     */
    public function disableTrimmable(bool|array $trimmable = false): static {
        if(is_array($trimmable) && sizeof($trimmable) == 0) $trimmable = false;
        $this->trimmable = $trimmable;
        return $this;
    }

    /**
     * Helper function for asking a question.
     *
     * @param Question $question Represents a question.
     * @return mixed The user answer.
     */
    protected function promptUser(Question $question): mixed {
        $validated = false;
        do {
            $response = $this->helper->ask($this->input, $this->output, $question);
            $validated = $this->validate($response);
        } while(!$validated);

        $this->anticipate(false);
        $this->disableTrimmable(true);
        $this->secret(false);
        $this->timeout();
        return $response;
    }

    /**
     * Sets input as hidden.
     *
     * @param boolean|array $secret Default value is true.  When true is passed then 
     * input is hidden.  Otherwise, output is not hidden.
     * @return static
     */
    public function secret(bool|array $secret = true): static {
        if(is_array($secret) && sizeof($secret) == 0) $secret = true;
        $this->secret = $secret;
        return $this;
    }

    /**
     * Sets timeout for input.
     *
     * @param int|array $timeout The time in seconds before prompt timeout.  
     * Default value is null.
     * @return static
     */
    public function timeout(mixed $timeout = null): static {
        if(is_array($timeout) && sizeof($timeout) == 0) $this->timeout = (int)$timeout[0];
        else $this->timeout = (int)$timeout;
        return $this;
    }
}