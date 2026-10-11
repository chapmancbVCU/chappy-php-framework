<?php
declare(strict_types=1);
namespace Core\Lib\Pagination\Renderers;

/**
 * Bootstrap 5 pagination controls.
 */
class BootstrapRenderer extends AbstractHtmlRenderer {
    public function __construct(protected string $align = 'justify-content-center') {}

    protected function link(string $url, string $label, ?string $ariaLabel = null): string {
        $aria = $ariaLabel ? ' aria-label="' . $this->e($ariaLabel) . '"' : '';
        $text = $ariaLabel ? '<span aria-hidden="true">' . $label . '</span>' : $label;
        return '<li class="page-item"><a class="page-link" href="' . $this->e($url) . '"' . $aria . '>'
            . $text . '</a></li>';
    }

    protected function active(string $label): string {
        return '<li class="page-item active" aria-current="page"><span class="page-link">' . $label . '</span></li>';
    }

    protected function disabled(string $label, string $ariaLabel): string {
        return '<li class="page-item disabled"><span class="page-link" aria-label="' . $this->e($ariaLabel) . '">'
            . '<span aria-hidden="true">' . $label . '</span></span></li>';
    }

    protected function gap(): string {
        return '<li class="page-item disabled"><span class="page-link">&hellip;</span></li>';
    }

    protected function wrap(string $items): string {
        return '<nav aria-label="Page navigation"><ul class="pagination ' . $this->e($this->align) . '">'
            . $items . '</ul></nav>';
    }
}
