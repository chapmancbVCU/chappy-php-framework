<?php
declare(strict_types=1);
namespace Console\Helpers;

use Console\Helpers\ReactStubs;
use Console\Helpers\Tools;
use Symfony\Component\Console\Command\Command;

/**
 * Contains functions that perform operations for React.js relate CLI commands.
 */
class React {
    public const COMPONENT_PATH = CHAPPY_BASE_PATH.DS.'resources'.DS.'js'.DS.'components'.DS;
    public const PAGE_PATH = CHAPPY_BASE_PATH.DS.'resources'.DS.'js'.DS.'pages'.DS;
    public const UTILS_PATH = CHAPPY_BASE_PATH.DS.'resources'.DS.'js'.DS.'utils'.DS;
    
    /**
     * Generates the auth jsx components.
     *
     * @return int A value that indicates success, invalid, or failure.
     */
    public static function authComponents(): int {
        $path = self::PAGE_PATH.'auth'.DS;
        Tools::pathExists($path);

        Tools::writeFile($path.'Login.jsx', ReactStubs::authLogin(), 'auth/Login.jsx');
        Tools::writeFile($path.'Register.jsx', ReactStubs::authRegister(), 'auth/Register.jsx');
        return Command::SUCCESS;
    }

    /**
     * Generates a component with default export.
     *
     * @param string $componentName The name of the component.
     * @return string The contents of the component.
     */
    public static function defaultComponentTemplate(string $componentName): string {
        return <<<JSX
import React from "react";
function {$componentName}() {

    return (
        <>
        
        </>
    );
}        
export default {$componentName};
JSX;
    }

    /**
     * Generates the error/NotFound.jsx component.
     *
     * @return int A value that indicates success, invalid, or failure.
     */
    public static function errorNotFoundComponent(): int {
        $path = self::PAGE_PATH.'error'.DS;
        Tools::pathExists($path);

        return Tools::writeFile($path.'NotFound.jsx', ReactStubs::errorNotFound(), 'error/NotFound.jsx');
    }

    /**
     * Generates the home/Index.jsx component.
     *
     * @return int A value that indicates success, invalid, or failure.
     */
    public static function homeComponent(): int {
        $path = self::PAGE_PATH.'home'.DS;
        Tools::pathExists($path);

        return Tools::writeFile($path.'Index.jsx', ReactStubs::homeIndex(), 'home/index.jsx');
    }

    /**
     * Generates a named export component.
     *
     * @param string $componentName The name of the component.
     * @return string The contents of the component.
     */
    public static function namedComponentTemplate(string $componentName): string {
        return <<<JSX
import React from "react";
export const {$componentName} = () => {

    return (
        <>
        
        </>
    );
}
JSX;
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
        Tools::pathExists(self::COMPONENT_PATH);
        $componentPath = self::COMPONENT_PATH.$componentName.'.jsx';
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

    /**
     * Generates the profile jsx components.
     *
     * @return int A value that indicates success, invalid, or failure.
     */
    public static function profileComponents(): int {
        $componentPath = self::COMPONENT_PATH;
        Tools::pathExists($componentPath);
        Tools::writeFile(
            $componentPath.'ProfileImageSorter.jsx',
            ReactStubs::profileImageSorter(),
            'components/ProfileImageSorter.jsx'
        );

        $path = self::PAGE_PATH.'profile'.DS;
        Tools::pathExists($path);

        Tools::writeFile($path.'Edit.jsx', ReactStubs::profileEdit(), 'profile/Edit.jsx');
        Tools::writeFile($path.'Index.jsx', ReactStubs::profileIndex(), 'profile/Index.jsx');
        Tools::writeFile($path.'UpdatePassword.jsx', ReactStubs::profileUpdatePassword(), 'profile/UpdatePassword.jsx');
        return Command::SUCCESS;
    }
}