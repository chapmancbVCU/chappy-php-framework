<?php
declare(strict_types=1);
namespace Chappy\Console\Helpers;

use Console\Helpers\Tools;
use Core\Lib\Utilities\Str;

class React {
    public const COMPONENT_PATH = CHAPPY_BASE_PATH.DS.'resources'.DS.'js'.DS.'components'.DS;
    public const PAGE_PATH = CHAPPY_BASE_PATH.DS.'resources'.DS.'js'.DS.'pages'.DS;
    /**
     * Generates a component with default export.
     *
     * @param string $componentName The name of the component.
     * @return string The contents of the component.
     */
    public static function defaultComponentTemplate(string $componentName): string {
        return 'import React from "react"
function '.$componentName.'() {

    return (
        <>
        
        </>
    );
}        
export default '.$componentName.';';
    }

    /**
     * Generates a named export component.
     *
     * @param string $componentName The name of the component.
     * @return string The contents of the component.
     */
    public static function namedComponentTemplate(string $componentName): string {
        return 'import React from "react"
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
        $componentPath = self::COMPONENT_PATH.Str::lcfirst($componentName).'.jsx';
        $content = ($named) 
            ? self::namedComponentTemplate($componentName) 
            : self::defaultComponentTemplate($componentName);
        return Tools::writeFile($componentPath, $content, 'React component');
    }

    public static function makePage(string $filePath, string $pageName, bool $named = false): int {
        $content = ($named)
            ? self::namedComponentTemplate($pageName) 
            : self::defaultComponentTemplate($pageName);

        return Tools::writeFile($filePath, $content, 'React page');
    }
}