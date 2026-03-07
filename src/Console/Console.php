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
     * Asks user question about file to be created.
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
        int $max = 50
    ): string {

        $question = new FrameworkQuestion($input, $output);
        return $question->required()
            ->noSpecialChars()
            ->fieldName($fieldName)
            ->alpha()
            ->notReservedKeyword()
            ->max($max)
            ->ask($message);
    }

    public static function argOptionValidate(
        string $field,
        string $message, 
        InputInterface $input, 
        OutputInterface $output, 
        string $fieldName = '',
        int $max = 50
    ) {
        $isValidated = self::getInstance($field)
                ->required()
                ->noSpecialChars()
                ->alpha()
                ->notReservedKeyword()
                ->max($max)
                ->validate($field);
        if(!$isValidated) {
            $field = self::prompt($message, $input, $output, $fieldName, $max);
        }
        return $field;
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