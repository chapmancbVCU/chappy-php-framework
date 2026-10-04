import { useState } from 'react';
import { Editor } from '@tinymce/tinymce-react';
import { formatId, normalizeAttrs } from '@chappy/utils/form';
import tinymce from '@chappy/utils/tinyMCEBootstrap'
import contentCssUrl from 'tinymce/skins/content/default/content.min.css?url'; 
import { FieldErrors } from '@chappy/utils';

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
 * @property {object} divAttrs The values used to set the class and other 
 * attributes of the surrounding div.  The default value is an empty object.
 * @property {Record<string,string[]>|string[]} [errors=[]] Error bag used by `errorMsg(errors, name)`.
 *
 * @param {RichTextProps} props
 * @returns {JSX.Element}
 * 
 * @example
 * 
 * <Forms.RichText
 *      label="Description"
 *      name="description"
 *      value={user.description}
 *      inputAttrs={{ placeholder: 'Describe yourself here...' }}
 *      divAttrs={{ className: 'form-group mb-3' }}
 * />
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

export default RichText;