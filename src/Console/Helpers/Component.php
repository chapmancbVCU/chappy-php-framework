<?php
declare(strict_types=1);
namespace Console\Helpers;

use Console\Console;
use Console\FrameworkQuestion;
use Core\Lib\Utilities\Str;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Supports operations related to the creation of components.
 */
class Component extends Console {
    /** Path to components. */
    public const COMPONENTS_PATH = ROOT.DS.'resources'.DS.'views'.DS.'components'.DS;

    /**
     * Generate a component when argument is provided.
     *
     * @param string $componentName The name for the new component.
     * @param InputInterface $input The Symfony InputInterface object.
     * @param OutputInterface $output The Symfony OutputInterface object.
     * @return int A value that indicates success, invalid, or failure.
     */
    public static function componentContents(string $componentName, InputInterface $input, OutputInterface $output): int {
        $card = $input->getOption('card');
        $form = $input->getOption('form');
        $table = $input->getOption('table');

        if($card && !$form && !$table) {
            return self::makeCardComponent($componentName);
        } else if($form && !$card && !$table) {
            return self::makeFormComponent(
                $componentName,
                self::formMethod($input, $output),
                self::enctype($input, $output)
            );
        } else if($table && !$card && !$form) {
            return self::makeTableComponent($componentName);
        } else {
            console_warning("You can only choose one component type at a time.");
            return Command::FAILURE;
        }

        console_warning('No component type selected');
        return Command::FAILURE;
    }

    /**
     * Prompts user for information about enctype to be used in form component.
     *
     * @param InputInterface $input The Symfony InputInterface object.
     * @param OutputInterface $output The Symfony OutputInterface object.
     * @return string The user's response.
     */
    public static function enctype(InputInterface $input, OutputInterface $output): string {
        $question = new FrameworkQuestion($input, $output);
        $message = "Choose a the enctype";
        $response = $question->choice(
            $message,
            ['default: none', 'multipart/form-data', 'text/plain']
        );
        if($response == 'default: none') $response = '';
        return $response;
    }
    /**
     * Prompts user for information about which form method to be used in form component.
     *
     * @param InputInterface $input The Symfony InputInterface object.
     * @param OutputInterface $output The Symfony OutputInterface object.
     * @return string The user's response.
     */
    public static function formMethod(InputInterface $input, OutputInterface $output): string {
        $question = new FrameworkQuestion($input, $output);
        $message = "Choose a form method";
        return $question->choice(
            $message,
            ['get', 'post', 'put']
        );
    }

    /**
     * Writes card component to a file.
     *
     * @param string $componentName The name of the card component.
     * @return int A value that indicates success, invalid, or failure.
     */
    public static function makeCardComponent(string $componentName): int {
        return Tools::writeFile(
            self::COMPONENTS_PATH.Str::lower($componentName).".php",
            ViewStubs::cardComponent(),
            "Form component"
        );
    }

    /**
     * Writes form component to file.
     *
     * @param string $componentName The name of the form component.
     * @param string $method The method to be used.
     * @param string $encType The enctype to be used.
     * @return int A value that indicates success, invalid, or failure.
     */
    public static function makeFormComponent(string $componentName, string $method, string $encType): int {
        return Tools::writeFile(
            self::COMPONENTS_PATH.Str::lower($componentName).".php",
            ViewStubs::formComponent($method, $encType),
            "Form component"
        );
    }

    /**
     * Writes table component to a file.
     *
     * @param string $componentName The name of the table component.
     * @return int A value that indicates success, invalid, or failure.
     */
    public static function makeTableComponent(string $componentName): int {
        return Tools::writeFile(
            self::COMPONENTS_PATH.Str::lower($componentName).".php",
            ViewStubs::tableComponent(),
            "Table component"
        );
    }
}