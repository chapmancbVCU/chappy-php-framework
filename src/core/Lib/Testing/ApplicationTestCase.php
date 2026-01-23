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
use Core\Lib\Http\JsonResponse;
/**
 * Abstract class for test cases.
 */
abstract class ApplicationTestCase extends TestCase {
    use JsonResponse;

    /**
     * The controller output.
     * @var array
     */
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
     * @param array $urlSegments          Parameters to pass to the action
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
        try {
            call_user_func_array([$controller, $actionMethod], $urlSegments); // full support for routed parameters
            return ob_get_clean();
        } catch (\Throwable $e) {
            // Clean buffer to avoid risky test error
            if (ob_get_level() > 0) {
                ob_end_clean();
            }
            throw $e;
        }
    }

    /**
     * Simulates a DELETE request to a specified URI. This sets the request method
     * to DELETE and runs the matching controller action.
     *
     * @param string $uri The URI to simulate, e.g., '/posts/10'
     * @param array $data The DELETE data.
     * @return \Core\Lib\Testing\TestResponse The test response object
     */
    protected function delete(string $uri, array $data = []): TestResponse { 
        return $this->request('DELETE', $uri, $data); 
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
     * @param string $uri The URI string, e.g., '/home/index' or '/products/show/3'
     * @return \Core\Lib\Testing\TestResponse The response object containing status and content
     */
    protected function get(string $uri): TestResponse
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $segments = array_values(array_filter(explode('/', trim($uri, '/'))));

        $controller = $segments[0] ?? 'home';
        $action = $segments[1] ?? 'index';
        $params = array_slice($segments, 2);

        try {
            $output = $this->controllerOutput($controller, $action, $params);

            return new TestResponse($output, 200);
        } catch (\Exception $e) {
            return new TestResponse($e->getMessage(), 404);
        } finally {
            unset($_SERVER['REQUEST_METHOD']);
        }
    }

    protected function json(string $method, string $uri, array $data = []): TestResponse {
        $method = strtoupper($method);

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Enable test-friendly JSON responses
        JsonResponse::$testing = true;

        // Feed raw JSON body to JsonResponse::get()
        JsonResponse::$rawInputOverride = json_encode($data);

        $_SERVER['REQUEST_METHOD'] = $method;

        // Optional but useful if you ever check headers in your code
        $_SERVER['CONTENT_TYPE'] = 'application/json';

        $segments = array_values(array_filter(explode('/', trim($uri, '/'))));
        $controller = $segments[0] ?? 'home';
        $action = $segments[1] ?? 'index';
        $params = array_slice($segments, 2);

        try {
            $output = $this->controllerOutput($controller, $action, $params);

            // Prefer the status code your jsonResponse() set (if you added it)
            $status = JsonResponse::$lastStatus ?? 200;

            return new TestResponse($output, $status);
        } catch (\Exception $e) {
            return new TestResponse($e->getMessage(), 500);
        } finally {
            JsonResponse::$rawInputOverride = null;
            unset($_SERVER['REQUEST_METHOD'], $_SERVER['CONTENT_TYPE']);
        }
    }
    
    /**
     * Create a mock file for actions that require file input in form submissions.
     *
     * @param string $files The name of the $_FILES field.
     * @return void
     */
    protected function mockFile(string $files): void {
        $_FILES[$files] = [
            'name' => '',
            'type' => '',
            'tmp_name' => '',
            'error' => 4, // No file uploaded
            'size' => 0
        ];
    }

    /**
     * Simulates a PATCH request to a specified URI. This sets the request method
     * to PUT and runs the matching controller action.
     *
     * @param string $uri The URI to simulate, e.g., '/patch/10'
     * @param array $data The PATCH data.
     * @return \Core\Lib\Testing\TestResponse The test response object
     */
    protected function patch(string $uri, array $data = []): TestResponse { 
        return $this->request('PATCH', $uri, $data); 
    }

    /**
     * Simulates a POST request by setting $_POST data and executing the specified
     * controller and action. Returns a TestResponse with the output and status.
     *
     * @param string $uri The URI to simulate, e.g., '/login'
     * @param array $data The POST data to inject (e.g., ['email' => 'foo@bar.com'])
     * @return \Core\Lib\Testing\TestResponse The test response object
     */
    protected function post(string $uri, array $data = []): TestResponse { 
        return $this->request('POST', $uri, $data); 
    }

    /**
     * Simulates a PUT request to a specified URI. This sets the request method
     * to PUT and runs the matching controller action.
     *
     * @param string $uri The URI to simulate, e.g., '/posts/10'
     * @param array $data The PUT data.
     * @return \Core\Lib\Testing\TestResponse The test response object
     */
    protected function put(string $uri, array $data = []): TestResponse { 
        return $this->request('PUT', $uri, $data); 
    }

    protected function request(string $method, string $uri, array $data = []): TestResponse {
        $method = strtoupper($method);

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $_POST = $data;
        $_REQUEST = $data;
        $_SERVER['REQUEST_METHOD'] = $method;

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
            $_REQUEST = [];
            unset($_SERVER['REQUEST_METHOD']);
        }
    }

    protected function routeJson(string $method, string $pathInfo, array $payload = []): TestResponse {
        $method = strtoupper($method);

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $prevServer = $_SERVER;

        // Enable test-mode behavior in JsonResponse (no exit, no headers)
        JsonResponse::$testing = true;
        JsonResponse::$rawInputOverride = json_encode($payload);

        $_SERVER['REQUEST_METHOD'] = $method;
        $_SERVER['PATH_INFO'] = $pathInfo;
        $_SERVER['REQUEST_URI'] = $pathInfo;
        $_SERVER['CONTENT_TYPE'] = 'application/json';

        ob_start();
        try {
            \Core\Router::route();
            $output = ob_get_clean();

            $status = JsonResponse::$lastStatus ?? 200;
            return new TestResponse($output, $status);
        } catch (\Throwable $e) {
            if (ob_get_level() > 0) {
                ob_end_clean();
            }
            return new TestResponse($e->getMessage(), 500);
        } finally {
            JsonResponse::$rawInputOverride = null;
            $_SERVER = $prevServer;
        }
    }

    protected function routeRequest(string $method, string $pathInfo, array $data = []): TestResponse {
        $method = strtoupper($method);

        // Start session if needed (router checks Session)
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Backup globals
        $prevServer  = $_SERVER;
        $prevGet     = $_GET;
        $prevPost    = $_POST;
        $prevRequest = $_REQUEST;

        // Simulate request
        $_SERVER['REQUEST_METHOD'] = $method;
        $_SERVER['PATH_INFO'] = $pathInfo; // Router prefers PATH_INFO
        $_SERVER['REQUEST_URI'] = $pathInfo; // fallback behavior if needed

        if ($method === 'GET') {
            $_GET = $data;
            $_REQUEST = $data;
            $_POST = [];
        } else {
            $_POST = $data;
            $_REQUEST = $data;
            $_GET = [];
        }

        ob_start();
        try {
            \Core\Router::route();
            $output = ob_get_clean();
            return new TestResponse($output, 200);
        } catch (\Throwable $e) {
            if (ob_get_level() > 0) {
                ob_end_clean();
            }
            // Your router tends to redirect instead of throw for not-found,
            // so most true exceptions here are 500s.
            return new TestResponse($e->getMessage(), 500);
        } finally {
            // Restore globals
            $_SERVER  = $prevServer;
            $_GET     = $prevGet;
            $_POST    = $prevPost;
            $_REQUEST = $prevRequest;
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
        JsonResponse::$testing = true;

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