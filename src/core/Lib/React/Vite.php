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
     * Simple env-based dev check.
     *
     * @return bool
     */
    public static function isDev(): bool {
        $env = env('APP_ENV', 'production');
        return in_array($env, ['local', 'dev', 'development'], true);
    }

    /**
     * Check if Vite dev server is reachable.
     *
     * @param string $devBase
     * @return bool
     */
    public static function viteIsRunning(string $devBase = 'http://localhost:5173'): bool {
        $url = rtrim($devBase, '/') . '/@vite/client';
        $ctx = stream_context_create(['http' => ['method' => 'HEAD', 'timeout' => 0.25]]);
        $fh = @fopen($url, 'r', false, $ctx);
        if ($fh) { fclose($fh); return true; }
        return false;
    }

    /**
     * Adds dev tags.
     *
     * @param string $entry Relative path from project root (e.g. 'resources/js/app.jsx')
     * @param string $devServer 'http://localhost:5173'
     * @return string
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
     * @return array|null
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
     * @return string
     */
    private static function prodTags(string $entry): string
    {
        $manifest = self::loadManifest();
        if ($manifest === null) {
            return "<!-- Vite manifest not found. Run `npm run build`. -->";
        }

        $key = ltrim($entry, '/');

        if (!isset($manifest[$key])) {
            return "<!-- Entry {$key} not in manifest. -->";
        }

        $publicBase = rtrim(env('APP_DOMAIN', '/'), '/'); // e.g. http://localhost:8000

        $tags = [];

        // JS entry
        $file = $manifest[$key]['file'] ?? null;
        if ($file) {
            $src = $publicBase . '/public/build/' . ltrim($file, '/');
            $tags[] = "<script type=\"module\" src=\"{$src}\"></script>";
        }

        // CSS from this entry
        if (!empty($manifest[$key]['css'])) {
            foreach ($manifest[$key]['css'] as $css) {
                $href = $publicBase . '/public/build/' . ltrim($css, '/');
                $tags[] = "<link rel=\"stylesheet\" href=\"{$href}\" />";
            }
        }

        // Also include imported CSS chunks if present
        if (!empty($manifest[$key]['imports'])) {
            foreach ($manifest[$key]['imports'] as $import) {
                if (isset($manifest[$import]['css'])) {
                    foreach ($manifest[$import]['css'] as $css) {
                        $href = $publicBase . '/public/build/' . ltrim($css, '/');
                        $tags[] = "<link rel=\"stylesheet\" href=\"{$href}\" />";
                    }
                }
            }
        }

        return implode("\n", $tags);
    }

    /**
     * Render script/link tags for a Vite entry.
     * - In dev (env=local/dev & dev server up), inject HMR client + raw module URL.
     * - In prod (APP_ENV=production), always use manifest-based assets.
     *
     * @param string $entry Relative path from project root (e.g. 'resources/js/app.jsx')
     * @param string $devServer
     * @return string
     */
    public static function tags(string $entry, string $devServer = 'http://localhost:5173'): string
    {
        $env = env('APP_ENV', 'production');

        // In dev-like envs, always use the dev server
        if (in_array($env, ['local', 'dev', 'development'], true)) {
            return self::devTags($entry, $devServer);
        }

        // Otherwise, use built assets
        return self::prodTags($entry);
    }

}
