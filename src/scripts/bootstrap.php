<?php

use Dotenv\Dotenv;
use Core\Lib\Utilities\Env;
use Core\Lib\Utilities\Config;

// Define CHAPPY_ROOT (framework root)
if (!defined('CHAPPY_ROOT')) {
    define('CHAPPY_ROOT', realpath(dirname(__DIR__)));
}

// Define CHAPPY_BASE_PATH (starter app root) using backtrace
if (!defined('CHAPPY_BASE_PATH')) {
    $caller = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0];
    define('CHAPPY_BASE_PATH', dirname($caller['file']));
}

// Optional: show path if .env not found
$envPath = CHAPPY_BASE_PATH . '/.env';
if (!file_exists($envPath)) {
    echo "⚠️  .env file not found at: $envPath" . PHP_EOL;
}

// Load helper functions
require_once CHAPPY_ROOT . '/scripts/helpers.php';
require_once CHAPPY_ROOT . '/scripts/forms.php';
// Load environment variables
$dotenv = Dotenv::createImmutable(CHAPPY_BASE_PATH);
$dotenv->load();

// Load .env values and config
Env::load($envPath);
Config::load(CHAPPY_BASE_PATH . '/config');
