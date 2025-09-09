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

    public function construct(
        string $baseUrl,
        array $defaultHeaders = ['Accept' => 'application/json'],
        array $defaultQuery,
        int $timeout = 6,
        int $defaultTtl = 0,
        string $acheNamespace = 'api'
    ) {
        
    }
}