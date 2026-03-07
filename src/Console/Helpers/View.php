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
 * Supports ability to create views, layouts, and menu_acl 
 * json files.
 */
class View extends Console {
  
    /** Path to CSS files. */
    public const CSS_PATH = ROOT.DS.'resources'.DS.'css'.DS;
    /** Path to layout files. */
    public const LAYOUT_PATH = ROOT.DS.'resources'.DS.'views'.DS.'layouts'.DS;
    /** Path to view files. */
    public const VIEW_PATH = ROOT.DS.'resources'.DS.'views'.DS;
    /** Path to widget files. */
    public const WIDGET_PATH = self::VIEW_PATH.'widgets'.DS;

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
}
