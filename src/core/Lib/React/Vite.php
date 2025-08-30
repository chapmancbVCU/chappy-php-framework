<?php
declare(strict_types=1);

namespace Core\Lib\React;

final class Vite
{
    public static function csrfToken(): string {
        $html = csrf();
        if(preg_match('/value="([^"]+)"/', (string)$html, $m)) {
            $token = $m[1];
        }
        return htmlspecialchars($token, ENT_QUOTES, 'UTF-8');
    }
    
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

    private static function devTags(string $entry, string $devServer): string
    {
        $hmr   = $devServer . '/@vite';
        $entry = $devServer . '/' . ltrim($entry, '/');
        return <<<HTML
<script type="module" src="{$hmr}/client"></script>
<script type="module" src="{$entry}"></script>
HTML;
    }

    public static function isDev() {
        $env = env('APP_ENV', 'production');
        return Vite::viteIsRunning() || in_array($env, ['local','dev','development'], true);
    }
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

    // Treat as dev if Vite's dev server is reachable,
    // OR if your env explicitly says dev-ish.
    public static function viteIsRunning(string $devBase = 'http://localhost:5173'): bool {
        $url = rtrim($devBase, '/') . '/@vite/client';
        $ctx = stream_context_create(['http' => ['method' => 'HEAD', 'timeout' => 0.25]]);
        $fh = @fopen($url, 'r', false, $ctx);
        if ($fh) { fclose($fh); return true; }
        return false;
    }
}
