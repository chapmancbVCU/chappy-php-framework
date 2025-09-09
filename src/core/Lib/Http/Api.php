<?php
declare(strict_types=1);
namespace Core\Lib\Http;

/**
 * Defines API based operations.
 */
class Api {
    protected string $baseUrl;
    protected array $defaultHeaders = [];
    protected array $defaultQuery = [];
    protected int $timeout;
    protected int $defaultTtl;
    protected string $cacheDir;

    private CONST CACHE_DIRECTORY = CHAPPY_BASE_PATH . DS . 'storage' . DS . 'cache';

    public function construct(
        string $baseUrl,
        array $defaultHeaders = ['Accept' => 'application/json'],
        array $defaultQuery,
        int $timeout = 6,
        int $defaultTtl = 0,
        string $cacheNamespace = 'api'
    ) {
        $this->baseUrl = $baseUrl;
        $this->defaultHeaders = $defaultHeaders;
        $this->defaultQuery = $defaultQuery;
        $this->timeout = $timeout;
        $this->defaultTtl = $defaultTtl;

        $rootCache = defined('CHAPPY_BASE_PATH') ? self::CACHE_DIRECTORY : sys_get_temp_dir();
        $this->cacheDir = $rootCache . DS . $cacheNamespace;
        if (!is_dir($this->cacheDir)) @mkdir($this->cacheDir, 0775, true);
    }
}