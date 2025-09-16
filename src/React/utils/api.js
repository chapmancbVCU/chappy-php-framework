import { useEffect, useState, useRef } from 'react';

export function apiDelete(path, body, opts) {
  // Some APIs accept a body with DELETE; pass it only if given.
  return apiRequest('DELETE', path, body == null ? opts : { ...opts, body });
}

export async function apiGet(path, opts) {
  return apiRequest('GET', path, opts);
}

export function apiPatch(path, body = {}, opts) {
  return apiRequest('PATCH', path, { ...opts, body });
}

export function apiPost(path, body = {}, opts) {
  return apiRequest('POST', path, { ...opts, body });
}

export function apiPut(path, body = {}, opts) {
  return apiRequest('PUT', path, { ...opts, body });
}

async function apiRequest(method, path, opts = {}) {
  const { query, body, headers, signal} = opts;
  const url = new URL(path, window.location.origin);

  if(query) {
    url.search = new URLSearchParams(query).toString();
  }

  const isBodyAllowed = method !== 'GET' && method !== 'DELETE' ? true : body != null;
  const init = {
    method,
    credentials: 'same-origin',
    headers: {
      'X-Requested-With': 'XMLHttpRequest',
      ...(headers || {}),
    },
    signal,
  };

  if(isBodyAllowed) {
    // If body is FormData/Blob/URLSearchParams/etc., let the browser set headers.
    const isMultipart =
      body instanceof FormData ||
      body instanceof Blob ||
      body instanceof ArrayBuffer ||
      body instanceof URLSearchParams ||
      (typeof ReadableStream !== 'undefined' && body instanceof ReadableStream);

    if(isMultipart) {
      init.body = body;
    } else {
      init.headers['Content-Type'] = 'application/json';
      init.body = JSON.stringify(body);
    }
  }

  const res = await fetch(url, init);

  // Handle 204/empty responses gracefully.
  const noBodyStatus = res.status === 204 || res.status === 205 || res.status === 304 || init.method === 'HEAD';
  let json = {};
  if (!noBodyStatus) {
    const ct = res.headers.get('content-type') || '';
    if (ct.includes('application/json') || ct.endsWith('+json')) {
      json = await res.json().catch(() => ({}));
    }
  }

  if(!res.ok || /** @type {any} */ (json)?.success === false) {
    const err = new Error(/** @type {any} */ (json)?.message || res.statusText);
    // @ts-ignore annotate for consumers
    err.status = res.status;
    // @ts-ignore annotate for consumers
    err.data = json;
    throw err;
  }

  // @ts-ignore generic T
  return json;
}

/**
 * React hook to run an async function and manage `{ data, loading, error }` state,
 * with automatic cancellation between reruns and unmounts.
 *
 * Behavior:
 * - Invokes `asyncFn` immediately and on every change of `deps`.
 * - Provides an `AbortSignal` to `asyncFn` so you can cancel in-flight requests.
 * - Sets `{ loading: true }` before each run, stores the resolved `data`,
 *   and captures any non-abort errors in `error`.
 * - On cleanup (deps change/unmount), aborts the previous run to avoid race conditions.
 *
 * @template T
 * @typedef {Object} UseAsyncState
 * @property {T|null} data   Resolved data from `asyncFn`, or `null` before/if it resolves.
 * @property {boolean} loading  `true` while a run is in progress.
 * @property {unknown|null} error  The caught error (excluding `AbortError`), or `null`.
 *
 * @param {(ctx: { signal: AbortSignal }) => Promise<T>} asyncFn
 *   The async function to execute. It will be called with an object containing
 *   an `AbortSignal` for cancellation support. If your underlying API uses `fetch`,
 *   pass this signal to it.
 *
 * @param {unknown[]} [deps=[]]
 *   Dependency array that controls when the async work reruns. Treat this like
 *   a `useEffect` dependency list. For best results, wrap `asyncFn` in `useCallback`
 *   so its identity is stable across renders.
 *
 * @returns {UseAsyncState<T>}
 *   The current `{ data, loading, error }` state for the async operation.
 *
 * @example
 * // Using with a fetcher that accepts AbortSignal
 * const state = useAsync(
 *   ({ signal }) => apiGet('/api/weather', { query: { q: city, units }, signal }),
 *   [city, units]
 * );
 *
 * @example
 * // Using with raw fetch
 * const userState = useAsync(async ({ signal }) => {
 *   const res = await fetch('/api/me', { signal, credentials: 'same-origin' });
 *   if (!res.ok) throw new Error(res.statusText);
 *   return res.json();
 * }, []);
 */
export function useAsync(asyncFn, deps = []) {
  const [state, setState] = useState({ data: null, loading: true, error: null });
  const abortRef = useRef(new AbortController());

  useEffect(() => {
    // new signal per run
    abortRef.current.abort();
    abortRef.current = new AbortController();

    let alive = true;
    setState({ data: null, loading: true, error: null });

    (async () => {
      try {
        const data = await asyncFn({ signal: abortRef.current.signal });
        if (alive) setState({ data, loading: false, error: null });
      } catch (error) {
        if (alive && error?.name !== 'AbortError') {
          setState({ data: null, loading: false, error });
        }
      }
    })();

    return () => { alive = false; abortRef.current.abort(); };
  // eslint-disable-next-line react-hooks/exhaustive-deps
  }, deps);

  return state; // { data, loading, error }
}