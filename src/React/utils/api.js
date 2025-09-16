import { useEffect, useState, useRef } from 'react';

/**
 * Performs a same-origin GET request and returns parsed JSON.
 *
 * Behavior:
 * - Merges optional `query` params into the URL via `URLSearchParams`.
 * - Sends the `X-Requested-With: XMLHttpRequest` header and includes cookies (`credentials: 'same-origin'`).
 * - Supports request cancellation via an `AbortSignal` (`options.signal`).
 * - Parses the response as JSON; if parsing fails, falls back to `{}`.
 * - Throws an `Error` when the HTTP status is not OK (non-2xx) **or** when the JSON payload contains `{ success: false }`.
 *   The thrown error includes `.status` (number) and `.data` (the parsed JSON).
 *
 * @template T
 * @param {string} path
 *   Relative or absolute path on the same origin (e.g., `"/api/weather"`).
 * @param {{
 *   query?: Record<string, string | number | boolean | string[] | number[] | boolean[]>,
 *   signal?: AbortSignal
 * }} [options]
 *   Optional options object.
 *   - `query`: Key/value pairs to append to the URL. Arrays are supported and will be serialized by `URLSearchParams`.
 *   - `signal`: An `AbortSignal` to cancel the request. If aborted, the promise rejects with a DOMException whose `name` is `"AbortError"`.
 *
 * @returns {Promise<T>}
 *   Resolves with the parsed JSON payload typed as `T`.
 *
 * @throws {Error & { status: number, data: any }}
 *   Throws when `response.ok` is false or when the payload has `success === false`.
 *   May also reject with a DOMException `"AbortError"` if the provided `signal` aborts the request.
 *
 * @example
 * // Simple GET with query params
 * const data = await apiGet('/api/weather', {
 *   query: { q: 'Austin', units: 'imperial' }
 * });
 *
 * @example
 * // Abortable request with AbortController
 * const ac = new AbortController();
 * const p = apiGet('/api/weather', { query: { q: 'Austin' }, signal: ac.signal });
 * ac.abort(); // later, if needed
 * try {
 *   await p;
 * } catch (e) {
 *   if (e?.name === 'AbortError') {
 *     // handle cancellation
 *   }
 * }
 *
 * @example
 * // Error handling pattern
 * try {
 *   const user = await  @type {ReturnType<typeof apiGet>}  (apiGet('/api/me'));
 * } catch (err) {
 *   console.error(err.status, err.message);
 *   // Optional: inspect server payload
 *   console.error(err.data);
 * }
 */
export async function apiGet(path, { query, signal } = {}) {
  const url = new URL(path, window.location.origin);
  if (query) url.search = new URLSearchParams(query).toString();

  const res = await fetch(url, {
    credentials: 'same-origin',
    headers: { 'X-Requested-With': 'XMLHttpRequest' },
    signal
  });

  const json = await res.json().catch(() => ({}));
  if (!res.ok || json?.success === false) {
    const err = new Error(json?.message || res.statusText);
    err.status = res.status; err.data = json;
    throw err;
  }
  return json;
}

/**
 * Performs a same-origin **POST** request and returns parsed JSON.
 *
 * Behavior:
 * - Optionally merges `query` params into the URL via `URLSearchParams`.
 * - Sends JSON with `Content-Type: application/json` and the header `X-Requested-With: XMLHttpRequest`.
 * - Includes cookies (`credentials: 'same-origin'`) so session-based auth works.
 * - Supports cancellation via `AbortSignal` (`options.signal`).
 * - Parses the response as JSON; if parsing fails, falls back to `{}`.
 * - Throws an `Error` when the HTTP status is not OK (non-2xx) **or** when the JSON payload contains `{ success: false }`.
 *   The thrown error includes `.status` (number) and `.data` (the parsed JSON).
 *
 * @template T
 * @param {string} path
 *   Relative or absolute path on the same origin (e.g., `"/api/submit"`).
 * @param {unknown} [body={}]
 *   Arbitrary data to JSON-encode as the request body. Objects/arrays are typical.
 * @param {{
 *   query?: Record<string, string | number | boolean | string[] | number[] | boolean[]>,
 *   signal?: AbortSignal,
 *   headers?: Record<string, string>
 * }} [options]
 *   Optional options object.
 *   - `query`: Key/value pairs to append to the URL (arrays supported).
 *   - `signal`: An `AbortSignal` to cancel the request. If aborted, the promise rejects with a DOMException `"AbortError"`.
 *   - `headers`: Extra headers to merge into the request (e.g., `{ 'X-CSRF-Token': getCsrf() }`).
 *
 * @returns {Promise<T>}
 *   Resolves with the parsed JSON payload typed as `T`.
 *
 * @throws {Error & { status: number, data: any }}
 *   Throws when `response.ok` is false or when the payload has `success === false`.
 *   May also reject with `AbortError` if the provided `signal` aborts the request.
 *
 * @example
 * // Simple POST
 * const res = await apiPost('/api/profile', { name: 'Ada' });
 *
 * @example
 * // With query params and CSRF header
 * const res = await apiPost('/api/items', { title: 'Book' }, {
 *   query: { shelf: 'reading' },
 *   headers: { 'X-CSRF-Token': getCsrf() }
 * });
 *
 * @example
 * // Abortable request with AbortController
 * const ac = new AbortController();
 * const p = apiPost('/api/upload', { fileId }, { signal: ac.signal });
 * ac.abort(); // later, if needed
 * try {
 *   await p;
 * } catch (e) {
 *   if (e?.name === 'AbortError') {
 *     // handle cancellation
 *   }
 * }
 *
 * @example
 * // With a reusable hook (useAsync)
 * const state = useAsync(
 *   ({ signal }) => apiPost('/api/login', creds, { signal }),
 *   [JSON.stringify(creds)]
 * );
 */
export async function apiPost(
  path,
  body = {},
  { query, signal, headers } = {}
) {
  const url = new URL(path, window.location.origin);
  if (query) url.search = new URLSearchParams(query).toString();

  const res = await fetch(url, {
    method: 'POST',
    credentials: 'same-origin',
    headers: {
      'Content-Type': 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
      ...(headers || {}),
    },
    body: JSON.stringify(body),
    signal, // ✅ enables abort via useAsync
  });

  const json = await res.json().catch(() => ({}));
  if (!res.ok || json?.success === false) {
    const err = new Error(json?.message || res.statusText);
    err.status = res.status;
    err.data = json;
    throw err;
  }
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