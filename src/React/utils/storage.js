import { useCallback, useMemo, useRef, useSyncExternalStore } from 'react';

const PREFIX = (import.meta.env.VITE_STORAGE_PREFIX ?? 'chappy') + ':';
export const CHANGE_EVENT = 'chappy:storage';

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

function createStore(type) {
    const native = typeof window !== 'undefined' ? probe(type) : null;
    const backend = native ?? memoryBackend();
    const full = key => PREFIX + key;
    const emit = key =>
        window.dispatchEvent(new CustomEvent(CHANGE_EVENT, { detail: { type, key } }));

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
        raw: key => backend.getItem(full(key)),
        decode,
        get(key, fallback = null) {
            return decode(backend.getItem(full(key)), fallback);
        },
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
        remove(key) {
            backend.removeItem(full(key));
            emit(key);
        },
        keys() {
            const out = [];
            for (let i = 0; i < backend.length; i++) {
                const k = backend.key(i);
                if (k?.startsWith(PREFIX)) out.push(k.slice(PREFIX.length));
            }
            return out;
        },
        clear() {
            this.keys().forEach(k => backend.removeItem(full(k)));
            emit(null);
        },
    };
}

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

export const useLocalStorage = (key, initial, opts) => useStorage('local', key, initial, opts);
export const useSessionStorage = (key, initial, opts) => useStorage('session', key, initial, opts);
export const local = createStore('local');
export const session = createStore('session');
export default { local, session };