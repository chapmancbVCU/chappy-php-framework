<?php
declare(strict_types=1);
namespace Chappy\Console\Helpers;

use Symfony\Component\Console\Command\Command;

class React {
    public const COMPONENT_PATH = CHAPPY_BASE_PATH.DS.'resources'.DS.'components'.DS;
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
        return Command::SUCCESS;
    }
}