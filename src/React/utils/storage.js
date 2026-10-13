/**
 * @file Client-side storage utilities for Chappy.php React views.
 *
 * Provides namespaced wrappers around `localStorage` and `sessionStorage`
 * with JSON serialization, optional time-to-live (TTL) expiry, graceful
 * fallback to in-memory storage, and change events that keep components
 * in sync within the same tab and across tabs.
 *
 * Also exports React hooks (`useLocalStorage`, `useSessionStorage`) built
 * on `useSyncExternalStore`.
 *
 * Do not store authentication tokens, CSRF tokens, or personally
 * identifiable information. Web storage is readable by any script running
 * on the page.
 *
 * @module @chappy/utils/storage
 *
 * @example
 * import storage, { local, session, useLocalStorage } from '@chappy/utils/storage';
 *
 * local.set('weather.units', 'metric');
 * session.set('catalog.filters', { genre: 'rpg' }, { ttl: 900 });
 *
 * const [units, setUnits] = useLocalStorage('weather.units', 'imperial');
 */
import { useCallback, useMemo, useRef, useSyncExternalStore } from 'react';

/**
 * Prefix applied to every key written by this module. Set with the
 * `VITE_STORAGE_PREFIX` environment variable. Defaults to `chappy`.
 *
 * @type {string}
 */
const PREFIX = (import.meta.env.VITE_STORAGE_PREFIX ?? 'chappy') + ':';

/**
 * Name of the custom window event dispatched whenever a value is written,
 * removed, or cleared in the current tab.
 *
 * @type {string}
 */
export const CHANGE_EVENT = 'chappy:storage';

/**
 * The kind of web storage a store wraps.
 *
 * @typedef {'local' | 'session'} StorageType
 */

/**
 * Options accepted when writing a value.
 *
 * @typedef {Object} SetOptions
 * @property {number} [ttl] - Time to live in seconds. When omitted the
 * value never expires.
 */

/**
 * Detail payload of a {@link CHANGE_EVENT} event.
 *
 * @typedef {Object} StorageChangeDetail
 * @property {StorageType} type - The store that changed.
 * @property {string|null} key - The unprefixed key that changed, or `null`
 * when the whole store was cleared.
 */

/**
 * Minimal subset of the Web Storage API used by this module. Satisfied by
 * `window.localStorage`, `window.sessionStorage`, and the in-memory
 * fallback.
 *
 * @typedef {Object} StorageBackend
 * @property {(key: string) => (string|null)} getItem
 * @property {(key: string, value: string) => void} setItem
 * @property {(key: string) => void} removeItem
 * @property {(index: number) => (string|null)} key
 * @property {number} length
 */

/**
 * A namespaced storage wrapper.
 *
 * @typedef {Object} Store
 * @property {StorageType} type - Which web storage this store wraps.
 * @property {boolean} persistent - `true` when backed by real web storage,
 * `false` when using the in-memory fallback (data is lost on reload).
 * @property {(key: string) => string} fullKey - Returns the prefixed key.
 * @property {(key: string) => (string|null)} raw - Returns the raw stored
 * string for a key, or `null`.
 * @property {<T>(raw: (string|null), fallback: T) => (*|T)} decode - Parses
 * a raw stored string, returning `fallback` if missing, malformed, or expired.
 * @property {<T>(key: string, fallback?: T) => (*|T)} get - Reads a value.
 * @property {(key: string, value: *, options?: SetOptions) => boolean} set -
 * Writes a value.
 * @property {(key: string) => void} remove - Deletes a value.
 * @property {() => string[]} keys - Lists unprefixed keys owned by this module.
 * @property {() => void} clear - Deletes every key owned by this module.
 */

/**
 * Tests whether a web storage area is available and writable.
 *
 * Accessing or writing to storage can throw in private browsing modes,
 * when site data is blocked, or when the quota is exhausted.
 *
 * @param {StorageType} type - The storage area to test.
 * @returns {Storage|null} The storage object if usable, otherwise `null`.
 */
function probe(type) {
    try {
        const s = type === 'session' ? window.sessionStorage : window.localStorage;
        s.setItem(PREFIX + '__probe', '1');
        s.removeItem(PREFIX + '__probe');
        return s;
    } catch {
        return null;
    }
}

/**
 * Creates an in-memory object that mimics the Web Storage API. Used as a
 * fallback when real storage is unavailable. Data does not survive a reload.
 *
 * @returns {StorageBackend} An in-memory storage backend.
 */
function memoryBackend() {
    const m = new Map();
    return {
        getItem: k => (m.has(k) ? m.get(k) : null),
        setItem: (k, v) => m.set(k, String(v)),
        removeItem: k => m.delete(k),
        key: i => [...m.keys()][i] ?? null,
        get length() { return m.size; },
    };
}

/**
 * Creates a namespaced store for the given storage type.
 *
 * Values are saved as a JSON envelope of the form `{ value, exp }`, where
 * `exp` is an optional expiry timestamp in milliseconds.
 *
 * @param {StorageType} type - Which web storage to wrap.
 * @returns {Store} The store instance.
 */
function createStore(type) {
    const native = typeof window !== 'undefined' ? probe(type) : null;
    const backend = native ?? memoryBackend();

    /**
     * Returns the prefixed form of a key.
     *
     * @param {string} key - The unprefixed key.
     * @returns {string} The key with {@link PREFIX} applied.
     */
    const full = key => PREFIX + key;

    /**
     * Dispatches a {@link CHANGE_EVENT} so subscribers in this tab update.
     *
     * @param {string|null} key - The changed key, or `null` for a clear.
     * @returns {void}
     */
    const emit = key =>
        window.dispatchEvent(new CustomEvent(CHANGE_EVENT, { detail: { type, key } }));

    /**
     * Parses a raw stored string into its value.
     *
     * @template T
     * @param {string|null} raw - The raw string from storage.
     * @param {T} fallback - Returned when the value is missing, malformed,
     * or expired.
     * @returns {*|T} The stored value or `fallback`.
     */
    const decode = (raw, fallback) => {
        if (raw === null) return fallback;
        try {
            const { value, exp } = JSON.parse(raw);
            return exp && Date.now() > exp ? fallback : value;
        } catch {
            return fallback;
        }
    };

    return {
        type,
        persistent: !!native,
        fullKey: full,

        /**
         * Returns the raw stored string for a key without parsing it.
         * Used by the hooks as a referentially stable snapshot.
         *
         * @param {string} key - The unprefixed key.
         * @returns {string|null} The raw string, or `null` if not set.
         */
        raw: key => backend.getItem(full(key)),

        decode,

        /**
         * Reads a value from storage.
         *
         * @template T
         * @param {string} key - The unprefixed key.
         * @param {T} [fallback=null] - Returned when the key is missing,
         * malformed, or expired.
         * @returns {*|T} The stored value or `fallback`.
         *
         * @example
         * const filters = session.get('catalog.filters', {});
         */
        get(key, fallback = null) {
            return decode(backend.getItem(full(key)), fallback);
        },

        /**
         * Writes a value to storage as JSON and notifies subscribers.
         *
         * Never throws. Returns `false` if the write fails, for example
         * when the storage quota is exceeded.
         *
         * @param {string} key - The unprefixed key.
         * @param {*} value - Any JSON-serializable value.
         * @param {SetOptions} [options] - Write options.
         * @returns {boolean} `true` if the value was saved, otherwise `false`.
         *
         * @example
         * local.set('recent.games', ids, { ttl: 60 * 60 * 24 * 7 });
         */
        set(key, value, { ttl } = {}) {
            const envelope = ttl ? { value, exp: Date.now() + ttl * 1000 } : { value };
            try {
                backend.setItem(full(key), JSON.stringify(envelope));
                emit(key);
                return true;
            } catch (e) {
                console.warn(`[storage] could not write "${key}"`, e);
                return false;
            }
        },

        /**
         * Deletes a value from storage and notifies subscribers.
         *
         * @param {string} key - The unprefixed key.
         * @returns {void}
         */
        remove(key) {
            backend.removeItem(full(key));
            emit(key);
        },

        /**
         * Lists every key written by this module in this storage area.
         * Keys belonging to other apps or libraries are ignored.
         *
         * @returns {string[]} Unprefixed keys.
         */
        keys() {
            const out = [];
            for (let i = 0; i < backend.length; i++) {
                const k = backend.key(i);
                if (k?.startsWith(PREFIX)) out.push(k.slice(PREFIX.length));
            }
            return out;
        },

        /**
         * Deletes every key written by this module in this storage area
         * and notifies subscribers. Keys from other apps or libraries on
         * the same origin are left untouched.
         *
         * @returns {void}
         */
        clear() {
            this.keys().forEach(k => backend.removeItem(full(k)));
            emit(null);
        },
    };
}

/**
 * Namespaced wrapper around `window.localStorage`. Data persists across
 * tabs and browser restarts.
 *
 * @type {Store}
 */
export const local = createStore('local');

/**
 * Namespaced wrapper around `window.sessionStorage`. Data is scoped to the
 * current tab and is removed when the tab closes.
 *
 * @type {Store}
 */
export const session = createStore('session');

/**
 * Default export containing both stores.
 *
 * @type {{ local: Store, session: Store }}
 */
export default { local, session };

/**
 * Tuple returned by the storage hooks, shaped like `useState`.
 *
 * @template T
 * @typedef {[T, (next: (T | ((prev: T) => T))) => void, () => void]} StorageHookResult
 * The current value, a setter that accepts a value or updater function,
 * and a function that removes the key.
 */

/**
 * Binds a React component to a key in web storage.
 *
 * The component re-renders when the value changes in this tab or, for
 * `local`, in another tab. Built on `useSyncExternalStore`; the raw string
 * is used as the snapshot so that parsing does not cause render loops.
 *
 * `initialValue` is read once on first render, so passing a new object
 * literal on every render is safe.
 *
 * @template T
 * @param {StorageType} type - Which storage area to use.
 * @param {string} key - The unprefixed key.
 * @param {T} initialValue - Value used when the key is missing or expired.
 * @param {SetOptions} [options] - Options applied to every write.
 * @returns {StorageHookResult<T>} `[value, setValue, remove]`.
 *
 * @example
 * const [draft, setDraft, clearDraft] = useStorage('session', 'product.draft', '');
 */
export function useStorage(type, key, initialValue, { ttl } = {}) {
    const store = type === 'session' ? session : local;
    const initial = useRef(initialValue).current;

    const subscribe = useCallback(cb => {
        const onNative = e => {
            if (e.key === null || e.key === store.fullKey(key)) cb();
        };
        const onLocal = e => {
            if (e.detail.type === type && (e.detail.key === key || e.detail.key === null)) cb();
        };
        window.addEventListener('storage', onNative);   // other tabs (local only)
        window.addEventListener(CHANGE_EVENT, onLocal); // this tab
        return () => {
            window.removeEventListener('storage', onNative);
            window.removeEventListener(CHANGE_EVENT, onLocal);
        };
    }, [store, type, key]);

    const raw = useSyncExternalStore(subscribe, () => store.raw(key), () => null);
    const value = useMemo(() => store.decode(raw, initial), [raw, initial, store]);

    const setValue = useCallback(next => {
        const resolved = typeof next === 'function' ? next(store.get(key, initial)) : next;
        store.set(key, resolved, { ttl });
    }, [store, key, initial, ttl]);

    const remove = useCallback(() => store.remove(key), [store, key]);

    return [value, setValue, remove];
}

/**
 * Binds a React component to a key in `localStorage`. Value persists
 * across reloads and stays in sync across tabs.
 *
 * @template T
 * @param {string} key - The unprefixed key.
 * @param {T} initial - Value used when the key is missing or expired.
 * @param {SetOptions} [opts] - Options applied to every write.
 * @returns {StorageHookResult<T>} `[value, setValue, remove]`.
 *
 * @example
 * const [units, setUnits] = useLocalStorage('weather.units', 'imperial');
 */
export const useLocalStorage = (key, initial, opts) => useStorage('local', key, initial, opts);

/**
 * Binds a React component to a key in `sessionStorage`. Value persists
 * across reloads within the current tab only.
 *
 * @template T
 * @param {string} key - The unprefixed key.
 * @param {T} initial - Value used when the key is missing or expired.
 * @param {SetOptions} [opts] - Options applied to every write.
 * @returns {StorageHookResult<T>} `[value, setValue, remove]`.
 *
 * @example
 * const [filters, setFilters] = useSessionStorage('catalog.filters', { genre: 'all' });
 */
export const useSessionStorage = (key, initial, opts) => useStorage('session', key, initial, opts);