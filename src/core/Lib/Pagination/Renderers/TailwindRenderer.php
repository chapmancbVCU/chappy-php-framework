<?php
declare(strict_types=1);
namespace Core\Lib\Pagination\Renderers;

/**
 * Tailwind CSS pagination controls. Utility classes are grouped in
 * properties so apps can subclass and restyle without rewriting markup.
 */
class TailwindRenderer extends AbstractHtmlRenderer {
    protected string $base = 'inline-flex items-center px-3 py-2 text-sm border';
    protected string $linkClass = 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50';
    protected string $activeClass = 'bg-blue-600 text-white border-blue-600';
    protected string $disabledClass = 'bg-white text-gray-300 border-gray-200 cursor-not-allowed';

    protected function link(string $url, string $label, ?string $ariaLabel = null): string {
        $aria = $ariaLabel ? ' aria-label="' . $this->e($ariaLabel) . '"' : '';
        return '<a href="' . $this->e($url) . '" class="' . $this->base . ' ' . $this->linkClass . '"' . $aria . '>'
            . $label . '</a>';
    }

    protected function active(string $label): string {
        return '<span aria-current="page" class="' . $this->base . ' ' . $this->activeClass . '">' . $label . '</span>';
    }

    protected function disabled(string $label, string $ariaLabel): string {
        return '<span aria-disabled="true" aria-label="' . $this->e($ariaLabel) . '" class="'
            . $this->base . ' ' . $this->disabledClass . '">' . $label . '</span>';
    }

    protected function gap(): string {
        return '<span class="' . $this->base . ' ' . $this->disabledClass . '">&hellip;</span>';
    }

    protected function wrap(string $items): string {
        return '<nav aria-label="Page navigation" class="flex justify-center"><div class="inline-flex -space-x-px">'
            . $items . '</div></nav>';
    }
}
