<?php
declare(strict_types=1);
namespace Chappy\Console\Helpers;

use Console\Helpers\Tools;
use Core\Lib\Utilities\Str;
use Symfony\Component\Console\Command\Command;

class React {
    public const COMPONENT_PATH = CHAPPY_BASE_PATH.DS.'resources'.DS.'js'.DS.'components'.DS;
    
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

    public static function namedComponentTemplate(string $componentName): string {
        return 'import React from "react"
export const '.$componentName.' = () => {

    return (
        <>
        
        </>
    );
}        
';
    }

    public static function makeComponent(string $componentName, bool $named): int {
        $componentPath = self::COMPONENT_PATH.Str::lcfirst($componentName).'.jsx';
        $content = ($named) 
            ? self::namedComponentTemplate($componentName) 
            : self::defaultComponentTemplate($componentName);
        return Tools::writeFile($componentPath, $content, 'React component');
    }
}