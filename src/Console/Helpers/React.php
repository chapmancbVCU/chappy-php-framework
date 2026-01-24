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
    /**
     * Path to React.js components.
     */
    public const COMPONENT_PATH = CHAPPY_BASE_PATH.DS.'resources'.DS.'js'.DS.'components'.DS;

    /**
     * Path to React.js hooks.
     */
    public const HOOKS_PATH = CHAPPY_BASE_PATH.DS.'resources'.DS.'js'.DS.'hooks'.DS;

    /**
     * Path to React.js pages.
     */
    public const PAGE_PATH = CHAPPY_BASE_PATH.DS.'resources'.DS.'js'.DS.'pages'.DS;

    /**
     * Path to JavaScript utilities.
     */
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
            ? ReactStubs::namedComponentTemplate($componentName) 
            : ReactStubs::defaultComponentTemplate($componentName);
        return Tools::writeFile($componentPath, $content, 'React component');
    }

    /**
     * Generates a JavaScript file under 'resources/js/hook' to be 
     * use as hook file with React.js front-end.
     *
     * @param string $hookName The name of the hook file.
     * @return int A value that indicates success, invalid, or failure.
     */
    public static function makeHook(string $hookName): int {
        Tools::pathExists(self::HOOKS_PATH);
        $filePath = self::HOOKS_PATH.$hookName.'.js';
        $content = ReactStubs::hookTemplate($hookName);
        return Tools::writeFile($filePath, $content, 'React.js hook');
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
            ? ReactStubs::namedComponentTemplate($pageName) 
            : ReactStubs::defaultComponentTemplate($pageName);

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