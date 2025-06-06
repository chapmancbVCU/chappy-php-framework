<?php
define('DS', DIRECTORY_SEPARATOR);
// File: vendor/chappy-php/chappy-php-framework/src/scripts/bootstrap_phpunit.php
if (!defined('CHAPPY_ROOT')) {
    define('CHAPPY_ROOT', realpath(dirname(__DIR__)));
}

// ✅ Add this legacy alias if needed by older files
if (!defined('ROOT')) {
    define('ROOT', CHAPPY_ROOT); // backward-compatible alias
}

// Assume PHPUnit is run from the starter project root
$starterBase = getcwd(); // e.g., /home/chadchapman/framework/chappy-php-starter
$autoloadPath = $starterBase . '/vendor/autoload.php';

if (!file_exists($autoloadPath)) {
    echo "❌ Could not find vendor/autoload.php at: $autoloadPath\n";
    exit(1);
}

require_once $autoloadPath;

// Predefine CHAPPY_BASE_PATH for test bootstrapping
if (!defined('CHAPPY_BASE_PATH')) {
    define('CHAPPY_BASE_PATH', $starterBase);
}

define('PHPUNIT_RUNNING', true);
require_once __DIR__ . '/bootstrap.php';

// Then load your framework bootstrap logic
require_once __DIR__ . '/bootstrap.php';
