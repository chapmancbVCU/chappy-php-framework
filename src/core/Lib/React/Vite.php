<?php
declare(strict_types=1);

namespace Core\Lib\React;

/**
 * Supports operations for Vite.
 */
final class Vite
{
    /**
     * Extracts value of csrf_token from hidden element.
     *
     * @return string The csrf_token.
     */
    public static function csrfToken(): string {
        $token = '';
        $html = csrf();
        if (preg_match('/value="([^"]+)"/', (string)$html, $m)) {
            $token = $m[1];
        }
        return htmlspecialchars((string)$token, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Fallback dev server URL used when no hot file is present.
     */
    public const DEFAULT_DEV_SERVER = 'http://localhost:5173';

    /**
     * Absolute path to the "hot" file written by the chappyHot Vite plugin
     * while `npm run dev` is running.  It contains the dev server's public URL.
     *
     * @return string Path to the hot file.
     */
    public static function hotFile(): string {
        $base = defined('CHAPPY_BASE_PATH') ? CHAPPY_BASE_PATH : dirname(__DIR__, 5);
        return $base . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'hot';
    }

    /**
     * Determines if the Vite dev server is running by checking for the hot file.
     *
     * @return bool True if the hot file exists, otherwise false.
     */
    public static function isRunningHot(): bool {
        return is_file(self::hotFile());
    }

    /**
     * Returns the dev server URL without a trailing slash.
     *
     * Resolution order: hot file contents, VITE_DEV_SERVER env variable,
     * then DEFAULT_DEV_SERVER.
     *
     * @return string The dev server URL.
     */
    public static function devServerUrl(): string {
        if (self::isRunningHot()) {
            $url = trim((string) @file_get_contents(self::hotFile()));
            if ($url !== '') {
                return rtrim($url, '/');
            }
        }

        return rtrim((string) env('VITE_DEV_SERVER', self::DEFAULT_DEV_SERVER), '/');
    }

    /**
     * Dev check.  Dev assets are used only when APP_ENV is a dev-like
     * environment AND the Vite dev server is running (hot file exists).
     * Otherwise the built assets from the manifest are used.
     *
     * @return bool
     */
    public static function isDev(): bool {
        $env = env('APP_ENV', 'production');
        return in_array($env, ['local', 'dev', 'development'], true) && self::isRunningHot();
    }

    /**
     * Check if Vite dev server is reachable.
     *
     * @deprecated 4.0.4 Use Vite::isRunningHot() instead.  This performs a
     * network request on every call.
     * @param string|null $devBase URL for dev server.  Defaults to devServerUrl().
     * @return bool True if running, otherwise false.
     */
    public static function viteIsRunning(?string $devBase = null): bool {
        $url = rtrim($devBase ?? self::devServerUrl(), '/') . '/@vite/client';
        $ctx = stream_context_create(['http' => ['method' => 'HEAD', 'timeout' => 0.25]]);
        $fh = @fopen($url, 'r', false, $ctx);
        if ($fh) { fclose($fh); return true; }
        return false;
    }

    /**
     * Adds dev tags.
     *
     * @param string $entry Relative path from project root (e.g. 'resources/js/app.jsx')
     * @param string $devServer URL for dev server.
     * @return string Script modules with tags.
     */
    private static function devTags(string $entry, string $devServer): string
    {
        $hmr   = rtrim($devServer, '/') . '/@vite';
        $entry = rtrim($devServer, '/') . '/' . ltrim($entry, '/');

        return <<<HTML
<script type="module" src="{$hmr}/client"></script>
<script type="module" src="{$entry}"></script>
HTML;
    }

    /**
     * Locate and decode the Vite manifest.
     *
     * @return array|null The Vite manifest.
     */
    private static function loadManifest(): ?array
    {
        // CHAPPY_BASE_PATH should point to the project root (same as index.php)
        $base = defined('CHAPPY_BASE_PATH') ? CHAPPY_BASE_PATH : dirname(__DIR__, 4);

        $candidates = [
            $base . '/public/build/manifest.json',
            $base . '/public/build/.vite/manifest.json',
        ];

        $path = null;
        foreach ($candidates as $candidate) {
            if (is_file($candidate)) {
                $path = $candidate;
                break;
            }
        }

        if ($path === null) {
            return null;
        }

        $json = file_get_contents($path);
        if ($json === false) {
            return null;
        }

        $data = json_decode($json, true);
        return is_array($data) ? $data : null;
    }

    /**
     * Adds production tags.
     *
     * @param string $entry Relative path from project root (e.g. 'resources/js/app.jsx')
     * @return string The production tags.
     */
    private static function prodTags(string $entry): string
    {
        $base = defined('CHAPPY_BASE_PATH')
            ? CHAPPY_BASE_PATH
            : dirname(__DIR__, 5); // fallback to climb out of vendor
        $manifestCandidates = [
            $base . '/public/build/.vite/manifest.json',
            $base . '/public/build/manifest.json',
        ];

        $manifest = null;
        foreach ($manifestCandidates as $path) {
            if (is_file($path)) {
                $json = file_get_contents($path) ?: '[]';
                $data = json_decode($json, true);
                if (is_array($data)) {
                    $manifest = $data;
                    break;
                }
            }
        }
        
        if (!$manifest) {
            return "<!-- Vite manifest not found. Run `npm run build`. -->";
        }

        $key = ltrim($entry, '/');
        if (!isset($manifest[$key])) {
            return "<!-- Entry {$key} not in manifest. -->";
        }

        $publicBase = rtrim(env('APP_DOMAIN', '/'), '/');
        $tags = [];

        $file = ltrim($manifest[$key]['file'], '/');
        $tags[] = '<script type="module" src="' . $publicBase . '/public/build/' . $file . '"></script>';

        if (!empty($manifest[$key]['css'])) {
            foreach ($manifest[$key]['css'] as $css) {
                $css = ltrim($css, '/');
                $tags[] = '<link rel="stylesheet" href="' . $publicBase . '/public/build/' . $css . '" />';
            }
        }

        if (!empty($manifest[$key]['imports'])) {
            foreach ($manifest[$key]['imports'] as $import) {
                if (isset($manifest[$import]['css'])) {
                    foreach ($manifest[$import]['css'] as $css) {
                        $css = ltrim($css, '/');
                        $tags[] = '<link rel="stylesheet" href="' . $publicBase . '/public/build/' . $css . '" />';
                    }
                }
            }
        }

        return implode("\n", $tags);
    }

    /**
     * Render script/link tags for a Vite entry.
     * - In dev (env=local/dev & hot file present), inject HMR client + raw module URL.
     * - Otherwise use manifest-based assets.
     *
     * @param string $entry Relative path from project root (e.g. 'resources/js/app.jsx')
     * @param string|null $devServer URL for dev server.  Defaults to devServerUrl().
     * @return string Script/link tags for a Vite entry.
     */
    public static function tags(string $entry, ?string $devServer = null): string
    {
        if (self::isDev()) {
            return self::devTags($entry, $devServer ?? self::devServerUrl());
        }

        // Otherwise, use built assets
        return self::prodTags($entry);
    }

}
