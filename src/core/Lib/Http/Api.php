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

    private const CACHE_DIRECTORY = CHAPPY_BASE_PATH . DS . 'storage' . DS . 'cache';

    public function __construct(
        string $baseUrl,
        string $cacheNamespace = 'api',
        array $defaultHeaders = ['Accept' => 'application/json'],
        array $defaultQuery = [],
        int $defaultTtl = 0,
        int $timeout = 6
    ) {
        $this->baseUrl = rtrim($baseUrl, '/');;
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
        $q = array_merge($this->defaultQuery, $query);
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

    public function get(string $path, array $query = [], ?int $ttl = null): array {
        $ttl = $ttl ?? $this->defaultTtl;
        $url = $this->buildUrl($path, $query);

        if($ttl > 0) {
            $hit = $this->readCache($url, $ttl);
            if($hit !== null) return $hit;
        }

        $data = $this->requestJson('GET', $url, null, []);
        if($ttl > 0) {
            $this->writeCache($url, $data);
        }

        return $data;
    }

    public function post(string $path, array $body = [], array $query = [], array $headers = []): array {
        $url = $this->buildUrl($path, $query);
        $headers = ['Content-Type' => 'application/json'] + $headers;

        $payload = json_encode($body, JSON_UNESCAPED_UNICODE);
        if($payload === false) {
            throw new \RuntimeException('Failed to JSON-encode POST body.');
        }

        return $this->requestJson('POST', $url, $payload, $headers);
    }

    protected function readCache(string $url, int $ttl): ?array {
        $file = $this->cacheFile($url);
        if(!is_file($file)) return null;
        if(time() - filemtime($file) > $ttl) return null;

        $raw = file_get_contents($file);
        $data = json_decode((string)$raw, true);
        return is_array($data) ? $data : null;
    }

    protected function requestJson(string $method, string $url, ?string $body, array $headers): array {
        $flatHeaders = $this->flattenHeaders($this->defaultHeaders + $headers);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_CUSTOMREQUEST  => $method,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT        => $this->timeout,
            CURLOPT_CONNECTTIMEOUT => $this->timeout,
            CURLOPT_HTTPHEADER     => $flatHeaders,
        ]);

        if($body !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        }

        $resp = curl_exec($ch);
        $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = curl_error($ch);
        curl_close($ch);

        if($resp === false) {
            throw new \RuntimeException("Upstream request failed: {$err}");
        }

        $data = json_decode((string)$resp, true);
        if(!is_array($data)) {
            throw new \RuntimeException("Invalid JSON from upstream (HTTP {$code}).");
        }

        if($code >= 400) {
            $msg = $data['message'] ?? 'Upstream error';
            throw new \RuntimeException($msg, $code);
        }

        return $data;
    }

    protected function writeCache(string $url, array $data): void {
        @file_put_contents(
            $this->cacheFile($url),
            json_encode($data, JSON_UNESCAPED_UNICODE)
        );
    }
}