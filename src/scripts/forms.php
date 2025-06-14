<?php

use Core\FormHelper;
use Core\Lib\Utilities\ArraySet;

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