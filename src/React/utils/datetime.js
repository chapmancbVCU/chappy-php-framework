/**
 * Date and time utilities that mirror Core\Lib\Utilities\DateTime.
 *
 * The same PHP format strings and FORMAT_* constants work on both sides,
 * so a view renders the same text whether PHP or React formats it.
 * Built on the browser's Intl APIs, so there are no dependencies.
 *
 * Wire contract with the PHP side (see Core\Traits\HasCasts):
 * - "2026-03-15T14:30:00Z" (ISO 8601 with Z or an offset): an exact moment.
 * - "2026-03-15 14:30:00" (no zone, legacy DB format): read as UTC, like PHP.
 *   Note that new Date() would read this as the browser's local time.
 * - "2026-03-15" (date only): a calendar date, never shifted between zones.
 *   Note that new Date() would read this as UTC midnight, which displays as
 *   the previous day anywhere west of UTC.
 * - Numbers are Unix timestamps in seconds, like PHP.
 *
 * Defaults come from VITE_TIME_ZONE and VITE_LOCALE, falling back to the
 * browser's timezone and 'en'.  Every function also takes an options object:
 * { timeZone, locale, fromTimeZone }.
 *
 * Testing: vi.useFakeTimers() and vi.setSystemTime() freeze every function
 * here, the same way InteractsWithTime does for PHP.
 */

/** 12 hour format. */
export const FORMAT_12_HOUR = 'Y-m-d h:i:s A';
/** 24 hour format. */
export const FORMAT_24_HOUR = 'Y-m-d H:i:s';
/** Human readable format. */
export const FORMAT_HUMAN_READABLE = 'l, F j, Y g:i A';
/** Date only. */
export const FORMAT_DATE_ONLY = 'Y-m-d';
/** Time only in 12 hour format. */
export const FORMAT_TIME_ONLY_12H = 'h:i A';
/** Time only in 24 hour format. */
export const FORMAT_TIME_ONLY_24H = 'H:i';
/** Friendly date format. */
export const FORMAT_FRIENDLY_DATE = 'F j, Y';
/** Day date format. */
export const FORMAT_DAY_DATE = 'l, M j';
/** ISO 8601 format. */
export const FORMAT_ISO_8601 = 'c';
/** RFC 2822 format. */
export const FORMAT_RFC_2822 = 'r';
/** SQL DateTime format. */
export const FORMAT_SQL_DATETIME = 'Y-m-d H:i:s';

// ---- Defaults ---------------------------------------------------------------

const viteEnv = import.meta.env ?? {};

const defaults = {
    timeZone: viteEnv.VITE_TIME_ZONE || Intl.DateTimeFormat().resolvedOptions().timeZone,
    locale: viteEnv.VITE_LOCALE || 'en',
};

/**
 * Overrides the default timezone and/or locale.  Useful in tests, or to
 * show times in the viewer's zone instead of the app's.
 *
 * @param {{timeZone?: string, locale?: string}} options
 */
export function setDateDefaults({ timeZone, locale } = {}) {
    if (timeZone) {
        new Intl.DateTimeFormat('en-US', { timeZone }); // throws RangeError if invalid
        defaults.timeZone = timeZone;
    }
    if (locale) defaults.locale = locale;
}

/**
 * @returns {{timeZone: string, locale: string}} The current defaults.
 */
export function getDateDefaults() {
    return { ...defaults };
}

function resolve(options = {}) {
    return {
        timeZone: options.timeZone ?? defaults.timeZone,
        locale: options.locale ?? defaults.locale,
        fromTimeZone: options.fromTimeZone ?? 'UTC',
    };
}

// ---- Timezone math ------------------------------------------------------------

const formatterCache = new Map();

function cachedFormatter(locale, options) {
    const key = locale + JSON.stringify(options);
    if (!formatterCache.has(key)) {
        formatterCache.set(key, new Intl.DateTimeFormat(locale, options));
    }
    return formatterCache.get(key);
}

/**
 * Wall-clock components of a moment in a timezone.
 */
function zonedParts(date, timeZone) {
    const parts = cachedFormatter('en-US', {
        timeZone,
        hourCycle: 'h23',
        year: 'numeric', month: 'numeric', day: 'numeric',
        hour: 'numeric', minute: 'numeric', second: 'numeric',
    }).formatToParts(date);

    const get = (type) => Number(parts.find((p) => p.type === type).value);
    return {
        year: get('year'), month: get('month'), day: get('day'),
        hour: get('hour') % 24, minute: get('minute'), second: get('second'),
    };
}

/** Offset of a timezone from UTC at a given moment, in minutes. */
function offsetMinutes(date, timeZone) {
    const p = zonedParts(date, timeZone);
    const asUtc = Date.UTC(p.year, p.month - 1, p.day, p.hour, p.minute, p.second);
    const whole = Math.floor(date.getTime() / 1000) * 1000;
    return Math.round((asUtc - whole) / 60000);
}

/** The moment a wall-clock time occurs in a timezone. */
function fromZonedTime(year, month, day, hour, minute, second, ms, timeZone) {
    const guess = Date.UTC(year, month - 1, day, hour, minute, second, ms);
    const first = offsetMinutes(new Date(guess), timeZone);
    let time = guess - first * 60000;
    const second_ = offsetMinutes(new Date(time), timeZone);
    if (second_ !== first) time = guess - second_ * 60000;
    return new Date(time);
}

// ---- Parsing ------------------------------------------------------------------

const EMPTY_DATES = new Set(['', '0000-00-00', '0000-00-00 00:00:00']);
const DATE_ONLY = /^(\d{4})-(\d{2})-(\d{2})$/;
const ZONELESS = /^(\d{4})-(\d{2})-(\d{2})[ T](\d{2}):(\d{2})(?::(\d{2})(?:\.(\d{1,6}))?)?$/;
const HAS_ZONE = /^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}(?::\d{2}(?:\.\d+)?)?(?:Z|[+-]\d{2}:?\d{2})$/i;

/**
 * Converts any supported value into a Date.  See the wire contract at the
 * top of this file.
 *
 * @param {Date|string|number|null|undefined} value
 * @param {{timeZone?: string, fromTimeZone?: string}} options
 *   timeZone anchors date-only strings; fromTimeZone is the zone of
 *   zoneless datetime strings (default 'UTC').
 * @returns {Date|null} Null for null, undefined, and empty dates.
 * @throws {RangeError} For values that cannot be parsed.
 */
export function parseDate(value, options = {}) {
    if (value === null || value === undefined) return null;

    const { timeZone, fromTimeZone } = resolve(options);
    let date;

    if (value instanceof Date) {
        date = new Date(value.getTime());
    } else if (typeof value === 'number') {
        date = new Date(value * 1000);
    } else if (typeof value === 'string') {
        const text = value.trim();
        if (EMPTY_DATES.has(text)) return null;

        let m;
        if ((m = DATE_ONLY.exec(text))) {
            date = fromZonedTime(+m[1], +m[2], +m[3], 0, 0, 0, 0, timeZone);
        } else if ((m = ZONELESS.exec(text))) {
            const ms = m[7] ? Number(m[7].padEnd(3, '0').slice(0, 3)) : 0;
            date = fromZonedTime(+m[1], +m[2], +m[3], +m[4], +m[5], +(m[6] ?? 0), ms, fromTimeZone);
        } else if (HAS_ZONE.test(text)) {
            date = new Date(text);
        }
    }

    if (!(date instanceof Date) || Number.isNaN(date.getTime())) {
        throw new RangeError(`Unrecognized date value: ${String(value)}`);
    }
    return date;
}

// ---- PHP format strings -------------------------------------------------------

/**
 * Intl inserts non-breaking spaces in some locales ("10\u00a0s").  PHP
 * output uses plain spaces, so normalize to keep both sides identical.
 */
const plain = (text) => text.replace(/[\u00a0\u202f]/g, ' ');

const pad = (n, width = 2) => String(Math.abs(n)).padStart(width, '0');

function englishOrdinal(day) {
    if (day % 100 >= 11 && day % 100 <= 13) return 'th';
    return { 1: 'st', 2: 'nd', 3: 'rd' }[day % 10] ?? 'th';
}

function formatOffset(minutes, colon) {
    const sign = minutes < 0 ? '-' : '+';
    const abs = Math.abs(minutes);
    return sign + pad(Math.floor(abs / 60)) + (colon ? ':' : '') + pad(abs % 60);
}

/**
 * Formats a Date using PHP date() format characters, in a timezone and
 * locale.  Month, weekday, and AM/PM names are translated like Carbon's
 * translatedFormat().  Escape literal characters with a backslash.
 *
 * Supported: d D j l N S w z F M m n t L Y y a A g G h H i s u v e T P p O c r U
 *
 * @param {Date} date
 * @param {string} format
 * @param {{timeZone?: string, locale?: string}} options
 * @returns {string}
 */
export function formatPhp(date, format, options = {}) {
    const { timeZone, locale } = resolve(options);
    const p = zonedParts(date, timeZone);
    const offset = offsetMinutes(date, timeZone);
    const weekday = new Date(Date.UTC(p.year, p.month - 1, p.day)).getUTCDay();
    const hour12 = p.hour % 12 || 12;
    const name = (opts) => cachedFormatter(locale, { timeZone, ...opts }).format(date);
    const dayPeriod = () => cachedFormatter(locale, { timeZone, hour: 'numeric', hour12: true })
        .formatToParts(date).find((x) => x.type === 'dayPeriod')?.value ?? (p.hour < 12 ? 'AM' : 'PM');
    const isLeap = (p.year % 4 === 0 && p.year % 100 !== 0) || p.year % 400 === 0;

    const tokens = {
        d: () => pad(p.day),
        D: () => name({ weekday: 'short' }),
        j: () => String(p.day),
        l: () => name({ weekday: 'long' }),
        N: () => String(weekday || 7),
        S: () => englishOrdinal(p.day),
        w: () => String(weekday),
        z: () => String((Date.UTC(p.year, p.month - 1, p.day) - Date.UTC(p.year, 0, 1)) / 86400000),
        F: () => name({ month: 'long' }),
        M: () => name({ month: 'short' }),
        m: () => pad(p.month),
        n: () => String(p.month),
        t: () => String(new Date(Date.UTC(p.year, p.month, 0)).getUTCDate()),
        L: () => (isLeap ? '1' : '0'),
        Y: () => String(p.year),
        y: () => pad(p.year % 100),
        a: () => dayPeriod().toLowerCase(),
        A: () => dayPeriod(),
        g: () => String(hour12),
        G: () => String(p.hour),
        h: () => pad(hour12),
        H: () => pad(p.hour),
        i: () => pad(p.minute),
        s: () => pad(p.second),
        u: () => pad(date.getUTCMilliseconds(), 3) + '000',
        v: () => pad(date.getUTCMilliseconds(), 3),
        e: () => timeZone,
        T: () => cachedFormatter('en-US', { timeZone, timeZoneName: 'short' })
            .formatToParts(date).find((x) => x.type === 'timeZoneName').value,
        P: () => formatOffset(offset, true),
        p: () => (offset === 0 ? 'Z' : formatOffset(offset, true)),
        O: () => formatOffset(offset, false),
        c: () => formatPhp(date, 'Y-m-d\\TH:i:sP', { timeZone, locale }),
        r: () => formatPhp(date, 'D, d M Y H:i:s O', { timeZone, locale: 'en' }),
        U: () => String(Math.floor(date.getTime() / 1000)),
    };

    let out = '';
    for (let i = 0; i < format.length; i++) {
        const ch = format[i];
        if (ch === '\\' && i + 1 < format.length) {
            out += format[++i];
        } else {
            out += tokens[ch] ? tokens[ch]() : ch;
        }
    }
    return plain(out);
}

// ---- Relative time ----------------------------------------------------------

/**
 * Largest whole unit between two moments, matching Carbon's
 * diffForHumans(): calendar months and years, truncated, not rounded.
 */
function largestUnit(from, to) {
    const [early, late] = from <= to ? [from, to] : [to, from];
    const seconds = Math.floor((late - early) / 1000);

    const stamp = (d) => [d.getUTCDate(), d.getUTCHours(), d.getUTCMinutes(), d.getUTCSeconds(), d.getUTCMilliseconds()];
    let months = (late.getUTCFullYear() - early.getUTCFullYear()) * 12 + (late.getUTCMonth() - early.getUTCMonth());
    const [a, b] = [stamp(late), stamp(early)];
    const earlierInMonth = a.findIndex((v, i) => v !== b[i]);
    if (earlierInMonth !== -1 && a[earlierInMonth] < b[earlierInMonth]) months--;

    if (months >= 12) return { value: Math.floor(months / 12), unit: 'year' };
    if (months >= 1) return { value: months, unit: 'month' };
    if (seconds >= 604800) return { value: Math.floor(seconds / 604800), unit: 'week' };
    if (seconds >= 86400) return { value: Math.floor(seconds / 86400), unit: 'day' };
    if (seconds >= 3600) return { value: Math.floor(seconds / 3600), unit: 'hour' };
    if (seconds >= 60) return { value: Math.floor(seconds / 60), unit: 'minute' };
    return { value: seconds, unit: 'second' };
}

/**
 * Describes how long ago (or from now) a time is, e.g. "2 hours ago".
 * Mirrors DateTime::timeAgo().  Future times use Intl's wording ("in 2 hours").
 *
 * @param {Date|string|number} value
 * @param {{locale?: string, short?: boolean, fromTimeZone?: string}} options
 *   short gives the compact form, e.g. "2h ago".
 * @returns {string}
 */
export function timeAgo(value, options = {}) {
    const { locale } = resolve(options);
    const date = parseDate(value, options);
    const now = new Date();
    const { value: amount, unit } = largestUnit(date, now);
    const past = date <= now;
    const english = /^en\b/i.test(locale);

    // Carbon's short English form says "1yr"/"2yrs"; CLDR says "1y".
    if (options.short && english && unit === 'year') {
        const label = `${amount}yr${amount === 1 ? '' : 's'}`;
        return past ? `${label} ago` : `in ${label}`;
    }

    // English 'narrow' matches Carbon ("2h ago").  Other locales use 'short',
    // because CLDR's narrow past forms can be bare ("-10 s" in French).
    return plain(new Intl.RelativeTimeFormat(locale, {
        numeric: 'always',
        style: options.short ? (english ? 'narrow' : 'short') : 'long',
    }).format(past ? -amount : amount, unit));
}

/**
 * Describes the difference between two times, e.g. "7 hours before".
 * Mirrors DateTime::timeDifference().  The amount is translated; the
 * "before"/"after" suffix is English, as Intl has no equivalent.
 *
 * @param {Date|string|number} start
 * @param {Date|string|number} end
 * @param {{locale?: string, fromTimeZone?: string}} options
 * @returns {string}
 */
export function timeDifference(start, end, options = {}) {
    const { locale } = resolve(options);
    const a = parseDate(start, options);
    const b = parseDate(end, options);
    const { value, unit } = largestUnit(a, b);

    const amount = new Intl.NumberFormat(locale, { style: 'unit', unit, unitDisplay: 'long' }).format(value);
    return plain(`${amount} ${a <= b ? 'before' : 'after'}`);
}

// ---- Formatting -----------------------------------------------------------------

/**
 * Formats a stored time for display.  Mirrors DateTime::formatTime().
 *
 * @param {Date|string|number} value
 * @param {string} format PHP format string; defaults to FORMAT_12_HOUR.
 * @param {{timeZone?: string, locale?: string, fromTimeZone?: string}} options
 * @returns {string} Empty string for null or empty dates.
 */
export function formatTime(value, format = FORMAT_12_HOUR, options = {}) {
    const date = parseDate(value, options);
    return date === null ? '' : formatPhp(date, format, options);
}

/**
 * The current time, formatted.  Mirrors DateTime::now().
 *
 * @param {string} format Defaults to FORMAT_12_HOUR.
 * @param {{timeZone?: string, locale?: string}} options
 * @returns {string}
 */
export function now(format = FORMAT_12_HOUR, options = {}) {
    return formatPhp(new Date(), format, options);
}

/**
 * Midnight of the current day, formatted.  Mirrors DateTime::today().
 *
 * @param {string} format Defaults to FORMAT_12_HOUR.
 * @param {{timeZone?: string, locale?: string}} options
 * @returns {string}
 */
export function today(format = FORMAT_12_HOUR, options = {}) {
    const { timeZone } = resolve(options);
    const p = zonedParts(new Date(), timeZone);
    return formatPhp(fromZonedTime(p.year, p.month, p.day, 0, 0, 0, 0, timeZone), format, options);
}

/**
 * The current UTC time in SQL format.  Mirrors DateTime::timeStamps().
 *
 * @returns {string} e.g. "2026-03-15 14:30:00"
 */
export function timeStamps() {
    return formatPhp(new Date(), FORMAT_SQL_DATETIME, { timeZone: 'UTC' });
}

/**
 * ISO 8601 with offset, in the display timezone.  Mirrors DateTime::toISO8601().
 *
 * @param {Date|string|number} value
 * @param {{timeZone?: string, fromTimeZone?: string}} options
 * @returns {string} e.g. "2026-03-15T10:30:00-04:00"
 */
export function toISO8601(value, options = {}) {
    return formatTime(value, FORMAT_ISO_8601, options);
}

/**
 * Namespace object so React code can read like PHP: DateTime.timeAgo(...).
 */
export const DateTime = Object.freeze({
    FORMAT_12_HOUR, FORMAT_24_HOUR, FORMAT_HUMAN_READABLE, FORMAT_DATE_ONLY,
    FORMAT_TIME_ONLY_12H, FORMAT_TIME_ONLY_24H, FORMAT_FRIENDLY_DATE,
    FORMAT_DAY_DATE, FORMAT_ISO_8601, FORMAT_RFC_2822, FORMAT_SQL_DATETIME,
    formatPhp, formatTime, now, parseDate, timeAgo, timeDifference,
    timeStamps, today, toISO8601,
});