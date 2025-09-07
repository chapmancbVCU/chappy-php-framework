import { useState } from 'react';
import { getCsrf } from '@chappy/utils/csrf';
import { Editor } from '@tinymce/tinymce-react';
import { appendErrorClass, htmlspecialchars, formatId, normalizeAttrs } from '@chappy/utils/form';
import tinymce from '@chappy/utils/tinyMCEBootstrap'
import contentCssUrl from 'tinymce/skins/content/default/content.min.css?url'; 

/**
 * Returns Button component with text set.
 * @property {string} label The contents of the button's label.
 * @property {object} inputAttrs The values used to set the class and other 
 * attributes of the input string.  The default value is an empty object.
 * @param {*} param0 
 * @returns {HTMLButtonElement} A button element.
 */
export const Button = ({label, inputAttrs={}}) => {
    const inputString = normalizeAttrs(inputAttrs);
    return <Button type="button" {...inputString}>{label}</Button>;
}

/**
 * Supports ability to create a styled button and styled surrounding div 
 * block.  Supports ability to have functions for event handlers".
 * @property {string} label The contents of the button's label.
 * @property {object} inputAttrs The values used to set the class and other 
 * attributes of the input string.  The default value is an empty object.
 * @property {object} outputAttrs The values used to set the class and other 
 * attributes of the surrounding div.  The default value is an empty object.
 * @param {*} param0 
 * @returns 
 */
export const ButtonBlock = ({label, inputAttrs, divAttrs}) => {
    const divString = normalizeAttrs(divAttrs);
    return (
        <div {...divString}>
            <Button label={label} inputAttrs={inputAttrs} />
        </div>
    )
}

/**
 * Generates a div containing an input of type checkbox with the label to 
 * the left.
 * @property {string} label Sets the label for this input.
 * @property {string} name Sets the value for the name, for, and id attributes 
 * for this input.
 * @property {string} value The value we want to set.  We can use this to set 
 * the value of the value attribute during form validation.  Default value 
 * is the empty string.  It can be set with values during form validation 
 * and forms used for editing records.
 * @property {bool} checked The value for the checked attribute.  If true 
 * this attribute will be set as checked="checked".  The default value is 
 * false.  It can be set with values during form validation and forms 
 * used for editing records.
 * @property {object} inputAttrs The values used to set the class and other 
 * attributes of the input string.  The default value is an empty object.
 * @property {object} outputAttrs The values used to set the class and other 
 * attributes of the surrounding div.  The default value is an empty object.
 * @param {*} param0 
 * @returns 
 */
export const CheckBoxLeftLabel = ({
    label,
    name,
    value = '',
    checked = false,
    inputAttrs = {},
    divAttrs = {},
    errors = []
}) => {
    // Is it a multi-checkbox group like "roles[]"?
    const isMultiple = name.endsWith('[]');

    // Error bags usually use the base name (e.g., "roles" not "roles[]")
    const errorKey = isMultiple ? name.slice(0, -2) : name;

    // Ensure unique IDs when multiple boxes share the same name
    const safeVal = String(value).replace(/\W+/g, '_');
    const id = `${formatId(name)}_${safeVal}`;

    const divProps = normalizeAttrs(divAttrs);
    const inputProps = normalizeAttrs(
        appendErrorClass(inputAttrs, errors, errorKey, 'is-invalid')
    );

    return (
        <div {...divProps}>
        {/* For single boolean fields, ensure "false" posts when unchecked */}
        {!isMultiple && <input type="hidden" name={name} value="0" />}

        <label className="form-label" htmlFor={id}>
            {label}{' '}
            <input
            type="checkbox"
            id={id}
            name={name}           
            value={value}    
            defaultChecked={checked}
            {...inputProps}
            />
        </label>

        <FieldErrors errors={errors} name={errorKey} />
        </div>
    );
};

/**
 * Generates a div containing an input of type checkbox with the label to 
 * the right.
 * @property {string} label Sets the label for this input.
 * @property {string} name Sets the value for the name, for, and id attributes 
 * for this input.
 * @property {string} value The value we want to set.  We can use this to set 
 * the value of the value attribute during form validation.  Default value 
 * is the empty string.  It can be set with values during form validation 
 * and forms used for editing records.
 * @property {bool} checked The value for the checked attribute.  If true 
 * this attribute will be set as checked="checked".  The default value is 
 * false.  It can be set with values during form validation and forms 
 * used for editing records.
 * @property {object} inputAttrs The values used to set the class and other 
 * attributes of the input string.  The default value is an empty object.
 * @property {object} outputAttrs The values used to set the class and other 
 * attributes of the surrounding div.  The default value is an empty object.
 * @param {*} param0 
 * @returns 
 */
export const CheckBoxRightLabel = ({
    label,
    name,
    value = '',
    checked = false,
    inputAttrs = {},
    divAttrs = {},
    errors = []
}) => {
    // Is it a multi-checkbox group like "roles[]"?
    const isMultiple = name.endsWith('[]');

    // Error bags usually use the base name (e.g., "roles" not "roles[]")
    const errorKey = isMultiple ? name.slice(0, -2) : name;

    // Ensure unique IDs when multiple boxes share the same name
    const safeVal = String(value).replace(/\W+/g, '_');
    const id = `${formatId(name)}_${safeVal}`;

    const divProps = normalizeAttrs(divAttrs);
    const inputProps = normalizeAttrs(
        appendErrorClass(inputAttrs, errors, errorKey, 'is-invalid')
    );

    return (
        <div {...divProps}>
        {/* For single boolean fields, ensure "false" posts when unchecked */}
        {!isMultiple && <input type="hidden" name={name} value="0" />}

        <input
            type="checkbox"
            id={id}
            name={name}           
            value={value}         
            defaultChecked={!checked}
            {...inputProps}
        />
        <label className="form-label" htmlFor={id}>{label}{' '}</label>
        
        <FieldErrors errors={errors} name={errorKey} />
        </div>
    );
};
/**
 * Generates hidden component for csrf token.
 * @param {string} name The name for the csrf token. 
 * @returns {HTMLInputElement} Input element of type hidden containing csrf token.
 */
export const CSRF = ({ name = 'csrf_token' } = {}) => {
  return <input type="hidden" name={name} value={getCsrf()} />;
}

/**
 * Creates an error bag containing all existing errors.
 * @property {object} errors Object containing errors.
 * @param {InputProps} param0 
 * @returns {HTMLDivElement} The error bag.
 */
export const DisplayErrors = ({errors}) => {
    const hasErrors = (errors.length !== 0) ? 'has-errors' : '';
    let list = Object.values(errors).flat(Infinity);

    return (
        <div className='form-errors'>
            <ul className={`bg-light ${hasErrors}`}>
            {list.map((field, index) => (
                <ul key={index} className='text-danger'>
                    {list[index]}
                </ul>
            ))}
            </ul>

        </div>
    )
}

/**
 * Renders an HTML div element that surrounds an input of type email.
 * @property {string} label Sets the label for this input.
 * @property {string} name Sets the value for the name, for, and id attributes 
 * for this input.
 * @property {string|number} value The value we want to set.  We can use this to set 
 * the value of the value attribute during form validation.  Default value 
 * is the empty string.  It can be set with values during form validation 
 * and forms used for editing records.
 * @property {object} inputAttrs The values used to set the class and other 
 * attributes of the input string.  The default value is an empty object.
 * @property {object} outputAttrs The values used to set the class and other 
 * attributes of the surrounding div.  The default value is an empty object.
 * @property {Record<string, string[]>|string[]} [errors=[]]The errors object.  
 * Default value is an empty object.
 * @param {InputProps} param0 
 * @returns {HTMLDivElement} A surrounding div and the input element of type email.
 */
const Email = ({
    label,
    name,
    value = '',
    inputAttrs={},     
    divAttrs={},
    errors=[],
}) => {
    const divString = normalizeAttrs(divAttrs);
    inputAttrs = appendErrorClass(inputAttrs, errors, name, 'is-invalid');
    const inputString = normalizeAttrs(inputAttrs);

    return (
        <div {...divString}>
            <label className="form-label" htmlFor={name}>{label}</label>
            <input type='email' id={name} name={name} defaultValue={value} {...inputString}/>
            <FieldErrors errors={errors} name={name} />
        </div>
    )
}
/**
 * Renders an error message for a particular form field.
 * @property {object} errors The error object.
 * @property {string} name Used to search errors object for key/form field.
 * @param {InputProps} param0 
 * @returns {HTMLSpanElement} The error message for a particular field.
 */
const FieldErrors = ({ errors = {}, name }) => {
    const list = Array.isArray(errors?.[name])
        ? errors[name]
        : (errors?.[name] != null ? [errors[name]] : []);

    if (!list.length) return null;

    return (
        <span className="invalid-feedback d-block">
            {list.map((message, index) => (
                <div key={index}>{htmlspecialchars(message)}</div>
            ))}
        </span>
    );
};

/**
 * Generates a hidden input element.
 * @property {string} The value for the name and id attributes.
 * @property {string|number} The value for the value attribute.
 * @param {InputProps} param0 
 * @returns {HTMLInputElement} The html input element with type hidden
 */
const Hidden = ({
    name,
    value
}) => {
    return <input type="hidden" name={name} id={name} defaultValue={value} />
}

/**
 * Assists in the development of forms input blocks in forms.  It accepts 
 * parameters for setting attribute tags in the form section.  Not to be 
 * used for inputs of type "Submit"  For submit inputs use the submitBlock 
 * or submitTag functions.
 *
 * @typedef {Object} InputProps
 * @property {'color'|'date'|'date-local'|'email'|'file'|'month'|'number'|'password'|'range'|'search'|'tel'|'text'|'time'|'url'|'week'} [type='text']
 * The input type we want to generate.
 * @property {string} label Sets the label for this input.
 * @property {string} name Sets the value for the name, for, and id attributes 
 * for this input.
 * @property {string|number} value The value we want to set.  We can use this to set 
 * the value of the value attribute during form validation.  Default value 
 * is the empty string.  It can be set with values during form validation 
 * and forms used for editing records.
 * @property {object} inputAttrs The values used to set the class and other 
 * attributes of the input string.  The default value is an empty object.
 * @property {object} outputAttrs The values used to set the class and other 
 * attributes of the surrounding div.  The default value is an empty object.
 * @property {Record<string, string[]>|string[]} [errors=[]]The errors object.  
 * Default value is an empty object.
 * @param {InputProps} props
 * @returns {HTMLDListElement} A surrounding div and the input element.
 *
 * @example
 * <Input
 *   label="First Name"
 *   name="fname"
 *   value={user.fname}
 *   divAttrs={{ className: 'mb-3' }}
 *   inputAttrs={{ className: 'form-control', placeholder: 'Enter first name' }}
 *   errors={errors}
 * />
 */
export const Input = ({
    type='text',
    label,
    name,
    value='',
    inputAttrs={},
    divAttrs={},
    errors=[]
}) => {
    const id = formatId(name);
    const divString = normalizeAttrs(divAttrs);
    inputAttrs = appendErrorClass(inputAttrs, errors, name, 'is-invalid');
    const inputString = normalizeAttrs(inputAttrs);

    return (
        <div {...divString}>
            <label className='form-label' htmlFor={id}>{label}</label>
            <input type={type} id={id} name={name} defaultValue={value} {...inputString} />
            <FieldErrors errors={errors} name={name} />
        </div>
    )
}

/**
 * Generates an HTML output element.
 * @property {string} name Sets the value for the name attributes for this 
 * input.
 * @property {string} forAttr Sets the value for the for attribute.
 * @param {*} param0 
 * @returns {HTMLOutputElement} The HTML output element.
 */
const Output = ({
    name,
    forAttr
}) => {
    return <output name={name} htmlFor={forAttr}/>
}

/**
 * Creates an input element of type radio with an accompanying label 
 * element.  Compatible with radio button groups.
 * @property {string} label Sets the label for this input.
 * @property {string} id The id attribute for the radio input button.
 * @property {string} name Sets the value for the name attribute.
 * @property {string} value The value we want to set.  We can use this to set 
 * the value of the value attribute during form validation.  It can be 
 * set with values during form validation and forms used for editing records.
 * @property {bool} checked The value for the checked attribute.  If true 
 * this attribute will be set as checked="checked".  The default value is 
 * false.  It can be set with values during form validation and forms 
 * used for editing records.
 * @property {object} inputAttrs The values used to set the class and other 
 * attributes of the input string.  The default value is an empty object.
 * @param {InputProps} param0 
 * @returns {JSX.Element} The radio input and its label.
 */
const Radio = ({
    label,
    id,
    name,
    value,
    checked = false,
    inputAttrs={}
}) => {
    const inputString = normalizeAttrs(inputAttrs);
    return (
        <>
            <input type="radio" id={id} name={name} defaultValue={value} defaultChecked={checked} {...inputString}/>
            <label className="form-label me-3" htmlFor={id}>{label}</label>
        </>
    )
}

/**
 * Rich text editor field (TinyMCE) that posts HTML via a hidden input.
 *
 * Behavior:
 * - Decodes any HTML entities in `value` before seeding the editor (so `<p>` renders as a paragraph).
 * - Uses TinyMCE as an uncontrolled editor; current HTML is mirrored into a hidden `<input name={name}>`
 *   so your PHP controller receives `$_POST[name]` as HTML.
 * - UI skin CSS is expected to be imported elsewhere; `skin:false` prevents URL fetches.
 *
 * @typedef {Object} RichTextProps
 * @property {string} label Sets the label for this input.
 * @property {string} name Sets the value for the name, for, and id attributes 
 * for this input.
 * @property {string} value The value we want to set.  We can use this to set 
 * the value of the value attribute during form validation.  Default value 
 * is the empty string.  It can be set with values during form validation 
 * and forms used for editing records.
 * @property {object} inputAttrs The values used to set the class and other 
 * attributes of the input string.  The default value is an empty object.
 * @property {object} outputAttrs The values used to set the class and other 
 * attributes of the surrounding div.  The default value is an empty object.
 * @property {Record<string,string[]>|string[]} [errors=[]] Error bag used by `errorMsg(errors, name)`.
 *
 * @param {RichTextProps} props
 * @returns {JSX.Element}
 */
const RichText = ({
    label,
    name,
    value = '',
    inputAttrs={},     
    divAttrs={},
    errors=[],
}) => {
    const id = formatId(name);
    const divString = normalizeAttrs(divAttrs);
    const placeholder = inputAttrs.placeholder || '';

    const decodeEntities = (s='') =>
        new DOMParser().parseFromString(String(s), 'text/html').documentElement.textContent || '';

    const initial = decodeEntities(value || '');
    const [html, setHtml] = useState(initial);

    return (
        <div {...divString}>
            {label && <label className="form-label" htmlFor={id}>{label}</label>}

            <Editor
                tinymce={tinymce}
                id={id}
                initialValue={decodeEntities(value)}
                onEditorChange={(value) => setHtml(value)}
                init={{
                    height: 300,
                    menubar: false,
                    branding: false,
                    placeholder,
                    skin: false,
                    content_css: contentCssUrl,  // let the iframe load this exact URL
                    content_css_cors: true,
                    entity_encoding: 'raw',
                    plugins:
                        'advlist autolink lists link image charmap preview anchor ' +
                        'searchreplace visualblocks code fullscreen insertdatetime media ' +
                        'table wordcount',
                    toolbar:
                        'undo redo | bold italic underline strikethrough backcolor | ' +
                        'outdent indent | alignleft aligncenter alignright alignjustify | ' +
                        'removeformat | code fullscreen',
                    license_key: 'gpl',
                }}
            />
            <input type="hidden" name={name} value={html} />
            <FieldErrors errors={errors} name={name} />
        </div>
    );
};

/**
 * @property {string} label Sets the label for this input.
 * @property {string}  name Sets the value for the name, for, and id attributes 
 * for this input.
 * @property {string} value The value we want to set.  We can use this to set 
 * the value of the value attribute during form validation.  Default value 
 * is the empty string.  It can be set with values during form validation 
 * and forms used for editing records.
 * @property {string} fieldName The name of the field in the model to use.
 * @property {array} The list of options we will use to populate the 
 * select option dropdown.  The default value is an empty array.
 * @property {object} inputAttrs The values used to set the class and other 
 * attributes of the input string.  The default value is an empty object.
 * @property {object} outputAttrs The values used to set the class and other 
 * attributes of the surrounding div.  The default value is an empty object.
 * @property {Record<string,string[]>|string[]} [errors=[]] The errors object.  
 * Default value is an empty object.
 * 
 * @param {InputProps} param0 
 * @returns 
 */
export const Select = ({
    label,
    name,
    value,
    fieldName="",
    options=[],
    inputAttrs={},
    divAttrs={},
    errors=[]
}) => {
    const id = formatId(name);
    const divString = normalizeAttrs(divAttrs);
    inputAttrs = appendErrorClass(inputAttrs, errors, name, 'is-invalid');
    const inputString = normalizeAttrs(inputAttrs);

    return (
        <div {...divString}>
            <label className='form-label' htmlFor={id}>{label}</label>
            <select id={id} name={name} defaultValue={value} {...inputString}>
                {options && options.map((option, index) => (
                    <SelectOptions 
                        key={String(option[fieldName] ?? index)} 
                        option={option} 
                        fieldName={fieldName} 
                    />
                ))}
            </select>
            <FieldErrors errors={errors} name={name} />
        </div>
    );
}

/**
 * Generates options for select.
 * @property {object} option An instance of model used to populate a select 
 * option
 * @property {string} fieldName The name of the field in the model to use.
 * @param {Input} param0 
 * @returns {HTMLOptionElement} An HTML element for select with correct 
 * value displayed.
 */
const SelectOptions = ({option, fieldName}) => {
    const val = String(option[fieldName]);
    return <option value={val}>{String(option[fieldName])}</option>;
}

/**
 * @property {string} label Sets the value of the text describing the 
 * button.
 * @property {object} inputAttrs The values used to set the class and other 
 * attributes of the input string.  The default value is an empty object.
 * @property {object} outputAttrs The values used to set the class and other 
 * attributes of the surrounding div.  The default value is an empty object.
 * @param {InputProps} param0 
 * @returns A surrounding div and the input element of type submit.
 */
export const SubmitBlock = ({label, inputAttrs={}, divAttrs={}}) => {
    const divString = normalizeAttrs(divAttrs);
    const inputString = normalizeAttrs(inputAttrs);

    return (
        <div {...divString}>
            <input type="submit" value={label} {...inputString} />
        </div>
    );
}

/**
 * Create a input element of type submit.
 *
 * @typedef {Object} SubmitTagProps
 * @property {string} label Sets the value of the text describing the 
 * button.
 * @property {object} inputAttrs The values used to set the class and other 
 * attributes of the input string.  The default value is an empty object.
 * @param {SubmitTagProps} props
 * @returns {HTMLInputElement} An input element of type submit.
 */
export const SubmitTag = ({label, inputAttrs={}}) => {
    const inputString = normalizeAttrs(inputAttrs);
    return(
        <input type="submit" value={label} {...inputString}/>
    )
}

/**
 * Renders an HTML div element that surrounds an input of type tel.
 * @property {string} label Sets the label for this input.
 * @property {string} name Sets the value for the name, for, and id attributes 
 * for this input.
 * @property {string|number} value The value we want to set.  We can use this to set 
 * the value of the value attribute during form validation.  Default value 
 * is the empty string.  It can be set with values during form validation 
 * and forms used for editing records.
 * @property {object} inputAttrs The values used to set the class and other 
 * attributes of the input string.  The default value is an empty object.
 * @property {object} outputAttrs The values used to set the class and other 
 * attributes of the surrounding div.  The default value is an empty object.
 * @property {Record<string, string[]>|string[]} [errors=[]]The errors object.  
 * Default value is an empty object.
 * @param {InputProps} param0 
 * @returns {HTMLDivElement}
 */
export const Tel = ({
    label,
    name,
    value='',
    inputAttrs={},
    divAttrs={},
    errors=[]
}) => {
    const divString = normalizeAttrs(divAttrs);
    inputAttrs = appendErrorClass(inputAttrs, errors, name, 'is-invalid');
    const inputString = normalizeAttrs(inputAttrs);

    return (
        <div {...divString}>
            <label className="form-label" htmlFor={name}>{label}</label>
            <input type="tel" id={name} name={name} defaultValue={value} {...inputString} />
            <FieldErrors errors={errors} name={name} />
        </div>
    );
}

/**
 * Assists in the development of textarea in forms.  It accepts parameters 
 * for setting  attribute tags in the form section.
 *
 * @typedef {Object} TextAreaProps
 * @property {string} label Sets the label for this input.
 * @property {string}  name Sets the value for the name, for, and id attributes 
 * for this input.
 * @property {string} value The value we want to set.  We can use this to set 
 * the value of the value attribute during form validation.  Default value 
 * is the empty string.  It can be set with values during form validation 
 * and forms used for editing records.
 * @property {object} inputAttrs The values used to set the class and other 
 * attributes of the input string.  The default value is an empty object.
 * @property {object} outputAttrs The values used to set the class and other 
 * attributes of the surrounding div.  The default value is an empty object.
 * @property {Record<string,string[]>|string[]} [errors=[]] The errors object.  
 * Default value is an empty object.
 *
 * @param {TextAreaProps} props
 * @returns {HTMLTextAreaElement} A surrounding div and the textarea element.
 */
export const TextArea = ({
    label,
    name,
    value='',
    inputAttrs={},
    divAttrs={},
    errors=[]
}) => {
    const id = formatId(name);
    const divString = normalizeAttrs(divAttrs);
    inputAttrs = appendErrorClass(inputAttrs, errors, name, 'is-invalid');
    const inputString = normalizeAttrs(inputAttrs);

    return (
        <div {...divString}>
            <label className='control-label' htmlFor={id}>{label}</label>
            <textarea id={id} name={name} {...inputString} defaultValue={value}></textarea>
            <FieldErrors errors={errors} name={name} />
        </div>
    )
}

const Forms = { 
    Button,
    CheckBoxLeftLabel,
    CheckBoxRightLabel,
    CSRF, 
    DisplayErrors, 
    Email,
    Hidden,
    Input, 
    Output,
    Radio,
    RichText, 
    Select,
    SubmitBlock,
    SubmitTag, 
    Tel,
    TextArea 
};
export default Forms;