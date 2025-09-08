<?php
declare(strict_types=1);
namespace Core;
use Core\Application;
use Core\Lib\Utilities\Env;
use Core\Lib\Testing\ApplicationTestCase;

/**
 * This is the parent Controller class.  It describes functions that are 
 * available to all classes that extends this Controller class.
 */
#[\AllowDynamicProperties]
class Controller extends Application {
    protected $_action;
    protected $_controller;
    public $request;
    public $view;

    /**
     * Constructor for the parent Controller class.  This constructor gets 
     * called when an instance of the child class is instantiated.
     *
     * @param string $controller The name of the controller obtained while 
     * parsing the URL.
     * @param string $action The name of the action specified in the path of 
     * the URL.
     */
    public function __construct(string $controller, string $action) {
        parent::__construct();
        $this->_controller = $controller;
        $this->_action = $action;
        $this->request = new Input();
        $this->view = new View();
        $this->onConstruct();
    }

    /**
     * Sample jsonResponse for supporting AJAX requests.
     *
     * @param mixed $data The JSON response.
     * @return void
     */
    public function jsonResponse(mixed $data, int $status = 200, array $extraHeaders = []): void {
        // CORS - keep '*' only for public, no-credentials endpoints


        header("Access-Control-Allow-Origin: *");
        header("Content-Type: application/json; charset=UTF-8");
        http_response_code(200);
        echo json_encode($res);
        exit;
    }

    /**
     * Captures the current View instance during testing so that test assertions
     * (e.g., assertViewContains) can access view-bound variables.
     *
     * This method should be called in controller actions before rendering a view.
     * It stores the View object in ApplicationTestCase::$controllerOutput['view']
     * when the application is running in the 'testing' environment.
     *
     * @param \Core\View $view The View instance to capture.
     * @return void
     */
    protected function logViewForTesting(View $view): void {
        if (Env::get('APP_ENV') === 'testing') {
            ApplicationTestCase::$controllerOutput['view'] = $view;
        }
    }

    /**
     * Function implemented by child model classes when models are 
     * instantiated.
     *
     * @return void
     */
    public function onConstruct(): void {}
}