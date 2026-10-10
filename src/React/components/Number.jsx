import { useState, useMemo } from 'react';
import { formatId, normalizeAttrs, appendErrorClass } from '@chappy/utils';
import { FieldErrors } from '@chappy/utils';

/**
 * @property {string} label Sets the label for this input.
 * @property {string} name Sets the value for the name, for, and id attributes 
 * for this input.
 * @property {string} value The value we want to set.  We can use this to set 
 * the value of the value attribute during form validation.  Default value 
 * is the empty string.  It can be set with values during form validation 
 * and forms used for editing records.
 * @property {object} config Object of optional keys.
 * @property {object} inputAttrs The values used to set the class and other 
 * attributes of the input string.  The default value is an empty object.
 * @property {object} divAttrs The values used to set the class and other 
 * attributes of the surrounding div.  The default value is an empty object.
 * @param {Number} param0 
 * @returns 
 */
const Number = ({
    label,
    name,
    value = '',
    config = {},
    inputAttrs = {},
    divAttrs = {},
    errors = {}
}) => {
    const {
        decimals = 0,
        useGrouping = false,
        min = null,
        max = null,
        step: stepCfg,
        locale = 'en-US',
    } = config;

    const step = stepCfg ?? (decimals > 0 ? `0.${'0'.repeat(decimals - 1)}1` : '1');

    // Coherence guard — fail loud (mirror PHP throw; relies on an error boundary).
    if (isFinite(+min) && isFinite(+max) && +min > +max) {
        throw new Error(`Number "${name}": min (${min}) must not exceed max (${max})`);
    }

    const id = formatId(name);
    const divProps = normalizeAttrs(divAttrs);

    const formatter = useMemo(
        () => new Intl.NumberFormat(locale, {
            minimumFractionDigits: decimals,
            maximumFractionDigits: decimals,
            useGrouping,
        }),
        [locale, decimals, useGrouping]
    );

    const cleanNumber = (v) => String(v).replace(/[^0-9.\-]/g, '');

    // Seed state from the raw incoming prop once.
    const [raw, setRaw] = useState(() => cleanNumber(value ?? ''));
    const [focused, setFocused] = useState(false);

    const display = () => {
        if (raw === '' || raw === '-') return raw;
        const n = parseFloat(raw);
        if (globalThis.Number.isNaN(n)) return '';
        // Focused: show editable raw digits. Blurred: show grouped/precise.
        return focused ? raw : formatter.format(n);
    };

    const attrs = {
        inputMode: decimals > 0 ? 'decimal' : 'numeric',
        step,
        ...(min !== null ? { min } : {}),
        ...(max !== null ? { max } : {}),
        ...inputAttrs,
    };
    const inputString = normalizeAttrs(appendErrorClass(attrs, errors, name, 'is-invalid'));

    return (
        <div {...divProps}>
            <label className="form-label" htmlFor={id}>{label}</label>
            <input
                type={useGrouping ? 'text' : 'number'}
                id={id}
                name={name}
                value={display()}
                onChange={(e) => setRaw(cleanNumber(e.target.value))}
                onFocus={() => setFocused(true)}
                onBlur={() => setFocused(false)}
                {...inputString}
            />
            <FieldErrors errors={errors} name={name} />
        </div>
    );
};
export default Number;