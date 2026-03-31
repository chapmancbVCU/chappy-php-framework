<?php
declare(strict_types=1);
namespace Console\Helpers;

use Console\Console;
use Core\Lib\Utilities\Str;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Supports ability to create views, layouts, and menu_acl 
 * json files.
 */
class View extends Console {
  
    /** Path to CSS files. */
    public const CSS_PATH = ROOT.DS.'resources'.DS.'css'.DS;
    /** Path to layout files. */
    public const LAYOUT_PATH = ROOT.DS.'resources'.DS.'views'.DS.'layouts'.DS;

    /**
     * Message presented to user when asking for name of a new layout file.
     */
    public const LAYOUT_PROMPT = "Enter name for new layout";

    /** Path to view files. */
    public const VIEW_PATH = ROOT.DS.'resources'.DS.'views'.DS;
    /** Path to widget files. */
    public const WIDGET_PATH = self::VIEW_PATH.'widgets'.DS;

    /**
     * Prompts user for name of new layout file.
     *
     * @param InputInterface $input The Symfony InputInterface object.
     * @param OutputInterface $output The Symfony OutputInterface object.
     * @return string The name of the new layout.
     */
    public static function layoutNamePrompt(InputInterface $input, OutputInterface $output): string {
        return self::prompt(self::LAYOUT_PROMPT, $input, $output, ['max:50', 'fieldName:layout-name']);
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
            Component::COMPONENTS_PATH.Str::lower($menuName)."_menu.php",
            ViewStubs::menu($menuName),
            "Menu file"
        );
    }

    /**
     * Generates a new menu_acl file.
     *
     * @param string $menuName The name for the new menu acl file.
     * @return int A value that indicates success, invalid, or failure.
     */
    public static function makeMenuAcl(string $menuName): int {
        return Tools::writeFile(
            ROOT.DS.'app'.DS.Str::lower($menuName)."_menu_acl.json",
            ViewStubs::menuAcl(Str::ucfirst($menuName)),
            "The menu_acl json"
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

    /**
     * Sets menu file name when layout-name argument is provided.
     *
     * @param string $layoutName The name of the layout.
     * @param mixed $menu The menu option.
     * @return string The name of the menu to be used by the layout.
     */
    public static function menu(string $layoutName, mixed $menu): string {
        if(!$menu) $menuName = 'main';
        else {
            $menuName = $layoutName;
            self::makeMenu($menuName);
        }
        return $menuName;
    }

    /**
     * Generates a menu if --menu flag is created with layout-name.  If layout-name 
     * is not provided the user is asked if they want to create a new menu that is 
     * associated with the layout.  If the user answers no then the main is returned.  
     * Otherwise, a string matching the name of the layout is returned.
     *
     * @param string $layoutName The name of the layout.
     * @param mixed $menu The --menu flag.
     * @param InputInterface $input The Symfony InputInterface object.
     * @param OutputInterface $output The Symfony OutputInterface object.
     * @return string The name of the menu to be associated with the layout.
     */
    public static function menuConfirm(
        string $layoutName, 
        mixed $menu, 
        InputInterface $input, 
        OutputInterface $output
    ): string {
        if($menu) {
            self::makeMenu($layoutName);
            return $layoutName;
        }
        $message = "Do you want to create a menu specific to this layout? (y/n)";
        if(self::confirm($message, $input, $output)) {
            self::makeMenu($layoutName);
            return $layoutName;
        }

        return 'main';
    }

    /**
     * Generates a new menu acl file if --menu-acl flag is provided.  If no flag 
     * is set then the user is asked if they want to generate a menu acl file 
     * associated with the layout.
     *
     * @param string $layoutName The name of the layout.
     * @param mixed $menuACL The --menu-acl flag.
     * @param InputInterface $input The Symfony InputInterface object.
     * @param OutputInterface $output The Symfony OutputInterface object.
     * @return void
     */
    public static function menuAclConfirm(
        string $layoutName, 
        mixed $menuACL, 
        InputInterface $input, 
        OutputInterface $output
    ): void {
        if($menuACL) {
            self::makeMenuAcl($layoutName);
            return;
        }
        $message = "Do you want to create a menu-acl file? (y/n)";
        if(self::confirm($message, $input, $output)) {
            self::makeMenuAcl($layoutName);
        }
    }
}
