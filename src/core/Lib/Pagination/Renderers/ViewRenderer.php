<?php
declare(strict_types=1);
namespace Core\Lib\Pagination\Renderers;

use Core\Lib\Pagination\Pagination;
use RuntimeException;

/**
 * Renders pagination through a plain PHP view partial, so an app can fully
 * own the markup (and publish/override it like other Core views). The
 * partial receives $pagination.
 */
class ViewRenderer implements PaginationRenderer {
    public function __construct(protected string $viewPath) {}

    public function render(Pagination $pagination): string {
        if (!is_file($this->viewPath)) {
            throw new RuntimeException("Pagination view not found: {$this->viewPath}");
        }
        ob_start();
        try {
            (static function (string $__view, Pagination $pagination): void {
                require $__view;
            })($this->viewPath, $pagination);
        } catch (\Throwable $e) {
            ob_end_clean();
            throw $e;
        }
        return (string)ob_get_clean();
    }
}
