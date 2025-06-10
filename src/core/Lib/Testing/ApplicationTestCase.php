<?php
declare(strict_types=1);
namespace Core\Lib\Testing;
use Core\DB;
use Core\Lib\Utilities\Env;
use Console\Helpers\Migrate;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Database\Seeders\DatabaseSeeder;
use Core\Lib\Testing\TestResponse;

/**
 * Abstract class for test cases.
 */
abstract class ApplicationTestCase extends TestCase {
    public static array $controllerOutput = [];
    
    /**
     * Assert that a record exists in the specified database table with the given conditions.
     *
     * This method builds a SQL WHERE clause from the provided key-value array and checks
     * whether a matching row exists. It fails the test if no such row is found.
     *
     * @param string $table   The name of the database table to search.
     * @param array  $data    An associative array of column => value pairs to match against.
     * @param string $message Optional custom failure message.
     *
     * @return void
     *
     * @throws \PHPUnit\Framework\AssertionFailedError If the assertion fails.
     */
    public function assertDatabaseHas(string $table, array $data, string $message = ''): void
    {
        $db = \Core\DB::getInstance(); // Adjust if your DB class is namespaced differently

        $query = "SELECT COUNT(*) as count FROM `$table` WHERE ";
        $conditions = [];
        $params = [];

        foreach ($data as $column => $value) {
            $conditions[] = "`$column` = ?";
            $params[] = $value;
        }

        $query .= implode(" AND ", $conditions);
        $result = $db->query($query, $params)->first();

        $exists = $result && $result->count > 0;

        Assert::assertTrue(
            $exists,
            $message ?: "Failed asserting that a row in the '$table' table matches: " . json_encode($data)
        );
    }

    /**
     * Assert that no record exists in the specified database table with the given conditions.
     *
     * This method builds a SQL WHERE clause from the provided key-value array and verifies
     * that no matching row exists. It fails the test if such a row is found.
     *
     * @param string $table   The name of the database table to search.
     * @param array  $data    An associative array of column => value pairs to match against.
     * @param string $message Optional custom failure message.
     *
     * @return void
     *
     * @throws \PHPUnit\Framework\AssertionFailedError If the assertion fails.
     */
    public function assertDatabaseMissing(string $table, array $data, string $message = ''): void
    {
        $db = \Core\DB::getInstance();

        $query = "SELECT COUNT(*) as count FROM `$table` WHERE ";
        $conditions = [];
        $params = [];

        foreach ($data as $column => $value) {
            $conditions[] = "`$column` = ?";
            $params[] = $value;
        }

        $query .= implode(" AND ", $conditions);
        $result = $db->query($query, $params)->first();

        $exists = $result && $result->count > 0;

        Assert::assertFalse(
            $exists,
            $message ?: "Failed asserting that a row in the '$table' table does not exist with: " . json_encode($data)
        );
    }

    /**
     * Asserts that a property exists on the View object captured via controllerOutput().
     * Optionally asserts that the value matches.
     *
     * @param string $property The view property to check.
     * @param mixed|null $expectedValue Optional value to compare against.
     * @return void
     */
    public function assertViewContains(string $property, mixed $expectedValue = null): void
    {
        $view = static::$controllerOutput['view'] ?? null;

        $this->assertNotNull($view, 'No view object captured. Did you forget to call logViewForTesting()?');
        $this->assertObjectHasProperty($property, $view, "View does not contain property '$property'.");

        if (func_num_args() === 2) {
            $this->assertEquals(
                $expectedValue,
                $view->{$property},
                "View property '$property' does not match the expected value."
            );
        }
    }

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

    protected function delete(string $uri): TestResponse
    {
        $_SERVER['REQUEST_METHOD'] = 'DELETE';

        $segments = array_values(array_filter(explode('/', trim($uri, '/'))));
        $controller = $segments[0] ?? 'home';
        $action = $segments[1] ?? 'index';
        $params = array_slice($segments, 2);

        try {
            $output = $this->controllerOutput($controller, $action, $params);
            return new TestResponse($output, 200);
        } catch (\Exception $e) {
            return new TestResponse($e->getMessage(), 500);
        } finally {
            unset($_SERVER['REQUEST_METHOD']);
        }
    }

    /**
     * Simulates an HTTP GET request to a given URI by resolving and executing
     * the corresponding controller and action, capturing the output.
     *
     * Supports URI segments in the form of /controller/action/param1/param2,
     * and maps them to a controller class and action method with optional
     * parameters passed positionally.
     *
     * Example:
     * - get('/')                → HomeController::indexAction()
     * - get('/products/show/3') → ProductsController::showAction(3)
     *
     * @param string \$uri The URI string, e.g., '/home/index' or '/products/show/3'
     * @return \Core\Lib\Testing\TestResponse The response object containing status and content
     */
    protected function get(string $uri): TestResponse
    {
        $segments = array_values(array_filter(explode('/', trim($uri, '/'))));

        $controller = $segments[0] ?? 'home';
        $action = $segments[1] ?? 'index';
        $params = array_slice($segments, 2);

        try {
            $output = $this->controllerOutput($controller, $action, $params);
            return new TestResponse($output, 200);
        } catch (\Exception $e) {
            return new TestResponse($e->getMessage(), 404);
        }
    }

    /**
     * Create a mock file for actions that require file input in form submissions.
     *
     * @param string $files The name of the $_FILES field.
     * @return void
     */
    protected function mockFile(string $files) {
        return $_FILES[$files] = [
            'name' => '',
            'type' => '',
            'tmp_name' => '',
            'error' => 4, // No file uploaded
            'size' => 0
        ];
    }

    /**
     * Simulates a POST request by setting $_POST data and executing the specified
     * controller and action. Returns a TestResponse with the output and status.
     *
     * @param string $uri The URI to simulate, e.g., '/login'
     * @param array $data The POST data to inject (e.g., ['email' => 'foo@bar.com'])
     * @return \Core\Lib\Testing\TestResponse The test response object
     */
    protected function post(string $uri, array $data = []): TestResponse
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $_POST = $data;
        $_REQUEST = $data; // ✅ Ensure Input::get() works correctly
        $_SERVER['REQUEST_METHOD'] = 'POST'; // ✅ Fix your test case

        $segments = array_values(array_filter(explode('/', trim($uri, '/'))));
        $controller = $segments[0] ?? 'home';
        $action = $segments[1] ?? 'index';
        $params = array_slice($segments, 2);

        try {
            $output = $this->controllerOutput($controller, $action, $params);
            return new TestResponse($output, 200);
        } catch (\Exception $e) {
            return new TestResponse($e->getMessage(), 500);
        } finally {
            // Clean up
            $_POST = [];
            $_REQUEST = [];
            unset($_SERVER['REQUEST_METHOD']);
        }
    }

    /**
     * Simulates a DELETE request to a specified URI. This sets the request method
     * to DELETE and runs the matching controller action.
     *
     * @param string $uri The URI to simulate, e.g., '/posts/10'
     * @return \Core\Lib\Testing\TestResponse The test response object
     */
    protected function put(string $uri, array $data = []): TestResponse
    {
        $_POST = $data;
        $_SERVER['REQUEST_METHOD'] = 'PUT';

        $segments = array_values(array_filter(explode('/', trim($uri, '/'))));
        $controller = $segments[0] ?? 'home';
        $action = $segments[1] ?? 'index';
        $params = array_slice($segments, 2);

        try {
            $output = $this->controllerOutput($controller, $action, $params);
            return new TestResponse($output, 200);
        } catch (\Exception $e) {
            return new TestResponse($e->getMessage(), 500);
        } finally {
            $_POST = [];
            unset($_SERVER['REQUEST_METHOD']);
        }
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