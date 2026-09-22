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

if(!function_exists('profileImageSort')) {
    /**
     * Loads scripts and styling for sorting of profile images.
     *
     * @return string The scripts needed to sort profile images.
     */
    function profileImageSort(): string {
        $html = "<link rel=\"stylesheet\" href=\"".env('APP_DOMAIN', '/')."resources/css/profileImage.css?v=".config('config.version')."\" media=\"screen\" title=\"no title\" charset=\"utf-8\">";
        $html .= "<script src='".env('APP_DOMAIN', '/')."node_modules/jquery/dist/jquery.min.js'></script>";
        $html .= "<script type='text/javascript' src='".env('APP_DOMAIN', '/')."node_modules/jquery-ui/dist/jquery-ui.min.js'></script>";
        $html .= "<script type='text/javascript' src='".env('APP_DOMAIN', '/')."node_modules/jquery-ui/ui/widgets/sortable.js'></script>";
        return $html;
    }
}