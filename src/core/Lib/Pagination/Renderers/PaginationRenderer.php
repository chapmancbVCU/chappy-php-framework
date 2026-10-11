<?php
declare(strict_types=1);
namespace Core\Lib\Pagination\Renderers;

use Core\Lib\Pagination\Pagination;

/**
 * Contract for anything that turns Pagination state into markup.
 */
interface PaginationRenderer {
    public function render(Pagination $pagination): string;
}
