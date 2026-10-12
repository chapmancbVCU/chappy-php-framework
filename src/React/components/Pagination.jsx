import React from 'react';

/**
 * Bootstrap 5 pagination controls driven by Pagination::toArray().
 *
 * Every page is a real link (href from the server), so middle-click and
 * "open in new tab" work. When onPageChange is given, normal clicks are
 * intercepted and handled client-side instead of reloading the page.
 *
 * @param {object} props
 * @param {object} props.pagination Output of Pagination::toArray().
 * @param {(page: number) => void} [props.onPageChange] Client-side handler.
 * @param {string} [props.className] Extra classes for the <ul>.
 * @param {string} [props.label] aria-label for the <nav>.
 * @returns {JSX.Element|null}
 */
export default function Pagination({
    pagination,
    onPageChange,
    className = 'justify-content-center',
    label = 'Page navigation',
}) {
    if (!pagination || pagination.total_pages <= 1) return null;

    const { current_page, links = [], prev_url, next_url } = pagination;

    const handle = (page) => (e) => {
        if (!onPageChange) return;
        // Let modified clicks (new tab/window) behave like normal links.
        if (e.metaKey || e.ctrlKey || e.shiftKey || e.altKey || e.button !== 0) return;
        e.preventDefault();
        onPageChange(page);
    };

    return (
        <nav aria-label={label}>
            <ul className={`pagination ${className}`.trim()}>
                <EdgeItem url={prev_url} onClick={handle(current_page - 1)} label="Previous" symbol="&laquo;" />

                {links.map((link, i) => {
                    if (link.page === null) {
                        return (
                            <li key={`gap-${i}`} className="page-item disabled">
                                <span className="page-link">&hellip;</span>
                            </li>
                        );
                    }
                    if (link.active) {
                        return (
                            <li key={link.page} className="page-item active" aria-current="page">
                                <span className="page-link">{link.page}</span>
                            </li>
                        );
                    }
                    return (
                        <li key={link.page} className="page-item">
                            <a className="page-link" href={link.url} onClick={handle(link.page)}>
                                {link.page}
                            </a>
                        </li>
                    );
                })}

                <EdgeItem url={next_url} onClick={handle(current_page + 1)} label="Next" symbol="&raquo;" />
            </ul>
        </nav>
    );
}

/** Previous/next item; disabled when there is no URL. */
function EdgeItem({ url, onClick, label, symbol }) {
    const content = <span aria-hidden="true" dangerouslySetInnerHTML={{ __html: symbol }} />;
    if (!url) {
        return (
            <li className="page-item disabled">
                <span className="page-link" aria-label={label}>{content}</span>
            </li>
        );
    }
    return (
        <li className="page-item">
            <a className="page-link" href={url} onClick={onClick} aria-label={label}>{content}</a>
        </li>
    );
}

/**
 * "Showing 11–20 of 45 users". Renders nothing when there are no results.
 *
 * @param {object} props
 * @param {object} props.pagination Output of Pagination::toArray().
 * @param {string} [props.noun] Item name, e.g. "users".
 * @param {string} [props.className]
 */
export function PaginationSummary({ pagination, noun = 'results', className = 'text-muted small mb-2' }) {
    if (!pagination || pagination.total === 0) return null;
    return (
        <p className={className}>
            Showing {pagination.from}–{pagination.to} of {pagination.total} {noun}
        </p>
    );
}

/**
 * Sortable table header cell. Uses the server-resolved sort state from
 * SearchFilter::toArray() so the default sort shows its arrow too.
 * Clicking the active column toggles direction; a new column starts asc.
 *
 * @param {object} props
 * @param {string} props.column Column name (must be whitelisted server-side).
 * @param {object} props.filter Output of SearchFilter::toArray().
 * @param {(column: string, dir: 'asc'|'desc') => void} props.onSort
 * @param {React.ReactNode} props.children Header label.
 */
export function SortableTh({ column, filter, onSort, children, className }) {
    const active = filter?.sort === column;
    const dir = active ? filter.dir : null;
    const nextDir = active && dir === 'asc' ? 'desc' : 'asc';
    const ariaSort = active ? (dir === 'asc' ? 'ascending' : 'descending') : 'none';

    return (
        <th aria-sort={ariaSort} className={className}>
            <button
                type="button"
                className="btn btn-link p-0 text-reset text-decoration-none fw-bold"
                onClick={() => onSort(column, nextDir)}
            >
                {children}
                {active && <span aria-hidden="true">{dir === 'asc' ? ' ▲' : ' ▼'}</span>}
            </button>
        </th>
    );
}
