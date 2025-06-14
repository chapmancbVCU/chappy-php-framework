<?php

use Core\Router;
use Core\FormHelper;
use Core\Lib\Utilities\Arr;
use Core\Lib\Utilities\Env;
use Core\Lib\Utilities\Config;
use Core\Lib\Utilities\ArraySet;
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

if(!function_exists('csrf')) {
    /**
     * Inserts csrf token into form.
     *
     * @return void
     */
    function csrf() {
        return FormHelper::csrfInput();
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

if(!function_exists('errorBag')) {
    /**
     * Returns list of errors.
     * 
     * @param array|ArraySet $errors A list of errors and their description that is 
     * generated during server side form validation.
     * @return string A string representation of a div element containing an 
     * input of type checkbox.
     */
    function errorBag(array|ArraySet $errors): string {
        return FormHelper::displayErrors($errors);
    }
}

/**
 * Performs redirect operations.
 * 
 * @param string $location The view where we will redirect the user.
 * @return void
 */
if(!function_exists('redirect')) {
    function redirect(string $location): void {
        Router::redirect($location);
    }
}

if(!function_exists('route')) {
    function route(string $path, array $params = []): string {
        $parts = explode('.', $path, 2);
        $controller = $parts[0];
        $action = $parts[1] ?? 'index';

        $url = rtrim(Env::get('APP_DOMAIN', '/'), '/') . '/' . trim($controller, '/') . '/' . trim($action, '/');

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

