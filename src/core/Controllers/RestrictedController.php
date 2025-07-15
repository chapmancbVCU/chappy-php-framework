<?php 
declare(strict_types=1);
namespace Core\Controllers;
use Core\Controller;
use Core\Lib\Logging\Logger;
/**
 * Implements support for the Restricted controller.  Interactions that the 
 * user performs that are restricted will result in a relevant view being 
 * rendered.
 */
class RestrictedController extends Controller {
    /**
     * Renders page when a bad csrf token is detected.
     *
     * @return void
     */
    public function badTokenAction(): void {
        Logger::log('Your token is corrupted', 'danger');
        $this->view->render('restricted.badToken', true, true);
    }
    
    /**
     * This controller's default action.
     *
     * @return void
     */
    public function indexAction(): void {
        $this->view->render('restricted.index', true, true);
    }

    /**
     * Handles case when controller is not found and sets 404 response.
     *
     * @param string $controllerName The name of the controller.
     * @return void
     */
    public function noControllerAction(string $controllerName): void {
        // Set HTTP response code
        http_response_code(404);

        $this->view->controllerName = $controllerName;
        $this->view->render('restricted.no_controller', true, true);
    }

    /**
     * Handles cases when view is not found and sets 404 response.
     *
     * @param string $actionName The name of the action/view.
     * @param string $controllerName The name of the controller.
     * @return void
     */
    public function notFoundAction(string $actionName, string $controllerName): void {
        // Set HTTP response code
        http_response_code(404);

        $this->view->actionName = $actionName;
        $this->view->controllerName = $controllerName;
        $this->view->render('restricted.not_found', true, true);
    }

    /**
     * Runs when the object is constructed.
     *
     * @return void
     */
    public function onConstruct(): void {
        $this->view->setLayout('internal');
    }
}