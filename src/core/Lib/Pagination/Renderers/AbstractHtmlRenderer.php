<?php
declare(strict_types=1);
namespace Core\Lib\Pagination\Renderers;

use Core\Lib\Pagination\Pagination;

/**
 * Shared structure for HTML renderers. Subclasses only supply markup for
 * the wrapper, a link, an active page, a disabled item, and a gap.
 */
abstract class AbstractHtmlRenderer implements PaginationRenderer {
    public function render(Pagination $pagination): string {
        if (!$pagination->hasPages()) {
            return '';
        }

        $items = [];
        $prev = $pagination->previousPageUrl();
        $items[] = $prev === null
            ? $this->disabled('&laquo;', 'Previous')
            : $this->link($prev, '&laquo;', 'Previous');

        foreach ($pagination->pageRange() as $page) {
            if ($page === null) {
                $items[] = $this->gap();
            } elseif ($page === $pagination->currentPageNumber()) {
                $items[] = $this->active((string)$page);
            } else {
                $items[] = $this->link($pagination->url($page), (string)$page);
            }
        }

        $next = $pagination->nextPageUrl();
        $items[] = $next === null
            ? $this->disabled('&raquo;', 'Next')
            : $this->link($next, '&raquo;', 'Next');

        return $this->wrap(implode('', $items));
    }

    protected function e(string $value): string {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }

    /** $label is trusted markup (page number or entity), $url is escaped here. */
    abstract protected function link(string $url, string $label, ?string $ariaLabel = null): string;
    abstract protected function active(string $label): string;
    abstract protected function disabled(string $label, string $ariaLabel): string;
    abstract protected function gap(): string;
    abstract protected function wrap(string $items): string;
}
