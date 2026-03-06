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
 * Supports ability to create views, layouts, components, and menu_acl 
 * json files.
 */
class View extends Console {
  /** Path to components. */
    public const COMPONENTS_PATH = ROOT.DS.'resources'.DS.'views'.DS.'components'.DS;
    /** Path to CSS files. */
    public const CSS_PATH = ROOT.DS.'resources'.DS.'css'.DS;
    /** Path to layout files. */
    public const LAYOUT_PATH = ROOT.DS.'resources'.DS.'views'.DS.'layouts'.DS;
    /** Path to view files. */
    public const VIEW_PATH = ROOT.DS.'resources'.DS.'views'.DS;
    /** Path to widget files. */
    public const WIDGET_PATH = self::VIEW_PATH.'widgets'.DS;
    
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
            return View::makeCardComponent($componentName);
        } else if($form && !$card && !$table) {
            return View::makeFormComponent(
                $componentName,
                self::formMethod($input, $output),
                self::enctype($input, $output)
            );
        } else if($table && !$card && !$form) {
            return View::makeTableComponent($componentName);
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
     * Generates a new CSS file.
     *
     * @param string $fileName The name of the CSS file.
     * @return int A value that indicates success, invalid, or failure.
     */
    public static function makeCSS(string $fileName): int {
        return Tools::writeFile(
            self::CSS_PATH.Str::lcfirst($fileName).".css", 
            '', 
            "CSS file '$fileName.css'"
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
     * Generates a new layout file.
     *
     * @param string $layoutName The name of the layout.
     * @param string $menuName The name of the menu to be used.
     * @return int A value that indicates success, invalid, or failure.
     */
    public static function makeLayout(string $layoutName, string $menuName = 'main'): int {
        $layoutPath = self::LAYOUT_PATH.Str::lcfirst($layoutName).".php";
        return Tools::writeFile(
            $layoutPath, 
            ViewStubs::layout($menuName), 
            'Layout'
        );
    }

    /**
     * Generates a new menu file.
     *
     * @param string $input The name of the menu.
     * @return int A value that indicates success, invalid, or failure.
     */
    public static function makeMenu(string $menuName): int {
        return Tools::writeFile(
            self::COMPONENTS_PATH.Str::lower($menuName)."_menu.php",
            ViewStubs::menu($menuName),
            "Menu file"
        );
    }

    /**
     * Generates a new menu_acl file.
     *
     * @param InputInterface $input The Symfony InputInterface object.
     * @param OutputInterface $output The Symfony OutputInterface object.
     * @return int A value that indicates success, invalid, or failure.
     */
    public static function makeMenuAcl(InputInterface $input, OutputInterface $output): int {
        $menuName = $input->getArgument('acl-name');
        if(!$menuName) {
            $question = new FrameworkQuestion($input, $output);
            $message = "Enter name for new acl file.";
            $menuName = $question->ask($message);
        } 

        $isValidated = self::getInstance('acl-name')
            ->required()
            ->noSpecialChars()
            ->alpha()
            ->notReservedKeyword()
            ->max(50)
            ->validate($menuName);
        if(!$isValidated) return Command::FAILURE;

        return Tools::writeFile(
            ROOT.DS.'app'.DS.Str::lower($menuName)."_menu_acl.json",
            ViewStubs::menuAcl(Str::ucfirst($menuName)),
            "The menu_acl json"
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
    
    /**
     * Writes template for view to a file.
     *
     * @param string $file The path to the view file.
     * @return int A value that indicates success, invalid, or failure.
     */
    public static function makeView(string $filePath): int {
        return Tools::writeFile($filePath, ViewStubs::viewContent(), "View file");
    }

    /**
     * Writes new file for widget.
     *
     * @param string $filePath The path to the widget file.
     * @return int A value that indicates success, invalid, or failure.
     */
    public static function makeWidget(string $filePath): int {
        return Tools::writeFile($filePath, '', "Widget file");
    }
}
