<?php
declare(strict_types=1);

/**
 * Bootstrap for static analysis of a Chappy.php application.
 *
 * At runtime, index.php and console define the path constants and
 * scripts/bootstrap.php loads the global helper functions (env(), redirect(),
 * flashMessage(), and so on).  Static analysers never run those files, so
 * they report the constants and functions as undefined.  This file provides
 * them without starting the application: it does not load .env or config,
 * open a database connection, or start a session.
 *
 * Point your analyser at this file and run it from the application root:
 *  - PHPStan: bootstrapFiles in phpstan.neon.dist
 *  - Psalm: the autoloader attribute in psalm.xml
 *
 * Path values only need to have the right type.  Analysers do not read files
 * through them.
 */

defined('DS')               || define('DS', DIRECTORY_SEPARATOR);
defined('ROOT')             || define('ROOT', (string)getcwd());
defined('CHAPPY_ROOT')      || define('CHAPPY_ROOT', dirname(__DIR__));
defined('CHAPPY_BASE_PATH') || define('CHAPPY_BASE_PATH', ROOT);

// Global helper functions.  Each is guarded by function_exists().
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/forms.php';
require_once __DIR__ . '/logging.php';
require_once __DIR__ . '/view.php';
