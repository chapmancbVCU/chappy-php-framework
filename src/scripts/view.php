<?php

if(!function_exists('initTinyMCE')) {
    /**
     * Returns script for initializing tinyMCE.
     *
     * @param string $id The id attribute of the textarea element we are targeting.
     * @return string The script that initializes tinyMCE.
     */
    function initTinyMCE(string $id): string {
       return "<script>
                   initializeTinyMCE('{$id}');
               </script>";
    }
}
if(!function_exists('loadTinyMCE')) {
    /**
     * Returns scripts to load tinyMCE.
     *
     * @return string The scripts for loading tinyMCE>
     */
    function loadTinyMCE(): string {
        $html = "<script src=\"".env('APP_DOMAIN', '/')."vendor/tinymce/tinymce/tinymce.min.js?v=".config('config.version')."\"></script>";
        $html .= "<script src='".env('APP_DOMAIN', '/')."vendor/chappy-php/chappy-php-framework/src/React/utils/phpTinyMCE.js'></script>";
        return $html;
    }
}