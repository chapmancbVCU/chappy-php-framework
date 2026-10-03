// currency.js — imported once in your app entry, NOT per field.
export default function cleanCurrency(v) {
    return v.replace(/[^0-9.]/g, '');
}

// focusin/focusout BUBBLE (focus/blur do not), so one document-level
// listener covers every current and future currency field.
document.addEventListener('focusin', (e) => {
    const el = e.target;
    if (!el.matches?.('[data-currency]')) return;
    const raw = cleanCurrency(el.value);
    el.value = raw ? parseFloat(raw) : '';        // strip for easy editing
});

document.addEventListener('focusout', (e) => {
    const el = e.target;
    if (!el.matches?.('[data-currency]')) return;
    const raw = cleanCurrency(el.value);
    if (!raw) { el.value = ''; return; }
    const n = parseFloat(raw);
    if (Number.isNaN(n)) { el.value = ''; return; }
    el.value = new Intl.NumberFormat(el.dataset.currencyLocale || 'en-US', {
        style: 'currency',
        currency: el.dataset.currency || 'USD',
    }).format(n);
});

// Normalize to raw on submit so the POST body carries 1234.50, not $1,234.50.
document.addEventListener('submit', (e) => {
    e.target.querySelectorAll('[data-currency]').forEach((el) => {
        el.value = cleanCurrency(el.value);
    });
}, true);