<?php
declare(strict_types=1);
namespace Console\Helpers;

/**
 * Helper class for controller related console commands.
 */
class ControllerStubs {
    /**
     * The default template for a new controller.
     *
     * @param string $controllerName The name of the controller.
     * @param string $layout The layout to be set.
     * @return string The contents for a new controller.
     */
    public static function defaultTemplate(string $controllerName, string $layout): string {
        return <<<PHP
<?php
namespace App\Controllers;
use Core\Controller;

/**
 * Undocumented class
 */
class {$controllerName}Controller extends Controller {
    /**
     * Runs when the object is constructed.
     *
     * @return void
     */
    public function onConstruct(): void {
        \$this->view->setLayout('{$layout}');
    }
}
PHP;
    }

    /**
     * Template for a React-backed (JSON API) controller.  Uses the JsonResponse
     * trait and returns JSON instead of rendering a view.
     *
     * @param string $controllerName The name of the controller.
     * @param string $layout Unused for API controllers; kept for signature parity
     *                       with the other templates.
     * @return string The contents for a new React/API controller.
     */
    public static function reactTemplate(string $controllerName, string $layout): string {
        return <<<PHP
<?php
namespace App\Controllers;
use Core\Controller;
use Core\Lib\Http\JsonResponse;
use Throwable;

/**
 * Undocumented class
 */
class {$controllerName}Controller extends Controller {
    use JsonResponse;

    public function showAction(): void {
        // \$data = {$controllerName}::find();
        // \$this->jsonResponse(['success' => true, 'data' => \$data]);
    }

    public function storeAction() {
        try {
            if (!\$this->apiCsrfCheck()) {
                return \$this->jsonError('Corrupted token');
            }

            // \$model = new {$controllerName}();
            // Pass the model's allowlist so only approved fields are set from
            // the request. id, deleted, and anything not whitelisted are ignored.
            // \$model->assign(\$this->get(), {$controllerName}::whiteList, {$controllerName}::WHITELIST);
            // \$model->save();

            \$this->jsonResponse(['success' => true]);
        } catch (Throwable \$e) {
            \$this->jsonError('Server error', 500);
        }
    }

    public function patchAction(int \$id) {
        try {
            if (!\$this->apiCsrfCheck()) {
                return \$this->jsonError('Corrupted token');
            }

            // \$model = {$controllerName}::findById(\$id);
            // if (\$model) {
            //     \$model->assign(\$this->get(), {$controllerName}::whiteList, {$controllerName}::WHITELIST);
            //     \$model->save();
            // }

            \$this->jsonResponse(['success' => true]);
        } catch (Throwable \$e) {
            \$this->jsonError('Server error', 500);
        }
    }

    public function destroyAction(int \$id) {
        try {
            if (!\$this->apiCsrfCheck()) {
                return \$this->jsonError('Corrupted token');
            }

            // \$model = {$controllerName}::findById(\$id);
            // if (\$model) {
            //     \$model->delete();
            // }

            \$this->jsonResponse(['success' => true]);
        } catch (Throwable \$e) {
            \$this->jsonError('Server error', 500);
        }
    }
}
PHP;
    }

    /**
     * The template that contains additional useful functions for a controller.
     *
     * @param string $controllerName The name of the controller.
     * @param string $layout The layout to be set.
     * @return string The contents for a new controller.
     */
    public static function resourceTemplate(string $controllerName, string $layout): string {
        return <<<PHP
<?php
namespace App\Controllers;
use Core\Controller;

/**
 * Undocumented class
 */
class {$controllerName}Controller extends Controller {
    /**
     * Runs when the object is constructed.
     *
     * @return void
     */
    public function onConstruct(): void {
        \$this->view->setLayout('{$layout}');
    }

    public function indexAction(): void {
        //
    }

    public function addAction(): void {
        // \$model = new {$controllerName}();
        // if (\$this->request->isPost()) {
        //     \$this->request->csrfCheck();
        //     // Pass the model's allowlist so only approved fields are set
        //     // from the request. Anything not whitelisted (id, deleted, and
        //     // any sensitive columns) is ignored.
        //     \$model->assign(\$this->request->get(), {$controllerName}::whiteList, {$controllerName}::WHITELIST);
        //     if (\$model->save()) {
        //         redirect('{$controllerName}');
        //     }
        // }
    }

    public function deleteAction(): void {
        //
    }

    public function editAction(): void {
        //
    }

    public function updateAction(): void {
        // Load the existing record first, then apply the same allowlist as addAction:
        // \$model->assign(\$this->request->get(), {$controllerName}::whiteList, {$controllerName}::WHITELIST);
        // \$model->save();
    }
}
PHP;
    }
}