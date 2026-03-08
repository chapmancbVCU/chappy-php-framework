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
     * 
     * @param string $field The reference to the value to be validated.
     * @param string $message The message to present to the user.
     * @param InputInterface $input The Symfony InputInterface object.
     * @param OutputInterface $output The Symfony OutputInterface object.
     * @param string $fieldName The name of the field being validated.
     * @return void
     */
    public static function argOptionValidate(
        string &$field,
        string $message, 
        InputInterface $input, 
        OutputInterface $output, 
        string $fieldName = '',
        array $validators = []
    ): void {

        $object = self::getInstance($fieldName);
        self::parseValidators($object, $validators);
        $object->required()
                ->noSpecialChars()
                ->alpha()
                ->notReservedKeyword();

        if(!$object->validate($field)) {
            $field = self::prompt($message, $input, $output, $fieldName, $validators);
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
     * @param string $fieldName The name of the field being validated.
     * @param array $validators An array of additional validators.
     * @return string The user response
     */
    public static function prompt(
        string $message, 
        InputInterface $input, 
        OutputInterface $output, 
        string $fieldName = '',
        array $validators = []
    ): string {
        $question = new FrameworkQuestion($input, $output);
        self::parseValidators($question, $validators);

        return $question->required()
            ->noSpecialChars()
            ->fieldName($fieldName)
            ->alpha()
            ->notReservedKeyword()
            ->ask($message);
    }

    private static function parseValidators(object $object, array $validators) {
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