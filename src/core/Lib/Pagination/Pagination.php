<?php
declare(strict_types=1);
namespace Core\Lib\Pagination;

use Core\Lib\Pagination\Renderers\BootstrapRenderer;
use Core\Lib\Pagination\Renderers\PaginationRenderer;

/**
 * Holds pagination state and builds page URLs. Rendering is delegated to a
 * PaginationRenderer so the same object can drive Bootstrap, Tailwind, a
 * custom layout, or a React view (via toArray()).
 */
class Pagination {
    protected int $currentPage;
    protected int $limit;
    protected int $totalItems;

    /** Query params preserved in every page link (search, filters, sort). */
    protected array $query = [];

    /** Base path for links. Empty string = relative to the current URL. */
    protected string $path = '';

    /** Name of the page query parameter. */
    protected string $pageName = 'page';

    /** Number of page links shown on each side of the current page. */
    protected int $window = 2;

    /** Renderer used by render() when none is passed. */
    protected static ?PaginationRenderer $defaultRenderer = null;

    /**
     * Constructor for Pagination class
     *
     * @param int $currentPage The current page that is selected.
     * @param int $limit The maximum number of items for a page.
     * @param int $totalItems The total number of items for a list.
     */
    public function __construct(int $currentPage = 1, int $limit = 10, int $totalItems = 0) {
        $this->limit = max(1, $limit);
        $this->totalItems = max(0, $totalItems);
        // Clamp so ?page=999 or ?page=-3 can't produce a bad offset.
        $this->currentPage = min(max(1, $currentPage), max(1, $this->totalPages()));
    }

    /**
     * Convenience factory that reads the page number from the request.
     *
     * @param object $request The request object.
     * @param int $limit The maximum number of items for a page.
     * @param int $totalItems The total number of items for a list.
     * @return static
     */
    public static function fromRequest(object $request, int $limit, int $totalItems): static {
        return new static(static::currentPage($request), $limit, $totalItems);
    }

    /**
     * Retrieves and validates the current page from the GET request.
     *
     * @param object $request The request object.
     * @param string $pageName Name of the page query parameter.
     * @return int The current page number (1 if missing or invalid).
     */
    public static function currentPage(object $request, string $pageName = 'page'): int {
        $page = filter_var(
            $request->get($pageName),
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1]]
        );
        return $page === false ? 1 : $page;
    }

    /* ---------------------------------------------------------------------
     * Fluent configuration
     * ------------------------------------------------------------------- */

    /**
     * Preserve the filter's active query params (search term, filters, sort)
     * in every generated page link.
     */
    public function withFilter(SearchFilter $filter): static {
        return $this->withQuery($filter->query());
    }

    /** Preserve arbitrary query params in every generated page link. */
    public function withQuery(array $query): static {
        unset($query[$this->pageName]);
        $this->query = $query;
        return $this;
    }

    /** Set a base path for links (e.g. '/admindashboard'). */
    public function withPath(string $path): static {
        $this->path = $path;
        return $this;
    }

    /** Change the page query parameter name (useful for two lists on one page). */
    public function withPageName(string $pageName): static {
        $this->pageName = $pageName;
        return $this;
    }

    /** Number of page links on each side of the current page. */
    public function withWindow(int $window): static {
        $this->window = max(0, $window);
        return $this;
    }

    /** Set the renderer used when render() is called without one. */
    public static function setDefaultRenderer(PaginationRenderer $renderer): void {
        static::$defaultRenderer = $renderer;
    }

    /* ---------------------------------------------------------------------
     * State
     * ------------------------------------------------------------------- */

    public function currentPageNumber(): int { return $this->currentPage; }
    public function limit(): int { return $this->limit; }
    public function totalItems(): int { return $this->totalItems; }
    public function pageName(): string { return $this->pageName; }

    /**
     * Calculates offset needed for pagination.
     *
     * @return int The offset
     */
    public function offset(): int {
        return ($this->currentPage - 1) * $this->limit;
    }

    /**
     * Returns total number of pages we need to handle.
     *
     * @return int The total number of pages to be managed by pagination.
     */
    public function totalPages(): int {
        return (int)ceil($this->totalItems / $this->limit);
    }

    /** True when there is more than one page (renderers output nothing otherwise). */
    public function hasPages(): bool {
        return $this->totalPages() > 1;
    }

    public function onFirstPage(): bool { return $this->currentPage <= 1; }
    public function onLastPage(): bool { return $this->currentPage >= $this->totalPages(); }

    /** 1-based index of the first item on this page (0 when empty). */
    public function firstItem(): int {
        return $this->totalItems === 0 ? 0 : $this->offset() + 1;
    }

    /** 1-based index of the last item on this page (0 when empty). */
    public function lastItem(): int {
        return min($this->offset() + $this->limit, $this->totalItems);
    }

    /* ---------------------------------------------------------------------
     * URLs
     * ------------------------------------------------------------------- */

    /**
     * Builds the URL for a page, keeping search/filter/sort params intact.
     *
     * @param int $page The page number.
     * @return string The URL (not HTML-escaped; renderers escape it).
     */
    public function url(int $page): string {
        $params = array_merge($this->query, [$this->pageName => $page]);
        return $this->path . '?' . http_build_query($params);
    }

    public function previousPageUrl(): ?string {
        return $this->onFirstPage() ? null : $this->url($this->currentPage - 1);
    }

    public function nextPageUrl(): ?string {
        return $this->onLastPage() ? null : $this->url($this->currentPage + 1);
    }

    /**
     * Sliding window of page numbers. null marks a gap (render as an ellipsis).
     * Example for page 10 of 20, window 2: [1, null, 8, 9, 10, 11, 12, null, 20]
     *
     * @return array<int|null>
     */
    public function pageRange(): array {
        $last = $this->totalPages();
        if ($last <= 1) {
            return $last === 1 ? [1] : [];
        }

        $start = max(2, $this->currentPage - $this->window);
        $end = min($last - 1, $this->currentPage + $this->window);

        $pages = [1];
        if ($start > 2) $pages[] = null;
        for ($i = $start; $i <= $end; $i++) $pages[] = $i;
        if ($end < $last - 1) $pages[] = null;
        $pages[] = $last;

        return $pages;
    }

    /* ---------------------------------------------------------------------
     * Query integration
     * ------------------------------------------------------------------- */

    /**
     * Builds find() params from a SearchFilter plus limit/offset.
     *
     * @param SearchFilter $filter The filter built from the request.
     * @return array Params ready for Model::find().
     */
    public function queryParams(SearchFilter $filter): array {
        return array_merge($filter->findParams(), [
            'limit' => $this->limit,
            'offset' => $this->offset(),
        ]);
    }

    /**
     * Builds parameters for pagination query (original API, kept for
     * existing callers).
     *
     * @param mixed $conditions The conditions for the query.
     * @param array $bind What we want to bind.
     * @param string $order The order we want the results returned.
     * @return array An array of params tailored for pagination.
     */
    public function paginationParams(mixed $conditions = '', array $bind = [], string $order = ''): array {
        $params = [
            'limit' => $this->limit,
            'offset' => $this->offset(),
        ];
        if ($conditions !== '' && $conditions !== null) $params['conditions'] = $conditions;
        if ($bind !== []) $params['bind'] = $bind;
        if ($order !== '') $params['order'] = $order;
        return $params;
    }

    /* ---------------------------------------------------------------------
     * Output
     * ------------------------------------------------------------------- */

    /**
     * Renders the controls with the given renderer, or the default one.
     */
    public function render(?PaginationRenderer $renderer = null): string {
        $renderer ??= static::$defaultRenderer ?? new BootstrapRenderer();
        return $renderer->render($this);
    }

    /**
     * Serializable state for React views or JSON APIs.
     */
    public function toArray(): array {
        return [
            'current_page' => $this->currentPage,
            'per_page' => $this->limit,
            'total' => $this->totalItems,
            'total_pages' => $this->totalPages(),
            'from' => $this->firstItem(),
            'to' => $this->lastItem(),
            'prev_url' => $this->previousPageUrl(),
            'next_url' => $this->nextPageUrl(),
            'links' => array_map(fn(?int $page) => [
                'page' => $page,
                'url' => $page === null ? null : $this->url($page),
                'active' => $page === $this->currentPage,
            ], $this->pageRange()),
        ];
    }

    /**
     * Renders Bootstrap 5 pagination controls (original static API).
     *
     * @deprecated Use $pagination->render() instead. A custom $base_url is
     *             no longer honored; use withPath()/withQuery().
     */
    public static function pagination(int $current_page, int $total_pages, string $base_url = '?page='): string {
        return (new static($current_page, 1, $total_pages))->render(new BootstrapRenderer());
    }
}
