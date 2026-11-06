<?php
declare(strict_types=1);

/**
 * PHPStan bootstrap.
 *
 * The framework's path constants are normally defined by the starter app's
 * entry points (console, index.php) and src/scripts/bootstrap.php. Static
 * analysis never runs those, so we define them here. Values only need to be
 * the right type; PHPStan never touches the filesystem through them.
 */
defined('DS')               || define('DS', DIRECTORY_SEPARATOR);
defined('ROOT')             || define('ROOT', __DIR__);
defined('CHAPPY_ROOT')      || define('CHAPPY_ROOT', __DIR__ . DS . 'src');
defined('CHAPPY_BASE_PATH') || define('CHAPPY_BASE_PATH', __DIR__);
