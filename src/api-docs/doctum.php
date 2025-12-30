<?php
declare(strict_types=1);

use Doctum\Doctum;
use Doctum\Parser\Filter\TrueFilter;
use Symfony\Component\Finder\Finder;

define('DS', DIRECTORY_SEPARATOR);
define('ROOT', dirname(__DIR__, 2));

$iterator = Finder::create()
    ->files()
    ->name('*.php')
    ->in([ROOT . DS . 'src'])
    ->exclude([
        'api-docs', // ignore src/api-docs (phar + generated views)
    ]);

return new Doctum($iterator, [
    'title' => 'Chappy.php API',
    'build_dir' => ROOT . DS . 'docs',
    'cache_dir' => ROOT . DS . '.cache' . DS . 'doctum',
    'default_opened_level' => 2,
    'base_url' => '/docs/',

    // IMPORTANT: Doctum 5.x expects filter as an option (no setFilter() method)
    'filter' => new TrueFilter(),
]);
