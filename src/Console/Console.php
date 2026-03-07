<?php
declare(strict_types=1);

namespace Console;

use Console\HasValidators;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class that can be extended by helpers when validators needs to be used.
 */
class Console {
    use HasValidators;

    /**
     * Creates new instance of Console class.
     *
     * @param string $fieldName The name of the field to be validated.
     */
    public function __construct(string $fieldName = "") {
        $this->fieldName($fieldName);
    }

    /**
     * Undocumented function
     *
     * @param string $message The message to present to the user.
     * @param array $choices An array of choices.
     * @param InputInterface $input The Symfony InputInterface object.
     * @param OutputInterface $output The Symfony OutputInterface object.
     * @param string|boolean|integer|float|null|null $default he default value if the 
     * user does not provide an answer.
     * @return mixed The user answer.
     */
    public static function choice(
        string $message, 
        array $choices, 
        InputInterface $input, 
        OutputInterface $output,
        string|bool|int|float|null $default = null, 
    ): mixed {
        $question = new FrameworkQuestion($input, $output);
        return $question->choice($message, $choices, $default);
    }

    /**
     * Validates argument and option input.  If validation fails then the 
     * user is asked to enter a new value.
     *
     * Validates the following conditions:
     * 1) required
     * 2) noSpecialChars
     * 3) alpha
     * 4) notReservedKeyword
     * 5) max
     * 6) different
     * 
     * @param string $field The reference to the value to be validated.
     * @param string $message The message to present to the user.
     * @param InputInterface $input The Symfony InputInterface object.
     * @param OutputInterface $output The Symfony OutputInterface object.
     * @param string $fieldName The name of the field being validated.
     * @param int $max Maximum allowed size for file name.
     * @return void
     */
    public static function argOptionValidate(
        string &$field,
        string $message, 
        InputInterface $input, 
        OutputInterface $output, 
        string $fieldName = '',
        int $max = 50,
        ?string $different = null
    ): void {

        $object = self::getInstance($fieldName);
        if($different) $object->different($different);
        $object->required()
                ->noSpecialChars()
                ->alpha()
                ->notReservedKeyword()
                ->max($max);
        if(!$object->validate($field)) {
            $field = self::prompt($message, $input, $output, $fieldName, $max, $different);
        }
    }

    /**
     * Asks user question about file to be created.
     *
     * Validates the following conditions:
     * 1) required
     * 2) noSpecialChars
     * 3) alpha
     * 4) notReservedKeyword
     * 5) max
     * 6) different
     * 
     * @param string $message The message to present to the user.
     * @param InputInterface $input The Symfony InputInterface object.
     * @param OutputInterface $output The Symfony OutputInterface object.
     * @param string $fieldName The name of the field being validated.
     * @param int $max Maximum allowed size for file name.
     * @return string The user response
     */
    public static function prompt(
        string $message, 
        InputInterface $input, 
        OutputInterface $output, 
        string $fieldName = '',
        int $max = 50,
        ?string $different = null
    ): string {

        $question = new FrameworkQuestion($input, $output);
        if($different) {
            $question->different($different);
        }
        return $question->required()
            ->noSpecialChars()
            ->fieldName($fieldName)
            ->alpha()
            ->notReservedKeyword()
            ->max($max)
            ->ask($message);
    }

    /**
     * Returns instance of this or child helper class.
     *
     * @param string $fieldName The name of the field to be validated.
     * @return static
     */
    public static function getInstance(string $fieldName = ""): static {
        return new static($fieldName);
    }
}