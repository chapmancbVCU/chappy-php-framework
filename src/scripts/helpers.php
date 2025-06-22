<?php

use Core\Router;
use Core\Session;
use Core\Lib\Utilities\Arr;
use Core\Lib\Utilities\DateTime;
use Core\Lib\Utilities\Env;
use Core\Lib\Logging\Logger;
use Core\Lib\Utilities\Config;
use Symfony\Component\VarDumper\VarDumper;

if (!function_exists('asset')) {
    /**
     * Retrieves publicly available asset. 
     *
     * @param string $path Path to the asset.
     * @return string The full path.
     */
    function asset(string $path): string {
        return rtrim(Env::get('APP_DOMAIN', '/'), '/') . '/' . ltrim($path, '/');
    }
}

if(!function_exists('cl')) {
    /**
     * Prints to console using JavaScript.
     * 
     * @param mixed $vars The information we want to print to console.
     * @param bool $with_script_tags - Determines if we will use script tabs in 
     * our output.  Default value is true.
     * @return void
     */
    function cl(mixed ...$vars): void {
        $json_outputs = Arr::map($vars, fn($vars) => json_encode($vars, JSON_HEX_TAG));
        $js_code = 'console.log(' . implode(', ', $json_outputs) . ');';
        echo '<script>' . $js_code . '</script>';
    }
}

if(!function_exists('config')) {
    /**
     * Get a configuration value.
     *
     * @param string $key Dot notation key
     * @param mixed $default Default value if key not found
     * @return mixed
     */
    function config($key, $default = null)
    {
        return Config::get($key, $default);
    }
}

if(!function_exists('dd')) {
    /**
     * Performs var_dump of parameter and kills the page.
     * 
     * @param mixed ...$var Contains the data we wan to print to the page.
     * @return void
     */
    function dd(mixed ...$vars): void {
        foreach ($vars as $var) {
        VarDumper::dump($var);
        }
        die(1); // Terminate the script
    }
}


if(!function_exists('dump')) {
      /**
     * Dumps content but continues execution.
     *
     * @param mixed ...$var Contains the data we wan to print to the page.
     * @return void
     */
    function dump(mixed ...$vars): void {
        foreach ($vars as $var) {
        VarDumper::dump($var);
        }
    }
}

if(!function_exists('e')) {
    /**
     * Escape a string for safe HTML output.
     *
     * This function converts special characters to HTML entities to prevent
     * cross-site scripting (XSS) vulnerabilities when rendering user-provided
     * content inside HTML templates.
     *
     * Equivalent to Laravel's `e()` function and wraps `htmlspecialchars()` using UTF-8 encoding.
     *
     * @param string|null $value The string to be escaped. Non-string values will be cast to string.
     * @return string The escaped string safe for HTML output.
     */
    function e($value) {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}

if(!function_exists('flashMessage')) {
    /**
     * Adds a session alert message.
     *
     * @param string $type Can be info, success, warning, danger, primary, 
     * secondary, or dark.
     * @param string $message The message you want to display in the alert.
     * @return void
     */
    function flashMessage(string $type, string $message): void {
        Session::addMessage($type, $message);
    }
}

if(!function_exists('env')) {
    /**
     * Get an environment variable.
     *
     * @param string $key The key to retrieve
     * @param mixed $default Default value if key is not found
     * @return mixed
     */
    function env(string $key, mixed $default = null)
    {
        return Env::get($key, $default);
    }
}

if(!function_exists('logger')) {
    /**
     * Performs operations for adding content to log files.
     *
     * @param string $message The description of an event that is being 
     * written to a log file.
     * @param string $level Describes the severity of the message.
     * @return void
     */
    function logger(string $message, string $level = 'info') {
        Logger::log($message, $level);
    }
}

if (!function_exists('now')) {
    /**
     * Get the current time as a formatted string using Carbon.
     *
     * This function supports optional user-specific overrides for timezone,
     * format, and locale. If not provided, it defaults to the application's
     * configured environment values.
     *
     * @param string|null $timezone The timezone to use (e.g., 'Europe/Berlin').
     *                              Falls back to env('TIME_ZONE') if null.
     * @param string|null $format   The format string (e.g., 'H:i' or 'g:i A').
     *                              Falls back to DateTime::FORMAT_12_HOUR if null.
     * @param string|null $locale   The locale code (e.g., 'en', 'fr').
     *                              Falls back to env('LOCALE') if null.
     *
     * @return string The current time formatted based on the provided or default settings.
     *
     * @see DateTime::formatTime() for formatting logic.
     */
    function now(?string $timezone = null, ?string $format = null, ?string $locale = null): string {
        $tz = $timezone ?? env('TIME_ZONE', 'UTC');
        $fmt = $format ?? \Core\Lib\Utilities\DateTime::FORMAT_12_HOUR;
        $loc = $locale ?? env('LOCALE', 'en');

        return \Core\Lib\Utilities\DateTime::formatTime(
            Carbon\Carbon::now($tz)->toDateTimeString(),
            $fmt,
            $loc,
            $tz
        );
    }
}

if(!function_exists('redirect')) {
    /**
     * Performs redirect operations.
     * 
     * @param string $location The view where we will redirect the user.
     * @param array $params The parameters for the action.
     * @return void
     */
    function redirect(string $location, array $params = []): void {
        Router::redirect($location, $params);
    }
}

if(!function_exists('route')) {
    /**
     * Route function for views that supports dot notation and array of parameters.
     *
     * @param string $path The controller name concatenated with the action 
     * name using dot notation.
     * @param array $params The parameters for the action.
     * @return string $url The URL.
     */
    function route(string $path, array $params = []): string {
        $parts = explode('.', $path, 2);
        $controller = $parts[0];
        $action = $parts[1] ?? 'index';

        $domain = Env::get('APP_DOMAIN') ?? '';
        $domain = rtrim($domain, '/');

        $url = $domain . '/' . trim($controller, '/') . '/' . trim($action, '/');

        if (!empty($params)) {
            $url .= '/' . implode('/', array_map('urlencode', $params));
        }

        return $url;
    }
}

if(!function_exists('vite')){
    /**
     * Generate the URL for a Vite asset.
     *
     * @param string $asset Path to the asset (e.g., 'resources/js/app.js').
     * @return string The URL to the asset.
     */
    function vite(string $asset): string {
        $devServer = 'http://localhost:5173';
        $manifestPath = __DIR__ . '/../public/build/manifest.json';
    
        if (is_file($manifestPath)) {
            $manifest = json_decode(file_get_contents($manifestPath), true);
            if (isset($manifest[$asset])) {
                return '/build/' . $manifest[$asset]['file'];
            }
        }
    
        return "$devServer/$asset";
    }
}

