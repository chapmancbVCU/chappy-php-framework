<?php
namespace Core\Lib\Testing;
use Core\DB;
use Core\Lib\Utilities\Env;
use Console\Helpers\Migrate;
use PHPUnit\Framework\TestCase;
use Database\Seeders\DatabaseSeeder;

/**
 * Abstract class for test cases.
 */
abstract class ApplicationTestCase extends TestCase {
    /**
     * Simulates a controller action based on URL-style input and captures its output.
     *
     * @param string $controllerSlug e.g., 'home'
     * @param string $actionSlug     e.g., 'index'
     * @param array $params          Parameters to pass to the action
     * @return string Rendered HTML output
     *
     * @throws \Exception
     */
    protected function controllerOutput(string $controllerSlug, string $actionSlug, array $urlSegments = []): string
    {
        $controllerClass = 'App\\Controllers\\' . ucfirst($controllerSlug) . 'Controller';
        $actionMethod = $actionSlug . 'Action';

        if (!class_exists($controllerClass)) {
            throw new \Exception("Controller class {$controllerClass} not found.");
        }

        $controller = new $controllerClass($controllerSlug, $actionSlug);

        if (!method_exists($controller, $actionMethod)) {
            throw new \Exception("Method {$actionMethod} not found in {$controllerClass}.");
        }

        ob_start();
        call_user_func_array([$controller, $actionMethod], $urlSegments); // 👈 full support for routed parameters
        return ob_get_clean();
    }



    /**
     * Implements setUp function from TestCase class.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        
        DB::connect([
            'driver'   => Env::get('DB_CONNECTION', 'sqlite'),
            'database' => Env::get('DB_DATABASE', ':memory:'),
            'host'     => Env::get('DB_HOST', '127.0.0.1'),
            'port'     => Env::get('DB_PORT', '3306'),
            'username' => Env::get('DB_USERNAME', 'root'),
            'password' => Env::get('DB_PASSWORD', ''),
            'charset'  => Env::get('DB_CHARSET', 'utf8mb4'),
        ]);
        
        // Control DB setup via env toggles
        if(Env::get('DB_REFRESH', true)) {
            Migrate::refresh();
        }

        if(Env::get('DB_MIGRATE', true)) {
            Migrate::migrate();
        }

        if(Env::get('DB_SEED', true)) {
            (new DatabaseSeeder())->run();
        }
    }
}