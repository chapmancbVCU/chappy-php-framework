<?php
declare(strict_types=1);
namespace Console\Helpers;

use Console\Helpers\ReactStubs;
use Console\Helpers\Tools;
use Core\Lib\Utilities\Str;
use Symfony\Component\Console\Command\Command;

class React {
    public const COMPONENT_PATH = CHAPPY_BASE_PATH.DS.'resources'.DS.'js'.DS.'components'.DS;
    public const PAGE_PATH = CHAPPY_BASE_PATH.DS.'resources'.DS.'js'.DS.'pages'.DS;
    public const UTILS_PATH = CHAPPY_BASE_PATH.DS.'resources'.DS.'js'.DS.'utils'.DS;
    
    /**
     * Generates a component with default export.
     *
     * @param string $componentName The name of the component.
     * @return string The contents of the component.
     */
    public static function defaultComponentTemplate(string $componentName): string {
        return 'import React from "react";
function '.$componentName.'() {

    return (
        <>
        
        </>
    );
}        
export default '.$componentName.';';
    }

    /**
     * Generates the home/Index.jsx component.
     *
     * @return int A value that indicates success, invalid, or failure.
     */
    public static function homeComponent(): int {
        $path = self::PAGE_PATH.'home'.DS;
        Tools::pathExists($path);

        return Tools::writeFile($path.'Index.jsx', ReactStubs::homeIndex(), 'Home.index');
    }

    /**
     * Generates a named export component.
     *
     * @param string $componentName The name of the component.
     * @return string The contents of the component.
     */
    public static function namedComponentTemplate(string $componentName): string {
        return 'import React from "react";
export const '.$componentName.' = () => {

    return (
        <>
        
        </>
    );
}';
    }

    /**
     * Generates component under 'resources/js/components'.
     *
     * @param string $componentName The name of the component.
     * @param bool $named Boolean flag to determine if named or default 
     * component is generated.
     * @return int A value that indicates success, invalid, or failure.
     */
    public static function makeComponent(string $componentName, bool $named = false): int {
        $componentPath = self::COMPONENT_PATH.Str::ucfirst($componentName).'.jsx';
        $content = ($named) 
            ? self::namedComponentTemplate($componentName) 
            : self::defaultComponentTemplate($componentName);
        return Tools::writeFile($componentPath, $content, 'React component');
    }

    /**
     * Generates a page view component under 'resources/js/pages/<group_name>'.
     *
     * @param string $filePath Full path for new page component.
     * @param string $pageName Name of the page component.
     * @param boolean $named Boolean flag to determine if named or default 
     * component is generated.
     * @return int A value that indicates success, invalid, or failure.
     */
    public static function makePage(string $filePath, string $pageName, bool $named = false): int {
        $content = ($named)
            ? self::namedComponentTemplate($pageName) 
            : self::defaultComponentTemplate($pageName);

        return Tools::writeFile($filePath, $content, 'React page');
    }

    /**
     * Generates a JavaScript file under 'resources/js/utils' to be 
     * use as support file with React.js front-end.
     *
     * @param string $utilityName The name of the utility file.
     * @return int A value that indicates success, invalid, or failure.
     */
    public static function makeUtility(string $utilityName): int {
        Tools::pathExists(self::UTILS_PATH);
        $filePath = self::UTILS_PATH.$utilityName.'.js';
        return Tools::writeFile($filePath, '', 'JavaScript utility');
    }
}