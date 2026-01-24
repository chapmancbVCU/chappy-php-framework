<?php
declare(strict_types=1);
namespace Console\Helpers;

use Core\Lib\Utilities\Str;
/**
 * Supports ability to create views, layouts, components, and menu_acl 
 * json files.
 */
class View {
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
     * @param string $menuName The name of the menu_acl file.
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
