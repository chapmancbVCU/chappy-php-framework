<?php
declare(strict_types=1);
namespace Core;
use Core\Lib\Utilities\Env;
use Core\Router;
use Core\Lib\Logging\Logger;
use Core\SessionManager;
use Core\ErrorHandler;
use Exception;

/**
 * The Application class supports basic functional needs of the application.
 */
class Application {
    /**
     * Calls functions for reporting and unregister of globals.
     */
    public function __construct() {
        $this->_set_reporting();
    }

    /**
     * Manages the displaying of error messages and other reporting for this 
     * application.
     *
     * @return void
     */
    private function _set_reporting(): void {
        $debug = Env::get('DEBUG', false);
        if($debug) {
            error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
            ini_set('display_errors', 1);
        } else {
            error_reporting(0);
            ini_set('display_errors', 0);
            ini_set('log_errors', 1);
            ini_set('error_log', ROOT . DS . 'tmp' . DS . 'logs' . DS . 'errors.log');
        }
    }

    /**
     * The main function of your application.
     *
     * @return void
     */
    public static function appStart() {
        // Define path related constants.
        define('DS', DIRECTORY_SEPARATOR);
        define('ROOT', dirname(__FILE__));
        // Start PHP session
        session_start();

        // Set up error & exception handling
        ErrorHandler::initialize();

        // Start session & handle auto-login from Remember Me cookie
        SessionManager::initialize();

        // Perform routing.
        try {
            Router::route();
        } catch (Exception $e) {
            Logger::log("Unhandled Exception: " . $e->getMessage(), Logger::ERROR);
            throw $e; // Let Whoops handle it
        }
    }
}