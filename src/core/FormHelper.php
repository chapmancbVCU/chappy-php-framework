<?php
declare(strict_types=1);
namespace Core;

use Core\Exceptions\FrameworkException;
use Core\Session;
use Core\Lib\Logging\Logger;
use Core\Lib\Utilities\Arr;
use Core\Lib\Utilities\Str;
use Core\Lib\Utilities\ArraySet;
/**
 * Contains functions for building form elements of various types.
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
     * @return array $attrs Div attributes with error classes added.
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
     * Supports ability to create a styled button.  Supports ability to have 
     * functions for event handlers.
     * 
     * An example function call is shown below:
     * FormHelper::button("Click Me!", ['class' => 'btn btn-large btn-primary', 'onClick' => 'alert(\'Hello World!\')']);
     * 
     * Example HTML output is shown below:
     * <button type="button"  class="btn btn-large btn-primary" onClick="alert('Hello World!')">Click Me!</button>
     * 
     * @param string $buttonText The contents of the button's label.
     * @param array $inputAttrs The values used to set the class and other 
     * attributes of the input string.  The default value is an empty array.
     * @return string An HTML button element with its label set and any other 
     * optional attributes set.
     */
    public static function button(string $buttonText, array $inputAttrs = []): string {
        $inputString = self::stringifyAttrs($inputAttrs);
        return '<button type="button" '.$inputString.'>'.$buttonText.'</button>';
    }

    /**
     * Supports ability to create a styled button and styled surrounding div 
     * block.  Supports ability to have functions for event handlers".
     * 
     * An example function call is shown below:
     * FormHelper::buttonBlock("Click Me!", ['class' => 'btn btn-large btn-primary', 'onClick' => 'alert(\'Hello World!\')'], ['class' => 'form-group']);
     * 
     * Example HTML output is shown below:
     * <div class="form-group"><button type="button"  class="btn btn-large btn-primary" onClick="alert('Hello World!')">Click Me!</button></div> 
     * 
     * @param string $buttonText The contents of the button's label.
     * @param array $inputAttrs The values used to set the class and other 
     * attributes of the input string.  The default value is an empty array.
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
     * FormHelper::checkboxBlockLabelRight('Remember Me', 'remember_me', 'on', $this->login->getRememberMeChecked(), [], ['class' => 'form-group'], $this->displayErrors);
     * 
     * Example HTML output is shown below:
     * <div class="form-group">
     *     <input type="checkbox" id="remember_me" name="remember_me" value="on" />
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
        $id = Str::replace('[]', '', $name); // Ensure unique ID
    
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
     * FormHelper::checkboxBlockLabelRight('Remember Me', 'remember_me', 'on', $this->login->getRememberMeChecked(), [], ['class' => 'form-group mr-1'], $this->displayErrors);
     * 
     * Example HTML output is shown below:
     * <div>
     *     <input type="checkbox" id="remember_me" name="remember_me" value="on" class="form-group mr-1">
     *     <label for="remember_me">Remember Me</label>
     * </div> 
     * 
     * @param string $type The input type we want to generate.
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
        $id = Str::replace('[]', '', $name); // Ensure unique ID
    
        $html = '<div' . $divString . '>';
        $html .= '<input class="form-label" type="checkbox" id="' . htmlspecialchars($id) . '" name="' . $nameWithBrackets . '" value="' . htmlspecialchars($value) . '"' . $checkString . $inputString . '> ';
        $html .= '<label for="' . htmlspecialchars($id) . '">' . htmlspecialchars($label) . '</label>';
        $html .= '<span class="invalid-feedback">' . self::errorMsg($errors, $name) . '</span>';
        $html .= '</div>';
        
        return $html;
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
     * Example HTML output is shown below:
     * <input type="hidden" name="csrf_token" id="csrf_token" value="RANDOM_STRING_OF_VALUES" />
     * 
     * @return string The hidden input of type hidden with the generated token 
     * set as the value.
     */
    public static function csrfInput(): string {
        return '<input type="hidden" name="csrf_token" id="csrf_token" value="' . self::generateToken() . '" />';
    }


    /**
     * Returns list of errors.
     * 
     * @param array|ArraySet $errors A list of errors and their description that is 
     * generated during server side form validation.
     * @return string A string representation of a div element containing an 
     * input of type checkbox.
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
                    $html .= '<li class="text-danger">'.htmlspecialchars($e).'</li>';
                    $logError .= $e . ' ';
                }
            } else {
                $html .= '<li class="text-danger">'.htmlspecialchars($error).'</li>';
                $logError .= $error . ' ';
            }
        });
        $html .= '</ul></div>';
        if($hasErrors != '') {
            Logger::log("Form validation failed: " . $logError, Logger::WARNING);
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
     */
    public static function emailBlock(
        string $label, 
        string $name, 
        mixed $value = '', 
        array $inputAttrs= [], 
        array $divAttrs = [], 
        array $errors = []
    ): string {

        // Make sure placeholder is not an attribute.
        if(arr::exists($inputAttrs, 'placeholder')) {
            throw new FrameworkException('Can not accept placeholder attribute found in your $inputString array.');
        }

        $inputAttrs = self::appendErrorClass($inputAttrs,$errors,$name,'is-invalid');
        $divString = self::stringifyAttrs($divAttrs);
        $inputString = self::stringifyAttrs(($inputAttrs));
        
        $html = '<div' . $divString . '>';
        $html .= '<label class="form-label" for="'.$name.'">'.$label.'</label>';
        $html .= '<input type="email" id="'.$name.'" name="'.$name.'" value="'.$value.'"'.$inputString.' placeholder="joe@example.com" />';
        $html .= '<span class="invalid-feedback">'.self::errorMsg($errors, $name).'</span>';
        $html .= '</div>';
        return $html;
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
     * Assists in the development of forms input blocks in forms.  It accepts 
     * parameters for setting attribute tags in the form section.  Not to be 
     * used for inputs of type "Submit"  For submit inputs use the submitBlock 
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
    public static function inputBlock(string $type, 
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
     * Creates an input element of type radio with an accompanying label 
     * element.  Compatible with radio button groups.
     *
     * An example function call is shown below:
     * FormHelper::radioInput('HTML', 'html', 'fav_language', "HTML", $check1, ['class' => 'form-group mr-1']);
     * FormHelper::radioInput('CSS', 'css', 'fav_language', "CSS", $check2, ['class' => 'form-group mr-1']);
     * 
     * Example HTML output is shown below:
     * <input type="radio" id="html" name="fav_language" value="HTML" class="form-group mr-1">
     * <label for="html">HTML</label>  <br>
     * <input type="radio" id="css" name="fav_language" value="CSS" class="form-group mr-1">
     * <label for="css">CSS</label>
     * 
     * @param string $label Sets the label for this input.
     * @param string $id The id attribute for the radio input element.
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
    public static function radioInput(string $label, 
        string $id, 
        string $name, 
        string $value, 
        bool $checked = false, 
        array $inputAttrs = [],
        ): string {

        $inputString = self::stringifyAttrs(($inputAttrs));
        $checkString = ($checked) ? ' checked="checked"' : '';
        return '<input type="radio" id="'.$id.'" name="'.$name.'" value="'.$value.'"'.$checkString.$inputString.'><label class="form-label me-3" for="'.$id.'">'.$label.'</label> ';
    }
    
    /**
     * Sanitizes potentially harmful string of characters.
     * 
     * @param string $dirty The potentially dirty string.
     * @return string The sanitized version of the dirty string.
     */
    public static function sanitize(string|array $dirty): string|array {
        if (Arr::isArray($dirty)) {
            return Arr::map([self::class, 'sanitize'], $dirty); // Recursively sanitize arrays
        }
        return htmlentities((string)$dirty, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Renders a select element with a list of options.
     * 
     * An example function call is shown below:
     * FormHelper::selectBlock("Test", "test", $_POST["test"],['A' => 'a','B' => 'b', 'C' => 'c'], ['class' => 'form-control'], ['class' => 'form-group'], $this->displayErrors);
     *
     * Example HTML output is shown below:
     * <div class="form-group">
     *     <label for="test">Test</label>
     *     <select id="test" name="test" value=""  class="form-control">
     *         <option>---Please select an item--</option>
     *         <option value="a">A</option>
     *         <option value="b">B</option>
     *         <option value="c">C</option>
     *     </select>
     * </div>
     * 
     * @param string $label Sets the label for this input.
     * @param string $name Sets the value for the name, for, and id attributes 
     * for this input.
     * @param string $value The value we want to set as selected.
     * @param array $inputAttrs The values used to set the class and other 
     * attributes of the input string.  The default value is an empty array.
     * @param array $options The list of options we will use to populate the 
     * select option dropdown.  The default value is an empty array.
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
     * @param string A surrounding div and the input element of type submit.
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
     * @return string The HTML div element surrounding an input of type tel 
     * with configuration and values set based on parameters entered during 
     * function call.
     */
    public static function telBlock(
        string $label,
        string $name,
        mixed $value = '',
        array $inputAttrs = [],
        array $divAttrs = [],
        array $errors = []
    ): string {
        $inputAttrs = self::appendErrorClass($inputAttrs, $errors, $name, 'is-invalid');
        $inputString = self::stringifyAttrs($inputAttrs);
        $divString = self::stringifyAttrs($divAttrs);

        // Build field
        $html = '<div' . $divString . '>';
        $html .= '<label class="form-label" for="' . $name . '">' . $label . '</label>';
        $html .= '<input type="tel" id="' . $name . '" name="' . $name . '" value="' . $value . '" ' . $inputString . ' />';
        $html .= '<span class="invalid-feedback">' . self::errorMsg($errors, $name) . '</span>';
        $html .= '</div>';

        return $html;
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
     * @param string A surrounding div and the textarea element.
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