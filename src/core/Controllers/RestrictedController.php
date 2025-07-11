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
     * Runs when the object is constructed.
     *
     * @return void
     */
    public function onConstruct(): void {
        $this->view->setLayout('internal');
    }
}