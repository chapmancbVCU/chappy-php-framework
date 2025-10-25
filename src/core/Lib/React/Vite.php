<?php
declare(strict_types=1);

namespace Core\Lib\React;

/**
 * Supports operations for Vite.
 */
final class Vite
{
    private static ?bool $cached = null;
    /**
     * Extracts value of csrf_token from hidden element.
     *
     * @return string The csrf_token.
     */
    public static function csrfToken(): string {
        $html = csrf();
        if(preg_match('/value="([^"]+)"/', (string)$html, $m)) {
            $token = $m[1];
        }
        return htmlspecialchars($token, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Determines if dev server is running.
     *
     * @param string $devServer 'http://localhost:5173'
     * @return bool True if it is running, otherwise we return false.
     */
    private static function devServerRunning(string $devServer): bool
    {
        // Cheap check: try opening the HMR endpoint
        $url = rtrim($devServer, '/') . '/@vite/client';
        $ctx = stream_context_create(['http' => ['timeout' => 0.15]]);
        try {
            $f = @fopen($url, 'r', false, $ctx);
            if ($f) { fclose($f); return true; }
        } catch (\Throwable) {}
        return false;
    }

    /**
     * Adds dev tags.
     *
     * @param string $entry Relative path from project root (e.g. 'resources/js/app.jsx')
     * @param string $devServer 'http://localhost:5173'
     * @return string The dev tags.
     */
    private static function devTags(string $entry, string $devServer): string
    {
        $hmr   = $devServer . '/@vite';
        $entry = $devServer . '/' . ltrim($entry, '/');
        return <<<HTML
<script type="module" src="{$hmr}/client"></script>
<script type="module" src="{$entry}"></script>
HTML;
    }

    /**
     * Checks if we are in development mode.
     *
     * @return bool True if in development mode, otherwise we return false.
     */
    public static function isDev() {
        if (self::$cached !== null) return self::$cached;

        // optional: short-circuit if you want APP_ENV to force prod
        if (env('APP_ENV', 'production') === 'production') {
            return self::$cached = false;
        }

        $host = '127.0.0.1';
        $port = 5173;
        $fp = @fsockopen($host, $port, $errno, $errstr, 0.05);
        if (is_resource($fp)) {
            fclose($fp);
            return self::$cached = true;
        }
        return self::$cached = false;
    }

    /**
     * Resolve a JS/CSS entry via manifest in production.
     * Example: vite('resources/js/app.jsx')
     */
    public static function asset(string $entry): string
    {
        if (self::isDev()) {
            return "http://localhost:5173/{$entry}";
        }

        $manifest = CHAPPY_BASE_PATH . '/public/build/.vite/manifest.json';
        $json = @file_get_contents($manifest);
        if ($json === false) {
            throw new \RuntimeException('Vite manifest not found: ' . $manifest);
        }
        $map = json_decode($json, true);
        if (!isset($map[$entry])) {
            throw new \RuntimeException("Vite entry '{$entry}' not found in manifest.");
        }
        return env('APP_DOMAIN', '/') . 'public/build/' . $map[$entry]['file'];
    }

    /**
     * Return all CSS files for a given entry (if any) in production.
     */
    public static function css(string $entry): array
    {
        if (self::isDev()) return []; // dev injects CSS via HMR

        $manifest = CHAPPY_BASE_PATH . '/public/build/.vite/manifest.json';
        $json = @file_get_contents($manifest);
        if ($json === false) return [];
        $map = json_decode($json, true);
        $css = $map[$entry]['css'] ?? [];
        return array_map(
            fn ($href) => env('APP_DOMAIN', '/') . 'public/build/' . $href,
            $css
        );
    }
    
    /**
     * Adds production tags.
     *
     * @param string $entry Relative path from project root (e.g. 'resources/js/app.jsx')
     * @return string The prod tags.
     */
    private static function prodTags(string $entry): string
    {
        $manifestPath = __DIR__ . '/../../public/build/manifest.json';
        if (!is_file($manifestPath)) {
            return "<!-- Vite manifest not found. Run `npm run build`. -->";
        }
        $manifest = json_decode((string)file_get_contents($manifestPath), true);
        $key = ltrim($entry, '/');
        if (!isset($manifest[$key])) {
            return "<!-- Entry {$key} not in manifest. -->";
        }

        $tags = [];
        $file = '/build/' . $manifest[$key]['file'];
        $tags[] = "<script type=\"module\" src=\"{$file}\"></script>";

        // CSS from this entry
        if (!empty($manifest[$key]['css'])) {
            foreach ($manifest[$key]['css'] as $css) {
                $tags[] = "<link rel=\"stylesheet\" href=\"/build/{$css}\" />";
            }
        }

        // Also include imported CSS chunks if present (rare but safe)
        if (!empty($manifest[$key]['imports'])) {
            foreach ($manifest[$key]['imports'] as $import) {
                if (isset($manifest[$import]['css'])) {
                    foreach ($manifest[$import]['css'] as $css) {
                        $tags[] = "<link rel=\"stylesheet\" href=\"/build/{$css}\" />";
                    }
                }
            }
        }

        return implode("\n", $tags);
    }

    /**
     * Render script/link tags for a Vite entry.
     * - In dev, inject HMR client + raw module URL from the dev server.
     * - In prod, read the manifest to emit hashed assets + CSS.
     *
     * @param string $entry Relative path from project root (e.g. 'resources/js/app.jsx')
     * @param string $devServer 'http://localhost:5173'
     */
    public static function tags(string $entry, string $devServer = 'http://localhost:5173'): string
    {
        $isDev = self::devServerRunning($devServer);
        return $isDev
            ? self::devTags($entry, $devServer)
            : self::prodTags($entry);
    }

    /**
     * Treat as dev if Vite's dev server is reachable,
     * OR if your env explicitly says dev-ish.
     *
     * @param string $devBase 'http://localhost:5173'
     * @return bool True if Vite is running, otherwise we return false.
     */
    public static function viteIsRunning(string $devBase = 'http://localhost:5173'): bool {
        $url = rtrim($devBase, '/') . '/@vite/client';
        $ctx = stream_context_create(['http' => ['method' => 'HEAD', 'timeout' => 0.25]]);
        $fh = @fopen($url, 'r', false, $ctx);
        if ($fh) { fclose($fh); return true; }
        return false;
    }
}
