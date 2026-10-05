<?php
declare(strict_types=1);
namespace Core;

use Core\Session;
use Core\Lib\Utilities\Arr;
use Core\Lib\Utilities\Str;
use Core\Lib\Utilities\ArraySet;
/**
 * Contains functions for building form elements of various types, the sanitation, 
 * and other setup responsibilities.
 */
class FormHelper {
    /**
     * Adds name of error classes to div associated with a form field.
     *
     * @param array $attrs The values used to set the class and other 
     * attributes of the input string.  The default value is an empty array.
     * @param array $errors The errors array.
     * @param string $name The name of the field associated with this error.
     * @param string $class Name of the class used to identify errors for a 
     * form field.
     * @return array Div attributes with error classes added.
     */
    public static function appendErrorClass(array $attrs, array $errors, string $name, string $class): array {
        $attrsArr = new ArraySet($attrs);
        $errorsArr = new ArraySet($errors);
    
        if ($errorsArr->has($name)->result()) {
            $currentClass = $attrsArr->get('class', '')->result();
            
            // Ensure it's a string before appending
            if (!is_string($currentClass)) {
                $currentClass = '';
            }
    
            $attrsArr->set('class', trim($currentClass . " " . $class));
        }
    
        return $attrsArr->all();
    }
    
    /**
     * Supports ability to create a styled button.
     * 
     * An example function call is shown below:
     * 
     * ```php
     * FormHelper::button(
     *      "Click Me!", 
     *      ['class' => 'btn btn-large btn-primary', 'onClick' => 'alert(\'Hello World!\')']
     * );
     * ```
     * 
     * Example HTML output is shown below:
     * <button type="button"  class="btn btn-large btn-primary" onClick="alert('Hello World!')">Click Me!</button>
     * 
     * @param string $buttonText The contents of the button's label.
     * @param array $inputAttrs This parameter is used to set values for attributes 
     * such as classes for styling, front-side validation, and event handlers. 
     * Make sure when performing an event handler function call that contains strings 
     * as arguments to escape any quotes. The default value is an empty array.
     * @return string An HTML button element with its label set and any other 
     * optional attributes set.
     */
    public static function button(string $buttonText, array $inputAttrs = []): string {
        $inputString = self::stringifyAttrs($inputAttrs);
        return '<button type="button" '.$inputString.'>'.$buttonText.'</button>';
    }

    /**
     * Supports ability to create a styled button and styled surrounding div 
     * block.
     * 
     * An example function call is shown below:
     * FormHelper::buttonBlock(
     *      "Click Me!", 
     *      ['class' => 'btn btn-large btn-primary', 'onClick' => 'alert(\'Hello World!\')'], 
     *      ['class' => 'form-group']
     * );
     * 
     * Example HTML output is shown below:
     * <div class="form-group"><button type="button"  class="btn btn-large btn-primary" onClick="alert('Hello World!')">Click Me!</button></div> 
     * 
     * @param string $buttonText The contents of the button's label.
     * @param array $inputAttrs This parameter is used to set values for attributes 
     * such as classes for styling, front-side validation, and event handlers. 
     * Make sure when performing an event handler function call that contains strings 
     * as arguments to escape any quotes. The default value is an empty array.
     * @param array $divAttrs The values used to set the class and other 
     * attributes of the surrounding div.  The default value is an empty array.
     * @return string An HTML div surrounding a button element with its label 
     * set and any other optional attributes set.
     */
    public static function buttonBlock(string $buttonText, array $inputAttrs = [], array $divAttrs = []): string {
        $divString = self::stringifyAttrs($divAttrs);
        $html = '<div'.$divString.'>';
        $html .= self::button($buttonText, $inputAttrs); 
        $html .= '</div>';
        return $html;
    }

    /**
     * Generates a div containing an input of type checkbox with the label to 
     * the left.
     *
     * An example function call is shown below:
     * FormHelper::checkboxBlockLabelLeft(
     *      'Remember Me', 
     *      'remember_me', 
     *      'on', 
     *      $this->login->getRememberMeChecked(), 
     *      [], 
     *      ['class' => 'form-group'], $this->displayErrors
     * );
     * 
     * Example HTML output is shown below:
     * <div class="form-group">
     *     <label for="remember_me">Remember Me</label> 
     *     <input type="checkbox" id="remember_me" name="remember_me" value="on" />
     * </div>
     * 
     * @param string $label Sets the label for this input.
     * @param string $name Sets the value for the name, for, and id attributes 
     * for this input.
     * @param string $value The value we want to set.  We can use this to set 
     * the value of the value attribute during form validation.  Default value 
     * is the empty string.  It can be set with values during form validation 
     * and forms used for editing records.
     * @param bool $checked The value for the checked attribute.  If true 
     * this attribute will be set as checked="checked".  The default value is 
     * false.  It can be set with values during form validation and forms 
     * used for editing records.
     * @param array $inputAttrs The values used to set the class and other 
     * attributes of the input string.  The default value is an empty array.
     * @param array $divAttrs The values used to set the class and other 
     * attributes of the surrounding div.  The default value is an empty array.
     * @param array $errors The errors array.  Default value is an empty array.
     * @return string A surrounding div and the input element of type checkbox.
     */
    public static function checkboxBlockLabelLeft(string $label, 
        string $name, 
        string $value = "",
        bool $checked = false, 
        array $inputAttrs = [], 
        array $divAttrs = [],
        array $errors = [],
    ): string {

        $inputAttrs = self::appendErrorClass($inputAttrs, $errors, $name, 'is-invalid');
        $divString = self::stringifyAttrs($divAttrs);
        $inputString = self::stringifyAttrs($inputAttrs);
        $checkString = ($checked) ? ' checked="checked"' : '';
    
        // Determine if it's a multiple checkbox group
        $isMultiple = Str::endsWith($name, '[]');
        $nameWithBrackets = $isMultiple ? $name : htmlspecialchars($name); 
        $id = Str::replace('[]', '', $name) . '_' . $value; // Ensure unique ID
    
        $html = '<div' . $divString . '>';
        $html .= '<label class="form-label" for="' . htmlspecialchars($id) . '">';
        $html .= htmlspecialchars($label) . ' ';
        $html .= '<input type="checkbox" id="' . htmlspecialchars($id) . '" name="' . $nameWithBrackets . '" value="' . htmlspecialchars($value) . '"' . $checkString . $inputString . ' />';
        $html .= '</label>';
        $html .= '<span class="invalid-feedback">' . self::errorMsg($errors, $name) . '</span>';
        $html .= '</div>';
        
        return $html;
    }

    /**
     * Generates a div containing an input of type checkbox with the label to 
     * the right.
     *
     * An example function call is shown below:
     * FormHelper::checkboxBlockLabelRight(
     *      'Remember Me', 
     *      'remember_me', 
     *      'on', 
     *      $this->login->getRememberMeChecked(), 
     *      [],
     *      ['class' => 'form-group mr-1'], $this->displayErrors
     * );
     * 
     * Example HTML output is shown below:
     * <div>
     *     <input type="checkbox" id="remember_me" name="remember_me" value="on" class="form-group mr-1">
     *     <label for="remember_me">Remember Me</label>
     * </div> 
     * 
     * @param string $label Sets the label for this input.
     * @param string $name Sets the value for the name, for, and id attributes 
     * for this input.
     * @param string $value The value we want to set.  We can use this to set 
     * the value of the value attribute during form validation.  Default value 
     * is the empty string.  It can be set with values during form validation 
     * and forms used for editing records.
     * @param boolean $checked The value for the checked attribute.  If true 
     * this attribute will be set as checked="checked".  The default value is 
     * false.  It can be set with values during form validation and forms 
     * used for editing records.
     * @param array $inputAttrs The values used to set the class and other 
     * attributes of the input string.  The default value is an empty array.
     * @param array $divAttrs The values used to set the class and other 
     * attributes of the surrounding div.  The default value is an empty array.
     * @param array $errors The errors array.  Default value is an empty array.
     * @return string A surrounding div and the input element.
     */
    public static function checkboxBlockLabelRight(string $label, 
        string $name, 
        string $value = "",
        bool $checked = false, 
        array $inputAttrs = [], 
        array $divAttrs = [],
        array $errors = [],
    ): string {

        $inputAttrs = self::appendErrorClass($inputAttrs, $errors, $name, 'is-invalid');
        $divString = self::stringifyAttrs($divAttrs);
        $inputString = self::stringifyAttrs($inputAttrs);
        $checkString = ($checked) ? ' checked="checked"' : '';
    
        // Determine if it's a multiple checkbox group
        $isMultiple = Str::endsWith($name, '[]');
        $nameWithBrackets = $isMultiple ? $name : htmlspecialchars($name); 
        $id = Str::replace('[]', '', $name) . '_' . $value; // Ensure unique ID
    
        $html = '<div' . $divString . '>';
        $html .= '<input class="form-label" type="checkbox" id="' . htmlspecialchars($id) . '" name="' . $nameWithBrackets . '" value="' . htmlspecialchars($value) . '"' . $checkString . $inputString . '> ';
        $html .= '<label for="' . htmlspecialchars($id) . '">' . htmlspecialchars($label) . '</label>';
        $html .= '<span class="invalid-feedback">' . self::errorMsg($errors, $name) . '</span>';
        $html .= '</div>';
        
        return $html;
    }

    /**
     * Renders a group of checkboxes sharing one name (submitted as name[]),
     * one wrapping div, and ONE error span. Label-right per box.
     *
     * Example:
     * <?= FormHelper::checkboxGroup(
     *     'acls',
     *     $this->acls,              // [value => label] map of all ACLs
     *     $this->user->getAcls(),   // the set of ACLs this user currently has
     *     [],
     *     ['class' => 'form-check'],
     *     $this->displayErrors
     * ); ?>
     * 
     * @param string $name           Group name WITHOUT '[]' (added internally), e.g. 'acls'.
     * @param array  $options        [value => label] map of choices.
     * @param array  $selectedValues Values that should render checked (the current set).
     * @param array  $inputAttrs     Passthrough attrs applied to every box (error-classed once here).
     * @param array  $divAttrs       Attrs for the group's wrapping div.
     * @param array  $errors         Errors array; one invalid-feedback span for the whole group.
     * @return string The checkbox group.
     */
    public static function checkboxGroup(
        string $name,
        array $options = [],
        array $selectedValues = [],
        array $inputAttrs = [],
        array $divAttrs = [],
        array $errors = []
    ): string {
        // Error class applied ONCE for the whole group, not per box.
        $inputAttrs = self::appendErrorClass($inputAttrs, $errors, $name, 'is-invalid');
        $divString  = self::stringifyAttrs($divAttrs);

        // Submit as an array; strip any stray '[]' the caller passed, then add one.
        $groupName = Str::replace('[]', '', $name) . '[]';

        $html = '<div' . $divString . '>';
        foreach ($options as $value => $label) {
            // Loose comparison bridges int/string, matching radio's ==.
            $checked = in_array($value, $selectedValues);
            $html .= self::checkboxInput($label, $groupName, (string)$value, $checked, $inputAttrs, true);
        }
        // ONE error span for the whole group.
        $html .= '<span class="invalid-feedback">' . self::errorMsg($errors, $name) . '</span>';
        $html .= '</div>';

        return $html;
    }

    /**
     * Renders a single checkbox input with its label — the bare item only,
     * no wrapping div and no error span (the caller owns the envelope).
     * Label sits to the RIGHT of the box by default; pass $labelRight = false
     * to place it on the left.
     *
     * $inputAttrs is expected already error-classed by the caller (mirrors radioInput).
     *
     * @param string $label      Visible label text.
     * @param string $name       Field name. Keep the '[]' suffix for group members
     *                           (e.g. 'genres[]') so they submit as an array.
     * @param string $value      Submitted value; also used to build a unique id.
     * @param bool   $checked    Whether this box renders checked.
     * @param array  $inputAttrs Passthrough HTML attributes (already error-classed).
     * @param bool   $labelRight Label on the right of the box (default true).
     */
    public static function checkboxInput(
        string $label,
        string $name,
        string $value = "",
        bool $checked = false,
        array $inputAttrs = [],
        bool $labelRight = true
    ): string {
        $inputString = self::stringifyAttrs($inputAttrs);
        $checkString = $checked ? ' checked="checked"' : '';
        $id = Str::replace('[]', '', $name) . '_' . $value;

        $input = '<input type="checkbox" id="' . htmlspecialchars($id) . '"'
            . ' name="' . htmlspecialchars($name) . '"'
            . ' value="' . htmlspecialchars($value) . '"'
            . $checkString . $inputString . ' />';

        $text = htmlspecialchars($label);
        $labelOpen = '<label class="form-check-label me-3" for="' . htmlspecialchars($id) . '">';

        // Default: box first, label text to its right.
        $inner = $labelRight
            ? $input . ' ' . $text
            : $text . ' ' . $input;

        return $labelOpen . $inner . '</label>';
    }

    /**
     * Checks if the csrf token exists.  This is used to verify that there has 
     * been no tampering of a form's csrf token.
     *
     * @param string $token The token string we will test whether or not it 
     * exists.
     * @return bool The result of the AND operation on whether or not a token 
     * exists with a session and if the session's token is equal to the value 
     * of the $token parameter.
     */
    public static function checkToken(string $token): bool {
        return (Session::exists('csrf_token') && Session::get('csrf_token') == $token);
    }

    /**
     * A hidden input to represent the csrf token in a web form.
     * 
     * @return string The hidden input of type hidden with the generated token 
     * set as the value.
     */
    public static function csrfInput(): string {
        return '<input type="hidden" name="csrf_token" id="csrf_token" value="' . self::generateToken() . '" />';
    }

    /**
     * Renders an HTML div element that surrounds an input of type currency.
     *
     * Example:
     *  <?= FormHelper::currencyBlock(
     *     label: 'Amount',
     *     name: "amount",
     *     inputAttrs: ['class' => 'form-control input-sm'],
     *     divAttrs: ['class' => 'form-group mb-3']
     *  ) ?>
     * 
     * @param string $label Sets the label for this input.
     * @param string $name Sets the value for the name, for, and id attributes 
     * for this input.
     * @param string $currency The 3 digit currency name.
     * @param string $intlNumberFormat The international number format.
     * @param mixed $value The value we want to set.  We can use this to set 
     * the value of the value attribute during form validation.  Default value 
     * is the empty string.  It can be set with values during form validation 
     * and forms used for editing records.
     * @param array $inputAttrs The values used to set the class and other 
     * attributes of the input string.  The default value is an empty array.
     * @param array $divAttrs The values used to set the class and other 
     * attributes of the surrounding div.  The default value is an empty array.
     * @param array $errors The errors array.  Default value is an empty array.
     * @return string A surrounding div and the input element of type currency.
     */
    public static function currencyBlock(
        string $label, 
        string $name, 
        string $intlNumberFormat = 'en-US',
        string $currency = "USD",
        mixed $value = '', 
        array $inputAttrs = [], 
        array $divAttrs = [],
        array $errors=[]
    ): string {
        // Incoming $value is the RAW stored decimal (e.g. 1234.5).
        // Format it for first paint so the field shows "$1,234.50" on load.
        $display = ($value === '' || $value === null)
            ? ''
            : self::formatCurrency($value, $currency, $intlNumberFormat);

        // Hints — only if caller didn't override.
        $inputAttrs += [
            'inputmode'   => 'decimal',
            'placeholder' => '$0.00',
            'pattern' => '[0-9]*([\.,][0-9]{2})?'
        ];

        // Config travels as data-attributes (HTML-attribute context, not JS).
        $inputAttrs['data-currency']        = $currency;
        $inputAttrs['data-currency-locale'] = $intlNumberFormat;

        $inputAttrs = self::appendErrorClass($inputAttrs, $errors, $name,'is-invalid');
        $divString = self::stringifyAttrs($divAttrs);
        $inputString = self::stringifyAttrs($inputAttrs);
        $id = Str::replace('[]','',$name);

        $html  = '<div' . $divString . '>';
        $html .= '<label class="form-label" for="' . htmlspecialchars($id) . '">'
            . htmlspecialchars($label) . '</label>';
        $html .= '<input type="text" id="' . htmlspecialchars($id) . '"'
            . ' name="' . htmlspecialchars($name) . '"'
            . ' value="' . htmlspecialchars($display) . '"'
            . $inputString . ' />';
        $html .= '<span class="invalid-feedback">'.self::errorMsg($errors, $name).'</span>';
        $html .= '</div>';
        return $html;
    }

    /**
     * Assists in the development of forms input blocks with datalist element in forms.  
     * It accepts parameters for setting attribute tags in the form section.  
     * 
     * 
     * @param string $type The input type we want to generate.
     * @param string $label Sets the label for this input.
     * @param string $name Sets the value for the name, for, and id attributes 
     * for this input.
     * @param string $listName The list name name and id for the datalist element.
     * @param mixed $value The value we want to set.  We can use this to set 
     * the value of the value attribute during form validation.  Default value 
     * is the empty string.  It can be set with values during form validation 
     * and forms used for editing records.
     * @param array $options A list of suggestions.
     * @param array $inputAttrs The values used to set the class and other 
     * attributes of the input string.  The default value is an empty array.
     * @param array $divAttrs The values used to set the class and other 
     * attributes of the surrounding div.  The default value is an empty array.
     * @param array $errors The errors array.  Default value is an empty array.
     * @return string A surrounding div and the input element.
     */
    public static function dataListBlock(
        string $type,
        string $label, 
        string $name, 
        string $listName,
        mixed $value = '',
        array $options = [],
        array $inputAttrs = [], 
        array $divAttrs = [],
        array $errors=[]
    ): string {

        $inputAttrs = self::appendErrorClass($inputAttrs, $errors, $name,'is-invalid');
        $divString = self::stringifyAttrs($divAttrs);
        $inputString = self::stringifyAttrs($inputAttrs);
        $id = Str::replace('[]','',$name);

        $html = '<div' . $divString . '>';
        $html .= '<label class="form-label" for="'.$id.'">'.$label.'</label>';
        $html .= '<input type="'.$type.'" list="'.$listName.'" id="'.$id.'" name="'.$name.'" value="'.$value.'"'.$inputString.' />';
        
        $html .= '<datalist id="'.$listName.'">';
        if($type === 'range') {
            foreach($options as $k => $v) {
                $html .= '<option value="'.$k.'" label="'.$v.'">';
            }
        } else {
            foreach($options as $option) {
                $html .= '<option value="'.$option.'">';
            }
        }
        $html .= '</datalist>';

        $html .= '<span class="invalid-feedback">'.self::errorMsg($errors, $name).'</span>';
        $html .= '</div>';
        return $html;
    }

    /**
     * Returns list of errors in the form of an error bag.
     * 
     * @param array|ArraySet $errors A list of errors and their description that is 
     * generated during server side form validation.
     * @return string The error bag.
     */
    public static function displayErrors(array|ArraySet $errors): string {
        // Ensure $errors is an Arr instance
        $errors = $errors instanceof ArraySet ? $errors : new ArraySet($errors);

        $hasErrors = !$errors->isEmpty() ? ' has-errors' : '';
        $html = '<div class="form-errors"><ul class="bg-light'.$hasErrors.'">';
        $logError = '';

        $errors->each(function($error) use (&$html, &$logError) {
            if (is_array($error)) {
                foreach ($error as $e) {
                    $html .= '<li class="text-danger">'.nl2br(htmlspecialchars($e)).'</li>';
                    $logError .= $e . ' ';
                }
            } else {
                $html .= '<li class="text-danger">'.nl2br(htmlspecialchars($error)).'</li>';
                $logError .= $error . ' ';
            }
        });
        $html .= '</ul></div>';
        if($hasErrors != '') {
            warning("Form validation failed: " . $logError);
        }
        
        return $html;
    }

    /**
     * Renders an HTML div element that surrounds an input of type email.
     *
     * An example function call is shown below:
     * FormHelper::emailBlock('Email', 'email', $this->contact->email, ['class' => 'form-control'], ['class' => 'form-group col-md-6'], $this->displayErrors);
     * 
     * Example HTML output is shown below:
     * <label for="email">Email</label><input type="email" id="email" name="email" value="" class="form-control" placeholder="joe_@_example.com" />
     * 
     * @param string $label Sets the label for this input.
     * @param string $name Sets the value for the name, for, and id attributes 
     * for this input.
     * @param mixed $value The value we want to set.  We can use this to set 
     * the value of the value attribute during form validation.  Default value 
     * is the empty string.  It can be set with values during form validation 
     * and forms used for editing records.
     * @param array $inputAttrs The values used to set the class and other 
     * attributes of the input string.  The default value is an empty array.
     * @param array $divAttrs The values used to set the class and other 
     * attributes of the surrounding div.  The default value is an empty array.
     * @param array $errors The errors array.  Default value is an empty array.
     * @return string A surrounding div and the input element of type email.
     * 
     * @deprecated 4.0.0 Use email() instead. Removal planned for 5.0.0.
     */
    #[\Deprecated(message: "use email() instead. Removal planned for 5.0.0", since: "4.0.0")]
    public static function emailBlock(
        string $label, 
        string $name, 
        mixed $value = '', 
        array $inputAttrs= [], 
        array $divAttrs = [], 
        array $errors = []
    ): string {

        return self::inputBlock(
            'email', 
            $label, 
            $name, 
            $value, 
            $inputAttrs, 
            $divAttrs,
            $errors
        );
    }

    /**
     * Renders an error message for a particular form field.
     *
     * @param array $errors The error array.
     * @param string $name Used to search errors array for key/form field.
     * @return string The error message for a particular field.
     */
    public static function errorMsg(array $errors, string $name): string {
        $value = (new ArraySet($errors))->get($name, "")->result();

        if (is_array($value)) {
            // Multiple errors for same field
            return implode('<br>', array_map('htmlspecialchars', $value));
        }

        return htmlspecialchars((string)$value);
    }

    /**
     * Renders an HTML div element that surrounds an input of type file.
     * 
     * Multiple File Uploads:
     * Use the $multiple flag to enable multiple file uploads.  Name attribute 
     * will be formatted correctly and the multiple attribute will be added to 
     * the input element.
     * 
     * Example: 
     * <?= FormHelper::fileBlock(
     *      "Upload Profile Image (Optional)", 
     *      'profileImage', 
     *      ['class' => 'form-control', 'accept' => 'image/gif image/jpeg image/png'], 
     *      ['class' => 'form-group mb-3']) 
     * ?>
     * 
     * @param string $label Sets the label for this input.
     * @param string $name Sets the value for the name, for, and id attributes 
     * for this input.
     * @param array $inputAttrs The values used to set the class and other 
     * attributes of the input string.  The default value is an empty array.
     * @param array $divAttrs The values used to set the class and other 
     * attributes of the surrounding div.  The default value is an empty array.
     * @param array $errors The errors array.  Default value is an empty array.
     * @param bool $multiple Flag for turning on or off multiple file uploads.
     * @return string A surrounding div and the input element of type file.
     */
    public static function fileBlock(
        string $label, 
        string $name, 
        array $inputAttrs= [], 
        array $divAttrs = [], 
        bool $multiple = false,
        array $errors = []
    ): string {
        // Test if $multiple is true and apply attributes.  Protect user if they 
        // add [] to name when $multiple flag is true.
        $baseName = Str::replace('[]','',$name);
        $name = $baseName;
        if($multiple) {
            $name .= '[]';
            $inputAttrs['multiple'] = 'multiple';
        }

        $inputAttrs = self::appendErrorClass($inputAttrs, $errors, $name,'is-invalid');
        $divString = self::stringifyAttrs($divAttrs);
        $inputString = self::stringifyAttrs($inputAttrs);

        $html = '<div' . $divString . '>';
        $html .= '<label class="form-label" for="'.$baseName.'">'.$label.'</label>';
        $html .= '<input type="file" id="'.$baseName.'" name="'.$name.'" value=""'.$inputString.' />';
        $html .= '<span class="invalid-feedback">'.self::errorMsg($errors, $baseName).'</span>';
        $html .= '</div>';
        return $html;
    }

    /**
     * Formats a raw decimal for display (initial render / after save).
     * Requires ext-intl.
     * 
     * @param $value - The value to format
     * @param string $currency The 3 digit currency name.
     * @param string $locale The international number format.
     * 
     * @return string The correct currency.
     */
    public static function formatCurrency(mixed $value, string $currency = 'USD', string $locale = 'en-US'): string {
        if ($value === '' || $value === null) return '';
        $number = (float) self::normalizeCurrency($value);
        $fmt = new \NumberFormatter($locale, \NumberFormatter::CURRENCY);
        return $fmt->formatCurrency($number, $currency);
    }

    /**
     * Raw stored value -> display string (grouping + fixed precision).
     */
    public static function formatNumber(mixed $value, int $decimals = 0, bool $useGrouping = false, string $locale = 'en-US'): string {
        if ($value === '' || $value === null) return '';
        $n = (float) self::normalizeNumber($value);
        $fmt = new \NumberFormatter($locale, \NumberFormatter::DECIMAL);
        $fmt->setAttribute(\NumberFormatter::FRACTION_DIGITS, $decimals);
        $fmt->setAttribute(\NumberFormatter::GROUPING_USED, $useGrouping ? 1 : 0);
        return $fmt->format($n);
    }
    
    /**
     * Creates a randomly generated csrf token.
     *
     * @return string The randomly generated token.
     */
    public static function generateToken(): string {
        if (!Session::exists('csrf_token')) {
            $token = base64_encode(openssl_random_pseudo_bytes(32));
            Session::set('csrf_token', $token);
        }
        return Session::get('csrf_token');
    }

    /**
     * Generates a hidden input element.
     * 
     * An example function call is shown below:
     * FormHelper::hidden("example_name", "example_value");
     * 
     * Example HTML output is shown below:
     * <input type="hidden" name="example_name" id="example_name" value="example_value" />
     * 
     * @param string $name The value for the name and id attributes.
     * @param mixed $value The value for the value attribute.
     * @return string The html input element with type hidden.
     */
    public static function hidden(string $name, mixed $value): string {
        return '<input type="hidden" name="'.$name.'" id="'.$name.'" value="'.$value.'" />';
    }

    /**
     * Create a input element of type image.
     * 
     * Example:
     * 
     * <?= FormHelper::image(
     *      'submit', 
     *      asset('public/logo.png', true), 
     *      100, 
     *      50, 
     *      ['class' => 'mt-5 pt-4']
     * ?>
     * 
     * @param string $id The id attribute for the image input.
     * @param string $src The path to the image file.
     * @param int $width The width of the image.
     * @param int $height The hight of the image.
     * @param array $inputAttrs The values used to set the class and other 
     * attributes of the input string.  The default value is an empty array.
     * @return string An input element of type image.
     */
    public static function image(string $id, string $src, int $width, int $height, array $inputAttrs = []): string {
        $inputString = self::stringifyAttrs($inputAttrs);
        return '<input type="image" id="'.$id.'" src="'.$src.'" width="'.$width.'" height="'.$height.'" '.$inputString.' />';
    }

    /**
     * Generates a div containing an input of type image.
     * 
     * Example:
     * 
     * <?= FormHelper::imageBlock(
     *      'submit', 
     *      asset('public/logo.png', true), 
     *      100, 
     *      50, 
     *      ['class' => 'mt-5 pt-4'], 
     *      ['class' => 'text-end'])
     * ?>
     * 
     * @param string $id The id attribute for the image input.
     * @param string $src The path to the image file.
     * @param int $width The width of the image.
     * @param int $height The hight of the image.
     * @param array $inputAttrs The values used to set the class and other 
     * attributes of the input string.  The default value is an empty array.
     * @param array $divAttrs The values used to set the class and other 
     * attributes of the surrounding div.  The default value is an empty array.
     * @return string A surrounding div and the input element of type image.
     */
    public static function imageBlock(
        string $id, 
        string $src, 
        int $width, 
        int $height,
        array $inputAttrs = [], 
        array $divAttrs = []
    ): string {
        $divString = self::stringifyAttrs($divAttrs);
        $inputString = self::stringifyAttrs($inputAttrs);
        $html = '<div'.$divString.'>';
        $html .= '<input type="image" id="'.$id.'" src="'.$src.'" width="'.$width.'" height="'.$height.'" '.$inputString.' />';
        $html .= '</div>';
        return $html;
    }

    /**
     * Assists in the development of forms input blocks in forms.  It accepts 
     * parameters for setting attribute tags in the form section.  Not to be 
     * used for inputs of type "Submit".  For submit inputs use the submitBlock 
     * or submitTag functions.
     * 
     * Types of inputs supported:
     * 1. color
     * 2. date
     * 3. datetime-local
     * 4. email
     * 5. file
     * 6. month
     * 7. number
     * 8. password
     * 9. range
     * 10. search
     * 11. tel
     * 12. text
     * 13. time
     * 14. url 
     * 15. week
     * 
     * An example function call is shown below:
     * FormHelper::inputBlock('text', 'Example', 'example_name', example_value, ['class' => 'form-control'], ['class' => 'form-group'], $this->displayErrors);
     * 
     * Example HTML output is shown below:
     * <div class="form-group">
     *     <label for="example">Example</label>
     *     <input type="text" id="example_name" name="example_name" value="example_value" class="form-control" />
     * </div>
     * 
     * @param string $type The input type we want to generate.
     * @param string $label Sets the label for this input.
     * @param string $name Sets the value for the name, for, and id attributes 
     * for this input.
     * @param mixed $value The value we want to set.  We can use this to set 
     * the value of the value attribute during form validation.  Default value 
     * is the empty string.  It can be set with values during form validation 
     * and forms used for editing records.
     * @param array $inputAttrs The values used to set the class and other 
     * attributes of the input string.  The default value is an empty array.
     * @param array $divAttrs The values used to set the class and other 
     * attributes of the surrounding div.  The default value is an empty array.
     * @param array $errors The errors array.  Default value is an empty array.
     * @return string A surrounding div and the input element.
     */
    public static function inputBlock(
        string $type, 
        string $label, 
        string $name, 
        mixed $value = '', 
        array $inputAttrs = [], 
        array $divAttrs = [],
        array $errors=[]
    ): string {

        $inputAttrs = self::appendErrorClass($inputAttrs, $errors, $name,'is-invalid');
        $divString = self::stringifyAttrs($divAttrs);
        $inputString = self::stringifyAttrs($inputAttrs);
        $id = Str::replace('[]','',$name);

        $html = '<div' . $divString . '>';
        $html .= '<label class="form-label" for="'.$id.'">'.$label.'</label>';
        $html .= '<input type="'.$type.'" id="'.$id.'" name="'.$name.'" value="'.$value.'"'.$inputString.' />';
        $html .= '<span class="invalid-feedback">'.self::errorMsg($errors, $name).'</span>';
        $html .= '</div>';
        return $html;
    }

    /**
     * Numeric input (integer or decimal) with optional thousands grouping
     * and fixed precision.
     *
     * Config keys (all optional):
     *   decimals    int   Decimal places. 0 = integer. Default 0.
     *   useGrouping bool  Thousands separators on display. Default false.
     *   min         int|float|null  HTML min. Default null (omitted).
     *   max         int|float|null  HTML max. Default null (omitted).
     *   step        int|float|string|null  HTML step. Default derived from decimals.
     *   locale      string  Intl locale for formatting. Default 'en-US'.
     *
     * Stores normalized (raw number, no separators); displays formatted.
     */
    public static function number(
        string $label,
        string $name,
        mixed $value = '',
        array $config = [],
        array $inputAttrs = [],
        array $divAttrs = [],
        array $errors = []
    ): string {
        $decimals    = (int)($config['decimals'] ?? 0);
        $useGrouping = (bool)($config['useGrouping'] ?? false);
        $min         = $config['min'] ?? null;
        $max         = $config['max'] ?? null;
        $locale      = (string)($config['locale'] ?? 'en-US');
        // step defaults to match precision: 1 for integers, 0.01 for 2 dp, etc.
        $step = $config['step'] ?? ($decimals > 0 ? '0.' . str_repeat('0', $decimals - 1) . '1' : '1');

        // Coherence guard (fail loud, like interval's min>max).
        if (is_numeric($min) && is_numeric($max) && $min > $max) {
            throw new \InvalidArgumentException(
                "number() for field '{$name}': min ({$min}) must not exceed max ({$max})"
            );
        }

        // Display: format the stored raw value for first paint.
        $display = ($value === '' || $value === null)
            ? ''
            : self::formatNumber($value, $decimals, $useGrouping, $locale);
 
        // Numeric constraints into attrs (named-config wins over passthrough).
        $inputAttrs['inputmode'] = $decimals > 0 ? 'decimal' : 'numeric';
        $inputAttrs['step']      = (string)$step;
        if ($min !== null) $inputAttrs['min'] = (string)$min;
        if ($max !== null) $inputAttrs['max'] = (string)$max;
        // Mark grouped fields so a JS enhancer (optional) can live-format.
        if ($useGrouping) {
            $inputAttrs['data-number-group']    = '1';
            $inputAttrs['data-number-decimals'] = (string)$decimals;
            $inputAttrs['data-number-locale']   = $locale;
        }

        $inputAttrs  = self::appendErrorClass($inputAttrs, $errors, $name, 'is-invalid');
        $divString   = self::stringifyAttrs($divAttrs);
        $inputString = self::stringifyAttrs($inputAttrs);
        $id = Str::replace('[]', '', $name);

        // type=text when grouping (commas aren't valid in type=number);
        // type=number otherwise for native spinners/validation.
        $type = $useGrouping ? 'text' : 'number';

        $html  = '<div' . $divString . '>';
        $html .= '<label class="form-label" for="' . htmlspecialchars($id) . '">'
            . htmlspecialchars($label) . '</label>';
        $html .= '<input type="' . $type . '" id="' . htmlspecialchars($id) . '"'
            . ' name="' . htmlspecialchars($name) . '"'
            . ' value="' . htmlspecialchars($display) . '"'
            . $inputString . ' />';
        $html .= '<span class="invalid-feedback">' . self::errorMsg($errors, $name) . '</span>';
        $html .= '</div>';
        return $html;
    }

    /**
     * Strips display formatting to a raw decimal string for storage.
     * Run this in the save handler BEFORE persisting a currency field.
     * 
     * @param mixed $value the value to normalize.
     * 
     * @return string The normalized value.
     */
    public static function normalizeCurrency(mixed $value): string {
        if ($value === null || $value === '') return '';
        // Keep digits, one decimal point, optional leading minus.
        $clean = preg_replace('/[^0-9.\-]/', '', (string)$value);
        return $clean === '' ? '' : $clean;
    }

    /**
     * Display string -> raw numeric string for storage.
     * Strips grouping separators and any non-numeric chrome; keeps one
     * decimal point and a leading minus.
     */
    public static function normalizeNumber(mixed $value): string {
        if ($value === null || $value === '') return '';
        $clean = preg_replace('/[^0-9.\-]/', '', (string)$value);
        return $clean === '' ? '' : $clean;
    }

    /**
     * Generates options for select.
     *
     * @param array $options An array of options for the select.
     * @param string|int|null $selectedValue The currently selected value.
     * @return string The HTML element for select with correct value displayed.
     */
    public static function optionsForSelect(array $options, string|int|null $selectedValue): string {
        $html = "";
        foreach($options as $value => $display){
            $selStr = ($selectedValue == $value) ? ' selected="selected"' : '';
            $html .= '<option value="'.$value.'"'.$selStr.'>'.$display.'</option>';
        }
        return $html;  
    }

    /** 
     * Generates an HTML output element.
     * 
     * An example function call is shown below:
     * FormHelper::output("my_name", "for_value")
     * 
     * Example HTML output is shown below:
     * <output name="my_name" for="for_value"></output>
     * 
     * @param string $name Sets the value for the name attributes for this 
     * input.
     * @param string $for Sets the value for the for attribute.
     * @return string The HTML output element.
     */
    public static function output(string $name, string $for): string {
        return '<output name="'.$name.'" for="'.$for.'"></output>';
    }

    /**
     * Performs sanitization of values obtained during $_POST.
     *
     * @param array $post Values from the $_POST superglobal array when the 
     * user submits a form.
     * @return array An array of sanitized values from the submitted form.
     */
    public static function posted_values(array $post): array {
        return (new ArraySet($post))->map(fn($value) => self::sanitize($value))->all();
    }

    /**
     * Renders a radio button group based on options provided.
     * 
     * Example:
     * <?= FormHelper::radioGroup(
     *      'inactive',                       // name
     *      [0 => 'Active', 1 => 'Inactive'], // [value => label] map
     *      $this->user->inactive,            // selected value: 0 or 1
     *      ['class' => 'form-check-input'],  // inputAttrs (applied to every radio)
     *      ['class' => 'form-group mb-3'],   // divAttrs
     *      $this->displayErrors              // errors
     * ); ?>
     * 
     * @param string $name Sets the value for the name attribute 
     * for this input.
     * @param array $options The list of options we will use to populate the 
     * radio group.
     * @param string|int|bool $selectedValue The selected value.
     * @param array $inputAttrs The values used to set the class and other 
     * attributes of the input string.  The default value is an empty array.
     * @param array $divAttrs The values used to set the class and other 
     * attributes of the surrounding div.  The default value is an empty array.
     * @param array $errors The errors array.  Default value is an empty array.
     * @return string A surrounding div and option select element.
     */
    public static function radioGroup(
        string $name,
        array $options = [],              // [value => label], same shape as optionsForSelect
        string|int|null $selectedValue = '',
        array $inputAttrs = [],
        array $divAttrs = [],
        array $errors = []
    ): string {
        $inputAttrs = self::appendErrorClass($inputAttrs, $errors, $name, ' is-invalid');
        $inputAttrs['class'] .= ' me-2';
        $divString  = self::stringifyAttrs($divAttrs);

        $html = '<div' . $divString . '>';
        foreach ($options as $optValue => $label) {
            $optValue = (string)$optValue;
            $checked = ($selectedValue == $optValue);
            $html .= self::radioInput($label, $name, $optValue, $checked, $inputAttrs);
        }
        $html .= '<span class="invalid-feedback">' . self::errorMsg($errors, $name) . '</span>';
        $html .= '</div>';
        return $html;
    }

    /**
     * Creates an input element of type radio with an accompanying label 
     * element.  Compatible with radio button groups.
     *
     * Example: 
     * 
     * <?= FormHelper::radioInput(
     *      "Phone", 
     *      'contact', 
     *      'phone', 
     *      false, 
     *      ['class' => 'form-group mb-3']) 
     * ?>
     * 
     * @param string $label Sets the label for this input.
     * @param string $name Sets the value for the name attribute 
     * for this input.
     * @param string $value The value we want to set.  We can use this to set 
     * the value of the value attribute during form validation.  It can be 
     * set with values during form validation and forms used for editing records.
     * @param bool $checked The value for the checked attribute.  If true 
     * this attribute will be set as checked="checked".  The default value is 
     * false.  It can be set with values during form validation and forms 
     * used for editing records.
     * @param array $inputAttrs The values used to set the class and other 
     * attributes of the input string.  The default value is an empty array.
     * @return string The HTML input element of type radio.
     */
    public static function radioInput(
        string $label, 
        string $name, 
        string $value, 
        bool $checked = false, 
        array $inputAttrs = [],
        ): string {

        $inputString = self::stringifyAttrs(($inputAttrs));
        $checkString = ($checked) ? ' checked="checked"' : '';
        $id = $name . '_' . $value;   // derive once, use for both

        return '<input type="radio" id="'.$id.'" name="'.$name.'" value="'.$value.'"'.$checkString.$inputString.'>'
         . '<label class="form-label me-3" for="'.$id.'">'.$label.'</label> ';
    }

    /**
     * Sanitizes potentially harmful string of characters.
     * 
     * @param string $dirty The potentially dirty string.
     * @return string The sanitized version of the dirty string.
     */
    public static function sanitize(string|array $dirty): string|array {
        if (is_array($dirty)) {
            return Arr::map($dirty, [self::class, 'sanitize']); // Recursively sanitize arrays
        }
        return htmlentities((string)$dirty, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Renders a select element with a list of options.
     * 
     * An example function call is shown below:
     * <?= FormHelper::selectBlock(
     *      'Account Status',                 // label
     *      'inactive',                       // name — matches the schema column
     *      $this->user->inactive,            // current value: 0 or 1
     *      [0 => 'Active', 1 => 'Inactive'], // [value => label] map
     *      ['class' => 'form-select'],       // inputAttrs
     *      ['class' => 'form-group mb-3'],   // divAttrs
     *      $this->displayErrors              // errors
     * ); ?>
     * 
     * @param string $label Sets the label for this input.
     * @param string $name Sets the value for the name, for, and id attributes 
     * for this input.
     * @param string $value The value we want to set as selected.
     * @param array $options The list of options we will use to populate the 
     * select option dropdown.
     * @param array $inputAttrs The values used to set the class and other 
     * attributes of the input string.  The default value is an empty array.
     * @param array $divAttrs The values used to set the class and other 
     * attributes of the surrounding div.  The default value is an empty array.
     * @param array $errors The errors array.  Default value is an empty array.
     * @return string A surrounding div and option select element.
     */
    public static function selectBlock(
        string $label, 
        string $name, 
        string|int|null $value, 
        array $options, 
        array $inputAttrs = [], 
        array $divAttrs = [], 
        array $errors = []
    ): string{
        $inputAttrs = self::appendErrorClass($inputAttrs, $errors, $name,' is-invalid');
        $divString = self::stringifyAttrs($divAttrs);
        $inputString = self::stringifyAttrs($inputAttrs);
        $id = Str::replace('[]' ,'' ,$name);
        $html = '<div' . $divString . '>';
        $html .= '<label class="form-label" for="'.$id.'" class="form-label">' . $label . '</label>';
        $html .= '<select id="'.$id.'" name="'.$name.'" '.$inputString.'>'.self::optionsForSelect($options, $value).'</select>';
        $html .= '<span class="invalid-feedback">'.self::errorMsg($errors, $name).'</span>';
        $html .= '</div>';
        return $html;
    }

    /**
     * Stringify attributes.
     * 
     * @param array $attrs The attributes we want to stringify.
     * @return string The stringified attributes.
     */
    public static function stringifyAttrs(array $attrs) {
        $string = '';
        (new ArraySet($attrs))->each(function($val, $key) use (&$string) {
            $string .= " $key=\"$val\"";
        });
        return $string;
    }

    /**
     * Generates a div containing an input of type submit.
     * 
     * An example function call is shown below:
     * FormHelper::submitBlock("Save", ['class'=>'btn btn-primary'], ['class'=>'text-end']);
     * 
     * Example HTML output is shown below:
     * <div class="text-end">
     *     <input type="submit" value="Save" class="btn btn-primary" />
     * </div>
     * 
     * @param string $buttonText Sets the value of the text describing the 
     * button.
     * @param array $inputAttrs The values used to set the class and other 
     * attributes of the input string.  The default value is an empty array.
     * @param array $divAttrs The values used to set the class and other 
     * attributes of the surrounding div.  The default value is an empty array.
     * @return string A surrounding div and the input element of type submit.
     */
    public static function submitBlock(
        string $buttonText, 
        array $inputAttrs = [], 
        array $divAttrs = []
    ): string {
        $divString = self::stringifyAttrs($divAttrs);
        $inputString = self::stringifyAttrs($inputAttrs);
        $html = '<div'.$divString.'>';
        $html .= '<input type="submit" value="'.$buttonText.'"'.$inputString.' />';
        $html .= '</div>';
        return $html;
    }
    
    /**
     * Create a input element of type submit.
     * 
     * An example function call is shown below:
     * FormHelper::submitTag("Save", ['class'=>'btn btn-primary']);
     * 
     * or 
     * 
     * self::submitTag("Save", ['class'=>'btn btn-primary']);
     * 
     * Example HTML output is shown below:
     * <input type="submit" value="Save" class="btn btn-primary" />
     * 
     * @param string $buttonText Sets the value of the text describing the 
     * button.
     * @param array $inputAttrs The values used to set the class and other 
     * attributes of the input string.  The default value is an empty array.
     * @return string An input element of type submit.
     */
    public static function submitTag(string $buttonText, array $inputAttrs = []): string {
        $inputString = self::stringifyAttrs($inputAttrs);
        return '<input type="submit" value="'.$buttonText.'"'.$inputString.' />';
    }
    
    /**
     * Renders an HTML div element that surrounds an input of type tel.
     * 
     * Example:
     * 
     * <?= FormHelper::telBlock(
     *      'Home phone', 
     *      'phone', 
     *      $this->user->phone, 
     *      ['class' => 'form-control input-sm'], 
     *      ['class' => 'form-group mb-3']) 
     * ?>
     * 
     * @param string $label Sets the label for this input.
     * @param string $name Sets the value for the name, for, and id attributes 
     * for this input.
     * @param mixed $value The value we want to set.  We can use this to set 
     * the value of the value attribute during form validation.  Default value 
     * is the empty string.  It can be set with values during form validation 
     * and forms used for editing records.
     * @param array $inputAttrs The values used to set the class and other 
     * attributes of the input string.  The default value is an empty array.
     * @param array $divAttrs The values used to set the class and other 
     * attributes of the surrounding div.  The default value is an empty array.
     * @param array $errors The errors array.  Default value is an empty array.
     * 
     * @deprecated 4.0.0 Use tel() instead. Removal planned for 5.0.0.
     * @return string The HTML div element surrounding an input of type tel 
     * with configuration and values set based on parameters entered during 
     * function call.
     */
    #[\Deprecated(message: "use tel() instead. Removal planned for 5.0.0", since: "4.0.0")]
    public static function telBlock(
        string $label,
        string $name,
        mixed $value = '',
        array $inputAttrs = [],
        array $divAttrs = [],
        array $errors = []
    ): string {
        
        $inputAttrs += [
            'autocomplete' => 'tel',
            'inputmode'    => 'tel',
            'placeholder'  => '(555) 123-4567',
            'pattern' => '[0-9]{3}-[0-9]{3}-[0-9]{4}'
        ];
        return self::inputBlock(
            'tel',
            $label,
            $name,
            $value,
            $inputAttrs,
            $divAttrs,
            $errors
        );
    }

    /**
     * Assists in the development of textarea in forms.  It accepts parameters 
     * for setting  attribute tags in the form section.
     * 
     * An example function call is shown below:
     * FormHelper::textAreaBlock("Example", 'example_name', example_value, ['class' => 'form-control input-sm', 'placeholder' => 'foo'], ['class' => 'form-group'], $this->displayErrors);
     * 
     * Example HTML output is shown below:
     * <div class="form-group">
     *     <label for="example_name">Example</label>
     *     <textarea id="example_name" name="example_name"  class="form-control input-sm" placeholder="foo">example_value</textarea>
     * </div>
     * 
     * @param string $label Sets the label for this input.
     * @param string $name Sets the value for the name, for, and id attributes 
     * for this input.
     * @param string|null $value The value we want to set.  We can use this to set 
     * the value of the value attribute during form validation.  Default value 
     * is the empty string.  It can be set with values during form validation 
     * and forms used for editing records.
     * @param array $inputAttrs The values used to set the class and other 
     * attributes of the input string.  The default value is an empty array.
     * @param array $divAttrs The values used to set the class and other 
     * attributes of the surrounding div.  The default value is an empty array.
     * @param array $errors The errors array.  Default value is an empty array.
     * @return string A surrounding div and the textarea element.
     */
    public static function textareaBlock(
        string $label, 
        string $name, 
        string|null $value, 
        array $inputAttrs=[], 
        array $divAttrs=[], 
        array $errors=[]
    ): string {
        $inputAttrs = self::appendErrorClass($inputAttrs,$errors,$name,'is-invalid');
        $divString = self::stringifyAttrs($divAttrs);
        $inputString = self::stringifyAttrs($inputAttrs);
        $id = Str::replace('[]','',$name);
        $html = '<div' . $divString . '>';
        $html .= '<label class="form-label" for="'.$id.'" class="form-label">' . $label . '</label>';
        $html .= '<textarea id="'.$id.'" name="'.$name.'"'.$inputString.'>'.$value.'</textarea>';
        $html .= '<span class="invalid-feedback">'.self::errorMsg($errors, $name).'</span>';
        $html .= '</div>';
        return $html;
    }
}