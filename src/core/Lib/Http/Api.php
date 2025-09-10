<?php
declare(strict_types=1);
namespace Core\Lib\Http;

/**
 * Defines API based operations.
 */
class Api {
    protected string $baseUrl;
    protected string $cacheDir;
    protected array $defaultHeaders = [];
    protected array $defaultQuery = [];
    protected int $defaultTtl;
    protected int $timeout;

    private CONST CACHE_DIRECTORY = CHAPPY_BASE_PATH . DS . 'storage' . DS . 'cache';

    public function construct(
        string $baseUrl,
        string $cacheNamespace = 'api',
        array $defaultHeaders = ['Accept' => 'application/json'],
        array $defaultQuery,
        int $defaultTtl = 0,
        int $timeout = 6
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

    /**
     * Build an absolute URL with merged default + per-call query.
     *
     * @param string $path The path.
     * @param array $query The per-call query.
     * @return string The absolute URL
     */
    protected function buildUrl(string $path, array $query): string {
        $q = $this->defaultQuery + $query;
        $qs = $q ? ('?' . http_build_query($q)) : '';
        return $this->baseUrl . $path . $qs;
    }

    /**
     * Caching helpers (GET only)
     *
     * @param string
     * @return string
     */
    protected function cacheFile(string $url): string {
        return $this->cacheDir . DS . 'cache_' . md5($url) . '.json';
    }

    protected function flattenHeaders(array $headers): array {
        $out = [];
        foreach($headers as $k => $v) {
            $out[] = "{$k}: {$v}";
        }

        return $out;
    }

    protected function readCache(string $url, int $ttl): ?array {
        $file = $this->cacheFile($url);
        if(!is_file($file)) return null;
        if(time() - filemtime($file) > $ttl) return null;

        $raw = file_get_contents($file);
        $data = json_decode((string)$raw, true);
        return is_array($data) ? $data : null;
    }

    protected function writeCache(string $url, array $data): void {
        @file_put_contents(
            $this->cacheFile($url),
            json_encode($data, JSON_UNESCAPED_UNICODE)
        );
    }
}