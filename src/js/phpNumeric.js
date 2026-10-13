export default function cleanNumber(v) { return v.replace(/[^0-9.\-]/g, ''); }

document.addEventListener('focusin', (e) => {
    const el = e.target;
    if (!el.matches?.('[data-number-group]')) return;
    el.value = cleanNumber(el.value);                 // strip for editing
});

document.addEventListener('focusout', (e) => {
    const el = e.target;
    if (!el.matches?.('[data-number-group]')) return;
    const raw = cleanNumber(el.value);
    if (!raw) return;
    const n = parseFloat(raw);
    if (globalThis.Number.isNaN(n)) return;
    el.value = new Intl.NumberFormat(el.dataset.numberLocale || 'en-US', {
        minimumFractionDigits: +el.dataset.numberDecimals || 0,
        maximumFractionDigits: +el.dataset.numberDecimals || 0,
        useGrouping: true,
    }).format(n);
});

// strip to raw on submit so the POST carries 1234.50, not 1,234.50
document.addEventListener('submit', (e) => {
    e.target.querySelectorAll('[data-number-group]').forEach((el) => {
        el.value = cleanNumber(el.value);
    });
}, true);