<?php
declare(strict_types=1);

namespace Console;

use Console\HasValidators;
use Core\Exceptions\FrameworkException;
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
     * 
     * @param string $field The reference to the value to be validated.
     * @param string $message The message to present to the user.
     * @param InputInterface $input The Symfony InputInterface object.
     * @param OutputInterface $output The Symfony OutputInterface object.
     * @return void
     */
    public static function argOptionValidate(
        string &$field,
        string $message, 
        InputInterface $input, 
        OutputInterface $output, 
        array $validators = []
    ): void {

        $object = self::getInstance();
        self::parseAttributes($object, $validators);
        $object->required()
                ->noSpecialChars()
                ->alpha()
                ->notReservedKeyword();

        if(!$object->validate($field)) {
            $field = self::prompt($message, $input, $output, $validators);
        }
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

    /**
     * Asks user question about file to be created.
     *
     * Validates the following conditions:
     * 1) required
     * 2) noSpecialChars
     * 3) alpha
     * 4) notReservedKeyword
     * 
     * @param string $message The message to present to the user.
     * @param InputInterface $input The Symfony InputInterface object.
     * @param OutputInterface $output The Symfony OutputInterface object.
     * @param array $attributes An array of additional validators.
     * @param array $suggestions An array of suggestions for when $anticipate 
     * is set to true.  An exception is thrown if this array is empty and 
     * $anticipate = true.
     * @param string|bool|int|float|null $default The default value if the 
     * user does not provide an answer.
     * @return mixed The user response
     */
    public static function prompt(
        string $message, 
        InputInterface $input, 
        OutputInterface $output, 
        array $attributes = [],
        array $suggestions = [],
        string|bool|int|float|null $default = null
    ): string {
        $question = new FrameworkQuestion($input, $output);
        self::parseAttributes($question, $attributes);

        $response = $question->required()
            ->noSpecialChars()
            ->alpha()
            ->notReservedKeyword()
            ->ask($message, $suggestions, $default);

        if(!$response) die;
        return $response;
    }

    /**
     * Parse array containing additional validators or attributes for FrameworkQuestion 
     * as strings along with any additional parameters that maybe expected.
     *
     * @param object $object The instance of a class using the HasValidators 
     * trait.
     * @param array $validators An array of validators.  Any additional 
     * parameters must be separated with a ":".
     * @return void
     */
    protected static function parseAttributes(object $object, array $validators): void {
        foreach($validators as $validator) {
            $arr = explode(":", $validator);
            $method = $arr[0];
            $params[] = array_slice($arr, 1);
            if(method_exists($object, $method)) {
                call_user_func_array([$object, $method], $params);
            } else {
                console_error("Validator rule does not exist");
            }
            $arr = [];
            $params = [];
        }
    }
}