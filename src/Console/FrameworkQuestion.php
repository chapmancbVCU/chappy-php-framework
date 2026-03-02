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

/**
 * Supports questions through the command line interface.
 */
class FrameworkQuestion {
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
     * Array of validator callbacks.
     *
     * @var array
     */
    protected array $validators = [];

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
     * Enforce rule where input must contain only alphabetic characters.
     *
     * @return static
     */
    public function alpha(): static {
        return $this->setValidator(function($response):void {
            if(!preg_match('/[a-z]/', $response) || !preg_match('/[A-Z]/', $response)) {
                throw new FrameworkRuntimeException("Input must contain only alphabetic characters.");
            }
        });
    }

    /**
     * Asks the user a question.  This function supports secret input 
     * and autocomplete.  An exception is thrown when both $secret and 
     * $anticipate are true.
     *
     * @param string $message The question to ask.
     * @param bool $anticipate Enables autocomplete when set to true.
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
        bool $anticipate = false, 
        array $suggestions = [],
        string|bool|int|float|null $default = null
    ): mixed {

        if($anticipate && $this->secret) {
            throw new FrameworkException('Cannot have $anticipate and $suggestion set to true simultaneously.');
        }

        if($anticipate && empty($suggestions)) {
            throw new FrameworkException('The $suggestions array cannot be empty when $anticipate = true.');
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
        
        if($anticipate) $question->setAutocompleterValues($suggestions);
        if(!$this->trimmable) $question->setTrimmable(false);

        if($this->timeout) {
            $question->setTimeout($this->timeout);
            try {
                return $this->promptUser($question, $message);
            } catch (MissingInputException $e) {
                console_error("No input received within timeout period.");
                return null;
            }
        }
        return $this->promptUser($question);
    }

    /**
     * Ensures input is between within a certain range in length.
     * @param int $minRule The minimum allowed size for input.
     * @param int $maxRule The maximum allowed size for input.
     * @return static
     */
    public function between(int $minRule, int $maxRule): static {
        return $this->setValidator(function($response) use ($minRule, $maxRule): void {
            if((strlen($response) < $minRule) || (strlen($response) > $maxRule)) {
                throw new FrameworkRuntimeException(
                    "This field must be between {$minRule} and {$maxRule} characters in length."
                );
            } 
        });
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
     * Disables trimmable.  Default value is false.
     *
     * @param bool $trimmable Disables trimmable if false is provided.  Otherwise, 
     * trimmable is set to true.
     * @return static
     */
    public function disableTrimmable(bool $trimmable = false): static {
        $this->trimmable = $trimmable;
        return $this;
    }

    /**
     * Ensures input is a valid E-mail address.
     *
     * @return static
     */
    public function email(): static {
        return $this->setValidator(function($response): void {
            if(!filter_var($response, FILTER_VALIDATE_EMAIL)) {
                throw new FrameworkRuntimeException("Input must match valid E-mail format.");
            }
        });
    }

    /**
     * Enforce rule where input must be an integer.
     *
     * @return static
     */
    public function integer(): static {
        return $this->setValidator(function($response): void {
            if(!is_numeric($response) || str_contains($response, '.')) {
                throw new FrameworkRuntimeException("Input must be an integer.");
            }
        });
    }

    /**
     * Enforces rule when input must contain at least one lower case character.
     *
     * @return static
     */
    public function lower(): static {
        return $this->setValidator(function($response):void {
            if(!preg_match('/[a-z]/', $response)) {
                throw new FrameworkRuntimeException("Input must contain at least one lower case character.");
            }
        });
    }

    /**
     * Ensures input meets requirements for maximum allowable length.
     *
     * @param int $maxRule The maximum allowed size for input.
     * @return static
     */
    public function max(int $maxRule): static {
        return $this->setValidator(function($response) use ($maxRule): void {
            if(strlen($response) > $maxRule) {
                throw new FrameworkRuntimeException("This field must be less than {$maxRule} characters in length.");
            } 
        });
    }

    /**
     * Ensures input meets requirements for minimum allowable length.
     *
     * @param int $minRule The minimum allowed size for input.
     * @return static
     */
    public function min(int $minRule): static {
        return $this->setValidator(function($response) use ($minRule): void {
            if(strlen($response) < $minRule) {
                throw new FrameworkRuntimeException("This field must be at least {$minRule} characters in length.");
            } 
        });
    }

    /**
     * Enforces rule when input must contain no special characters.
     *
     * @return static
     */
    public function noSpecialChars(): static {
        return $this->setValidator(function($response):void {
            if((preg_match('/[^a-zA-Z0-9]/', $response) == 1) && (preg_match('/\s/', $response) == 0)) {
                throw new FrameworkRuntimeException("Input must contain no special characters.");
            }
        });
    }

    /**
     * Enforces rule when input must contain at least one numeric character.
     *
     * @return static
     */
    public function number(): static {
        return $this->setValidator(function($response):void {
            if(!preg_match('/[0-9]/', $response)) {
                throw new FrameworkRuntimeException("Input must contain at least one numeric character.");
            }
        });
    }

    /**
     * Enforce rule where input must contain only numeric characters.
     *
     * @return static
     */
    public function numeric(): static {
        return $this->setValidator(function($response): void {
            if(!is_numeric($response)) {
                throw new FrameworkRuntimeException("Input must consist of only numeric characters.");
            }
        });
    }

    /**
     * Ensures required input is entered.
     *
     * @return static
     */
    public function required(): static {
        return $this->setValidator(function($response): void {
            if($response === '' || $response === null) {
                throw new FrameworkRuntimeException('This field is required.');
            }
        });
    }

    /**
     * Helper function for asking a question.
     *
     * @param Question $question Represents a question.
     * @return mixed The user answer.
     */
    protected function promptUser(Question $question,): mixed {
        $response = $this->helper->ask($this->input, $this->output, $question);
        dump(get_class($question));
        $this->validate($response);
        $this->secret(false);
        $this->timeout();
        dump(strlen($response));
        $this->disableTrimmable(true);
        return $response;
    }

    /**
     * Sets input as hidden.
     *
     * @param boolean $secret Default value is true.  When true is passed then 
     * input is hidden.  Otherwise, output is not hidden.
     * @return static
     */
    public function secret(bool $secret = true): static {
        $this->secret = $secret;
        return $this;
    }

    /**
     * Adds validator to array of validators to be used.
     *
     * @param callable $validator The anonymous function for a validator.
     * @return static
     */
    public function setValidator(callable $validator): static {
        $this->validators[] = $validator;
        return $this;
    }

    /**
     * Enforces rule when input must contain at least one special character.
     *
     * @return static
     */
    public function special(): static {
        return $this->setValidator(function($response):void {
            if(!(preg_match('/[^a-zA-Z0-9]/', $response) == 1) || (!preg_match('/\s/', $response) == 0)) {
                throw new FrameworkRuntimeException("Input must contain at least one special character.");
            }
        });
    }

    /**
     * Sets timeout for input.
     *
     * @param int|null $timeout The time in seconds before prompt timeout.  
     * Default value is null.
     * @return static
     */
    public function timeout(?int $timeout = null): static {
        $this->timeout = $timeout;
        return $this;
    }

    /**
     * Enforces rule when input must contain at least one lower case character.
     *
     * @return static
     */
    public function upper(): static {
        return $this->setValidator(function($response):void {
            if(!preg_match('/[A-Z]/', $response)) {
                throw new FrameworkRuntimeException("Input must contain at least one upper case character.");
            }
        });
    }

    /**
     * Calls validator callbacks.  This function also ensures validators 
     * don't bleed into next question if instance is reused.
     *
     * @param mixed $response The user answer.
     * @return void
     */
    protected function validate(mixed $response): void {
        foreach($this->validators as $callback) {
            $callback($response);
        }

        $this->validators = [];
    }
}