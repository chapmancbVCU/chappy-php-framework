<?php

use Doctum\Doctum;
use Doctum\Parser\Filter\PublicFilter;
use Doctum\Config;
use Symfony\Component\Finder\Finder;

define('DS', DIRECTORY_SEPARATOR);
define('ROOT', dirname(__DIR__, 2));

// Finder: Which files to document
$iterator = Finder::create()
    ->files()
    ->name('*.php')
    ->in([
        ROOT . DS . 'src'
    ])
    ->exclude([
        'vendor',
        'node_modules',
        'config',
        'public',
        'logs',
        'cache',
        'api-docs/views'
    ]);

// Create Config object first (preferred in latest Doctum)
$config = new Config($iterator, [
    'title' => 'Chappy.php API',
    'build_dir' => ROOT . DS . 'src' . DS . 'api-docs' . DS . 'views',
    'cache_dir' => ROOT . DS . 'cache' . DS . 'doctum',
    'default_opened_level' => 2,
    'base_url' => '/api-docs/',
]);

$config->setFilter(new PublicFilter());

// Return Doctum instance
return new Doctum($config);
