<?php

use Core\FormHelper;
use Core\Lib\Utilities\ArraySet;

if(!function_exists('checkboxLeft')) {
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
    function checkboxLeft(
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

if(!function_exists('checkboxRight')) {
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
    function checkboxRight(
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
}