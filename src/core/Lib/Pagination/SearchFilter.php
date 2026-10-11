<?php
declare(strict_types=1);
namespace Core\Lib\Pagination;

/**
 * Turns request input (search term, filters, sort) into find() params using
 * whitelists only. Column names never come from user input directly, so the
 * generated SQL is safe to concatenate; all values go through bind.
 *
 * Example:
 *   $filter = SearchFilter::fromRequest($request)
 *       ->where('id != ?', [$currentUserId])
 *       ->searchable(['fname', 'lname', 'email', 'username'])
 *       ->filterable(['acl' => ['Admin', 'Manager', '']])
 *       ->sortable(['created_at', 'lname', 'email'], 'created_at', 'desc');
 */
class SearchFilter {
    /** Raw request input for the keys this class understands. */
    protected array $input;

    protected string $searchParam = 'q';
    protected string $sortParam = 'sort';
    protected string $dirParam = 'dir';

    /** @var string[] Columns matched with LIKE against the search term. */
    protected array $searchable = [];

    /** @var array<string, array|null> column => allowed values (null = any). */
    protected array $filterable = [];

    /** @var string[] Columns the user may sort by. */
    protected array $sortable = [];
    protected string $defaultSort = '';
    protected string $defaultDir = 'asc';

    /** @var string[] Fixed conditions that always apply (e.g. exclude current user). */
    protected array $baseConditions = [];
    protected array $baseBind = [];

    /**
     * @param array $input Request input, typically all GET params.
     */
    public function __construct(array $input = []) {
        $this->input = $input;
    }

    /**
     * Build from the request object. Assumes $request->get() with no key
     * returns all GET params; adjust if your Request differs.
     */
    public static function fromRequest(object $request): static {
        $input = $request->get();
        return new static(is_array($input) ? $input : []);
    }

    /* ---------------------------------------------------------------------
     * Configuration (developer-controlled whitelists)
     * ------------------------------------------------------------------- */

    /** Add a fixed condition that is always applied. */
    public function where(string $condition, array $bind = []): static {
        $this->baseConditions[] = $condition;
        array_push($this->baseBind, ...$bind);
        return $this;
    }

    /** @param string[] $columns */
    public function searchable(array $columns): static {
        $this->searchable = array_values(array_filter($columns, [$this, 'isSafeColumn']));
        return $this;
    }

    /**
     * @param array $filters Either ['status'] (any value) or
     *                       ['status' => ['active', 'banned']] (allowed values).
     */
    public function filterable(array $filters): static {
        foreach ($filters as $key => $value) {
            [$column, $allowed] = is_int($key) ? [$value, null] : [$key, $value];
            if ($this->isSafeColumn($column)) {
                $this->filterable[$column] = $allowed;
            }
        }
        return $this;
    }

    /** @param string[] $columns */
    public function sortable(array $columns, string $default = '', string $defaultDir = 'asc'): static {
        $this->sortable = array_values(array_filter($columns, [$this, 'isSafeColumn']));
        $this->defaultSort = in_array($default, $this->sortable, true) ? $default : '';
        $this->defaultDir = strtolower($defaultDir) === 'desc' ? 'desc' : 'asc';
        return $this;
    }

    /** Rename the query params (e.g. two filtered lists on one page). */
    public function params(string $search = 'q', string $sort = 'sort', string $dir = 'dir'): static {
        $this->searchParam = $search;
        $this->sortParam = $sort;
        $this->dirParam = $dir;
        return $this;
    }

    /* ---------------------------------------------------------------------
     * Resolved (validated) values
     * ------------------------------------------------------------------- */

    /** Trimmed search term, or '' when none. */
    public function term(): string {
        $term = $this->input[$this->searchParam] ?? '';
        return is_string($term) ? trim($term) : '';
    }

    /** Active filters after whitelist validation. Empty values are ignored. */
    public function activeFilters(): array {
        $active = [];
        foreach ($this->filterable as $column => $allowed) {
            $value = $this->input[$column] ?? null;
            if (!is_scalar($value) || (string)$value === '') continue;
            $value = (string)$value;
            if ($allowed !== null && !in_array($value, array_map('strval', $allowed), true)) continue;
            $active[$column] = $value;
        }
        return $active;
    }

    /** Validated sort column ('' if none). */
    public function sortColumn(): string {
        $sort = $this->input[$this->sortParam] ?? '';
        return is_string($sort) && in_array($sort, $this->sortable, true) ? $sort : $this->defaultSort;
    }

    /** Validated direction: 'asc' or 'desc'. */
    public function sortDirection(): string {
        $dir = $this->input[$this->dirParam] ?? '';
        if (!is_string($dir) || !in_array(strtolower($dir), ['asc', 'desc'], true)) {
            return $this->defaultDir;
        }
        return strtolower($dir);
    }

    /** True when the user has searched or filtered (useful for "Clear" links). */
    public function isActive(): bool {
        return $this->term() !== '' || $this->activeFilters() !== [];
    }

    /* ---------------------------------------------------------------------
     * Output
     * ------------------------------------------------------------------- */

    /**
     * Params for Model::find() / Model::findTotal(). Contains no limit/offset,
     * so the same array works for both the count and the page query.
     */
    public function findParams(): array {
        $conditions = $this->baseConditions;
        $bind = $this->baseBind;

        $term = $this->term();
        if ($term !== '' && $this->searchable !== []) {
            // '!' as escape char: works in MySQL, PostgreSQL and SQLite alike.
            $like = '%' . str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $term) . '%';
            $parts = [];
            foreach ($this->searchable as $column) {
                $parts[] = "{$column} LIKE ? ESCAPE '!'";
                $bind[] = $like;
            }
            $conditions[] = '(' . implode(' OR ', $parts) . ')';
        }

        foreach ($this->activeFilters() as $column => $value) {
            $conditions[] = "{$column} = ?";
            $bind[] = $value;
        }

        $params = [];
        if ($conditions !== []) {
            $params['conditions'] = implode(' AND ', $conditions);
            $params['bind'] = $bind;
        }
        if (($sort = $this->sortColumn()) !== '') {
            $params['order'] = $sort . ' ' . strtoupper($this->sortDirection());
        }
        return $params;
    }

    /** Only params the user actually set, for carrying through page links. */
    public function query(): array {
        $query = [];
        if (($term = $this->term()) !== '') $query[$this->searchParam] = $term;
        $query += $this->activeFilters();
        $sort = $this->input[$this->sortParam] ?? null;
        if (is_string($sort) && in_array($sort, $this->sortable, true)) {
            $query[$this->sortParam] = $sort;
            $query[$this->dirParam] = $this->sortDirection();
        }
        return $query;
    }

    /**
     * URL for a sortable column header: toggles direction when already
     * sorted by that column. Resets to page 1.
     */
    public function sortUrl(string $column, string $path = ''): string {
        $dir = ($this->sortColumn() === $column && $this->sortDirection() === 'asc') ? 'desc' : 'asc';
        $query = array_merge($this->query(), [$this->sortParam => $column, $this->dirParam => $dir]);
        return $path . '?' . http_build_query($query);
    }

    /** Allows plain or table-qualified identifiers only: name, users.name. */
    protected function isSafeColumn(mixed $column): bool {
        return is_string($column)
            && preg_match('/^[A-Za-z_][A-Za-z0-9_]*(\.[A-Za-z_][A-Za-z0-9_]*)?$/', $column) === 1;
    }
}
