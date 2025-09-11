/**
 * Performs a same-origin GET request and returns parsed JSON.
 *
 * Behavior:
 * - Merges optional `query` params into the URL via `URLSearchParams`.
 * - Sends the `X-Requested-With: XMLHttpRequest` header and includes cookies (`credentials: 'same-origin'`).
 * - Parses the response as JSON; if parsing fails, falls back to `{}`.
 * - Throws an `Error` when the HTTP status is not OK (non-2xx) **or** when the JSON payload contains `{ success: false }`.
 *   The thrown error includes `.status` (number) and `.data` (the parsed JSON).
 *
 * @template T
 * @param {string} path
 *   Relative or absolute path on the same origin (e.g., `"/api/weather"`).
 * @param {{ query?: Record<string, string|number|boolean|string[]|number[]|boolean[]> }} [options]
 *   Optional options object.
 *   - `query`: Key/value pairs to append to the URL. Arrays are supported and will be serialized by `URLSearchParams`.
 *
 * @returns {Promise<T>}
 *   Resolves with the parsed JSON payload typed as `T`.
 *
 * @throws {Error & { status: number, data: any }}
 *   Throws when `response.ok` is false or when the payload has `success === false`.
 *
 * @example
 * // Simple GET with query params
 * const data = await apiGet('/api/weather', {
 *   query: { q: 'Austin', units: 'imperial' }
 * });
 *
 * @example
 * // Error handling pattern
 * try {
 *   const user = await apiGet('/api/me');
 * } catch (err) {
 *   console.error(err.status, err.message);
 *   // Optional: inspect server payload
 *   console.error(err.data);
 * }
 */
export async function apiGet(path, { query } = {}) {
    const url = new URL(path, window.location.origin);

    if(query) {
        url.search = new URLSearchParams(query).toString();
    }

    const response = await fetch(url, {
        credentials: 'same-origin',
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    });;

    const json = await response.json().catch(() => ({}));

    if(!response.ok || json?.success === false) {
        const error = new Error(json?.message || response.statusText);
        error.status = response.status;
        error.data = json;
        throw error;
    }
    return json;
}

/**
 * Sends a same-origin JSON **POST** request and returns the parsed JSON payload.
 *
 * Behavior:
 * - Appends optional `query` params to the URL via `URLSearchParams`.
 * - Sends `Content-Type: application/json` and `X-Requested-With: XMLHttpRequest`.
 * - Uses `credentials: 'same-origin'` so cookies/CSRF tokens flow for your app.
 * - Parses the response as JSON; if parsing fails, falls back to `{}`.
 * - Throws an `Error` when the HTTP status is not OK **or** the JSON includes `{ success: false }`.
 *   The thrown error includes `.status` (number) and `.data` (the parsed JSON).
 *
 * @template T
 * @param {string} path
 *   Relative or absolute path on the same origin (e.g., `"/api/weather"`).
 * @param {Record<string, any>} [body={}]
 *   Object to JSON-encode as the request body.
 * @param {{ query?: Record<string, string | number | boolean | string[] | number[] | boolean[]> }} [options]
 *   Optional options object.
 *   - `query`: Key/value pairs to append to the URL query string.
 *
 * @returns {Promise<T>}
 *   Resolves with the parsed JSON payload typed as `T`.
 *
 * @throws {Error & { status: number, data: any }}
 *   Throws when `response.ok` is false or when the payload has `success === false`.
 *
 * @example
 * // Submit a login form
 * const result = await apiPost('/api/auth/login', {
 *   username: 'chad',
 *   password: 'secret'
 * });
 *
 * @example
 * // With query params and error handling
 * try {
 *   const data = await apiPost('/api/weather', { unit: 'imperial' }, { query: { q: 'Austin' } });
 * } catch (err) {
 *   console.error(err.status, err.message);
 *   console.error('Server payload:', err.data);
 * }
 */
export async function apiPost(path, body = {}, { query } = {}) {
  const url = new URL(path, window.location.origin);
  if (query) url.search = new URLSearchParams(query).toString();

  const res = await fetch(url, {
    method: 'POST',
    credentials: 'same-origin',
    headers: {
      'Content-Type': 'application/json',
      'X-Requested-With': 'XMLHttpRequest'
    },
    body: JSON.stringify(body)
  });

  const json = await res.json().catch(() => ({}));
  if (!res.ok || json?.success === false) {
    const err = new Error(json?.message || res.statusText);
    err.status = res.status; err.data = json;
    throw err;
  }
  return json;
}