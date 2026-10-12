import { useCallback, useEffect, useRef, useState } from 'react';
import { apiGet, apiError } from '@chappy/utils/api';

/**
 * Drives a server-paginated, searchable list without full page reloads.
 *
 * The URL query string is the single source of truth: every change pushes
 * a history entry and fetches JSON from the same path (apiGet sends
 * X-Requested-With, so the controller can answer with JSON instead of HTML).
 * Back/forward re-fetch the matching state, and refreshing or sharing a
 * URL renders the same list server-side.
 *
 * @param {object} initialData Server-rendered props (e.g. { users, pagination, filter }).
 * @param {object} [options]
 * @param {string} [options.path] Endpoint to fetch; defaults to the current path.
 * @param {string} [options.pageParam] Name of the page query param.
 * @returns {{
 *   data: object,
 *   params: Record<string, string>,
 *   loading: boolean,
 *   error: string|null,
 *   update: (changes: Record<string, any>, opts?: { resetPage?: boolean, replace?: boolean }) => void,
 *   goToPage: (page: number) => void,
 *   reset: () => void,
 * }}
 */
export default function usePaginatedQuery(initialData, { path, pageParam = 'page' } = {}) {
    const [data, setData] = useState(initialData);
    const [params, setParams] = useState(() => readUrl());
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState(null);

    const paramsRef = useRef(params);
    const abortRef = useRef(null);

    const load = useCallback(async (query) => {
        abortRef.current?.abort();
        const controller = new AbortController();
        abortRef.current = controller;

        setLoading(true);
        setError(null);
        try {
            const json = await apiGet(path ?? window.location.pathname, { query, signal: controller.signal });
            if (abortRef.current === controller) setData(json);
        } catch (err) {
            if (err?.name !== 'AbortError') setError(apiError(err));
        } finally {
            if (abortRef.current === controller) setLoading(false);
        }
    }, [path]);

    /** Applies a new param set: state, history (push/replace/none), fetch. */
    const apply = useCallback((next, historyMode) => {
        paramsRef.current = next;
        setParams(next);
        if (historyMode) {
            const url = window.location.pathname + toSearch(next);
            window.history[historyMode === 'replace' ? 'replaceState' : 'pushState'](null, '', url);
        }
        load(next);
    }, [load]);

    /**
     * Merge changes into the current params. Empty values remove the param.
     * Any change other than the page itself resets to page 1.
     */
    const update = useCallback((changes, { resetPage = true, replace = false } = {}) => {
        const next = clean({ ...paramsRef.current, ...changes });
        if (resetPage && !(pageParam in changes)) delete next[pageParam];
        apply(next, replace ? 'replace' : 'push');
    }, [apply, pageParam]);

    const goToPage = useCallback((page) => {
        update({ [pageParam]: page > 1 ? page : '' }, { resetPage: false });
    }, [update, pageParam]);

    const reset = useCallback(() => apply({}, 'push'), [apply]);

    // Back/forward: load whatever state the URL now describes.
    useEffect(() => {
        const onPop = () => apply(readUrl(), null);
        window.addEventListener('popstate', onPop);
        return () => {
            window.removeEventListener('popstate', onPop);
            abortRef.current?.abort();
        };
    }, [apply]);

    return { data, params, loading, error, update, goToPage, reset };
}

/** Drops empty values and stringifies the rest. */
function clean(params) {
    const out = {};
    for (const [key, value] of Object.entries(params)) {
        if (value !== undefined && value !== null && value !== '') out[key] = String(value);
    }
    return out;
}

function readUrl() {
    return clean(Object.fromEntries(new URLSearchParams(window.location.search)));
}

function toSearch(params) {
    const qs = new URLSearchParams(params).toString();
    return qs ? `?${qs}` : '';
}
