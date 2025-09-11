<?php
declare(strict_types=1);
namespace Core\Lib\Http;

/**
 * Lightweight HTTP JSON client with optional on-disk caching.
 *
 * Responsibilities:
 * - Build URLs from a base URL plus default and per-call query parameters.
 * - Perform JSON-based GET/POST requests with cURL and sensible timeouts.
 * - Cache successful GET responses to `storage/cache/<namespace>/cache_<hash>.json`.
 *
 * Typical usage is to subclass this client and provide service-specific defaults
 * (e.g., base URL, default headers, API key in `$defaultQuery`, TTL, etc.).
 *
 * @package Core\Lib\Http
 */
class Api {
    /**
     * Base URL (no trailing slash), e.g. "https://api.example.com/v1"
     * @var string
     */
    protected string $baseUrl;

    /**
     * Absolute path to the cache directory for this client/namespace.
     * @var string
     */
    protected string $cacheDir;

    /**
     * Default headers sent on every request (merged with per-call headers).
     * Keys are header names, values are strings.
     * @var array<string,string>
     */
    protected array $defaultHeaders = [];

    /**
     * Default query parameters merged into every request (per-call overrides).
     * @var array<string, mixed>
     */
    protected array $defaultQuery = [];

    /**
     * Default TTL (seconds) for GET cache. 0 disables caching.
     * @var int
     */
    protected int $defaultTtl;

    /**
     * Connection/overall timeout in seconds.
     * @var int
     */
    protected int $timeout;

    /**
     * Root cache directory for the application.
     * @var string
     */
    private const CACHE_DIRECTORY = CHAPPY_BASE_PATH . DS . 'storage' . DS . 'cache';

    /**
     * @param string               $baseUrl         Service base URL (e.g., "https://api.example.com")
     * @param string               $cacheNamespace  Subdirectory under cache root (e.g., "api", "weather")
     * @param array<string,string> $defaultHeaders  Default headers for all requests
     * @param array<string,mixed>  $defaultQuery    Default query params for all requests
     * @param int                  $defaultTtl      Default cache TTL (seconds) for GET; 0 disables caching
     * @param int                  $timeout         cURL timeout in seconds (also used as connect timeout)
     */
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
     * Build an absolute URL with merged default + per-call query parameters.
     * Per-call parameters override defaults on conflict.
     *
     * @param string              $path   Path relative to the base URL (e.g., "/weather")
     * @param array<string,mixed> $query  Per-call query parameters
     * @return string                     Absolute URL including query string
     */
    protected function buildUrl(string $path, array $query): string {
        $q = array_merge($this->defaultQuery, $query);
        $qs = $q ? ('?' . http_build_query($q)) : '';
        return $this->baseUrl . $path . $qs;
    }

    /**
     * Compute the cache filename for a given URL.
     *
     * @param string $url Absolute URL
     * @return string     Absolute path to the cache file
     */
    protected function cacheFile(string $url): string {
        return $this->cacheDir . DS . 'cache_' . md5($url) . '.json';
    }

    /**
     * Convert an associative array of headers into "Key: Value" lines.
     *
     * @param array<string,string> $headers
     * @return list<string>
     */
    protected function flattenHeaders(array $headers): array {
        $out = [];
        foreach($headers as $k => $v) {
            $out[] = "{$k}: {$v}";
        }

        return $out;
    }

    /**
     * Perform a GET request that expects a JSON response.
     * Utilizes on-disk caching when TTL > 0.
     *
     * @param string              $path  Path relative to base URL (e.g., "/weather")
     * @param array<string,mixed> $query Query parameters for this call
     * @param int|null            $ttl   Override cache TTL (seconds); null uses default; 0 disables caching
     * @return array<string,mixed>       Decoded JSON as an associative array
     *
     * @throws \RuntimeException         On transport errors or invalid/upstream error JSON
     */
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

    /**
     * Perform a POST request with a JSON body and expect a JSON response.
     *
     * @param string               $path     Path relative to base URL
     * @param array<string,mixed>  $body     Payload to JSON-encode and send
     * @param array<string,mixed>  $query    Extra query parameters
     * @param array<string,string> $headers  Extra headers (merged over defaults)
     * @return array<string,mixed>           Decoded JSON as an associative array
     *
     * @throws \RuntimeException             When JSON encoding fails or upstream returns an error
     */
    public function post(string $path, array $body = [], array $query = [], array $headers = []): array {
        $url = $this->buildUrl($path, $query);
        $headers = ['Content-Type' => 'application/json'] + $headers;

        $payload = json_encode($body, JSON_UNESCAPED_UNICODE);
        if($payload === false) {
            throw new \RuntimeException('Failed to JSON-encode POST body.');
        }

        return $this->requestJson('POST', $url, $payload, $headers);
    }

    /**
     * Read a cached JSON response for a URL if it exists and is fresh.
     *
     * @param string $url Absolute URL used as cache key
     * @param int    $ttl TTL in seconds
     * @return array<string,mixed>|null Decoded JSON or null if cache miss/stale
     */
    protected function readCache(string $url, int $ttl): ?array {
        $file = $this->cacheFile($url);
        if(!is_file($file)) return null;
        if(time() - filemtime($file) > $ttl) return null;

        $raw = file_get_contents($file);
        $data = json_decode((string)$raw, true);
        return is_array($data) ? $data : null;
    }

    /**
     * Core HTTP request using cURL that decodes JSON and throws on errors.
     *
     * @param string               $method  HTTP method (e.g., "GET", "POST")
     * @param string               $url     Absolute URL
     * @param string|null          $body    Raw request body (e.g., JSON) or null
     * @param array<string,string> $headers Extra headers (merged over defaults)
     * @return array<string,mixed>          Decoded JSON as an associative array
     *
     * @throws \RuntimeException            On transport errors, invalid JSON, or upstream HTTP >= 400
     */
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

    /**
     * Persist a decoded JSON response to the cache for a given URL.
     *
     * @param string              $url  Absolute URL used as cache key
     * @param array<string,mixed> $data Decoded JSON to write
     * @return void
     */
    protected function writeCache(string $url, array $data): void {
        @file_put_contents(
            $this->cacheFile($url),
            json_encode($data, JSON_UNESCAPED_UNICODE)
        );
    }
}