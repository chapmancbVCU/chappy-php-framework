<?php
use Core\Lib\Utilities\Env;

define('DS', DIRECTORY_SEPARATOR);

// Define CHAPPY_ROOT and CHAPPY_BASE_PATH
if (!defined('CHAPPY_ROOT')) {
    define('CHAPPY_ROOT', realpath(dirname(__DIR__))); // chappy-php-framework/src
}
if (!defined('ROOT')) {
    define('ROOT', CHAPPY_ROOT);
}

$starterBase = getcwd(); // starter project root
$autoloadPath = $starterBase . '/vendor/autoload.php';

if (!file_exists($autoloadPath)) {
    echo "❌ Could not find vendor/autoload.php at: $autoloadPath\n";
    exit(1);
}

require_once $autoloadPath;

// Load .env.testing BEFORE anything else
$envFile = $starterBase . '/.env.testing';
if (file_exists($envFile)) {
    Env::load($envFile);
} else {
    echo "⚠️ .env.testing not found at $envFile — using fallback config.\n";
}

define('CHAPPY_BASE_PATH', $starterBase);
define('PHPUNIT_RUNNING', true);

// Now load the main framework bootstrap
require_once __DIR__ . '/bootstrap.php';
