<?php

use Core\FormHelper;
use Core\Lib\Utilities\ArraySet;

if(!function_exists('checkboxLabelLeft')) {
    /**
     * Generates a div containing an input of type checkbox with the label to 
     * the left that is not part of a group.
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
    function checkboxLabelLeft(
        string $label, 
        string $name, 
        string $value = "",
        bool $checked = false, 
        array $inputAttrs = [], 
        array $divAttrs = [],
        array $errors = []
    ): string {
        return FormHelper::checkboxBlockLabelLeft(
            $label, 
            $name, 
            $value,
            $checked, 
            $inputAttrs,
            $divAttrs,
            $errors
        );
    }
}

if(!function_exists('checkboxLabelRight')) {
    /**
     * Generates a div containing an input of type checkbox with the label to 
     * the right that is not part of a group.
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
    function checkboxLabelRight(
        string $label, 
        string $name, 
        string $value = "",
        bool $checked = false, 
        array $inputAttrs = [], 
        array $divAttrs = [],
        array $errors = []
    ): string {
        return FormHelper::checkboxBlockLabelRight(
            $label, 
            $name, 
            $value,
            $checked, 
            $inputAttrs,
            $divAttrs,
            $errors
        );
    }
}

if(!function_exists('csrf')) {
    /**
     * Inserts csrf token into form.
     *
     * @return void
     */
    function csrf() {
        return FormHelper::csrfInput();
    }  
}

if(!function_exists('errorBag')) {
    /**
     * Returns list of errors.
     * 
     * @param array|ArraySet $errors A list of errors and their description that is 
     * generated during server side form validation.
     * @return string A string representation of a div element containing an 
     * input of type checkbox.
     */
    function errorBag(array|ArraySet $errors): string {
        return FormHelper::displayErrors($errors);
    }
}

if(!function_exists('email')) {
    /**
     * Renders an HTML div element that surrounds an input of type email.
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
    function email(
        string $label, 
        string $name, 
        mixed $value = '', 
        array $inputAttrs= [], 
        array $divAttrs = [], 
        array $errors = []
    ): string {
        return FormHelper::emailBlock(
            $label, 
            $name, 
            $value, 
            $inputAttrs, 
            $divAttrs, 
            $errors
        );
    }
}

if(!function_exists('hidden')) {
    /**
     * Generates a hidden input element.
     * 
     * @param string $name The value for the name and id attributes.
     * @param mixed $value The value for the value attribute.
     * @return string The html input element with type hidden.
     */
    function hidden(string $name, mixed $value): string {
        return FormHelper::hidden($name, $value);
    }
}

if(!function_exists('input')) {
    /**
     * Assists in the development of forms input blocks in forms.  It accepts 
     * parameters for setting attribute tags in the form section.  Not to be 
     * used for inputs of type "Submit"  For submit inputs use the submitBlock 
     * or submitTag functions.
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
    function input(
        string $type, 
        string $label, 
        string $name, 
        mixed $value = '', 
        array $inputAttrs = [], 
        array $divAttrs = [],
        array $errors=[]
    ): string {
        return FormHelper::inputBlock(
            $type, 
            $label, 
            $name, 
            $value, 
            $inputAttrs, 
            $divAttrs,
            $errors
        );
    }
}

if(!function_exists('output')) {
    /** 
     * Generates an HTML output element.
     * 
     * @param string $name Sets the value for the name attributes for this 
     * input.
     * @param string $for Sets the value for the for attribute.
     * @return string The HTML output element.
     */
    function output(string $name, string $for): string {
        return FormHelper::output($name, $for);
    }
}

if(!function_exists('radio')) {
    /**
     * Creates an input element of type radio with an accompanying label 
     * element.  Compatible with radio button groups.
     *
     * @param string $label Sets the label for this input.
     * @param string $id The id attribute for the radio input element.
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
     * @return string The HTML input element of type radio.
     */
    function radio(
        string $label, 
        string $id, 
        string $name, 
        string $value, 
        bool $checked = false, 
        array $inputAttrs = [],
    ): string {
        return FormHelper::radioInput(
            $label, 
            $id, 
            $name, 
            $value, 
            $checked, 
            $inputAttrs
        );
    }
}

if(!function_exists('select')) {
    /**
     * Renders a select element with a list of options.
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
    function select(
        string $label, 
        string $name, 
        string|int|null $value, 
        array $options, 
        array $inputAttrs = [], 
        array $divAttrs = [],
        array $errors = []
    ): string {
        return FormHelper::selectBlock(
            $label, 
            $name, 
            $value, 
            $options, 
            $inputAttrs, 
            $divAttrs,
            $errors
        );
    };
}