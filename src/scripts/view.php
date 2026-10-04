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

if(!function_exists('initVite')) {
    /**
     * Initializes vite in head of layout.
     *
     * @param boolean $isDev True if in dev mode.  Otherwise we return false.
     * @return string The scripts and other resources needed to initialize vite.
     */
    function initVite(bool $isDev) {
        $html = " ";
        if ($isDev) {
            $html .= "<!-- React Fast Refresh preamble -->";
            $html .= "<script type=\"module\">
                import RefreshRuntime from 'http://localhost:5173/@react-refresh'
                RefreshRuntime.injectIntoGlobalHook(window)
                window.\$RefreshReg$ = () => {}
                window.\$RefreshSig$ = () => (type) => type
                window.__vite_plugin_react_preamble_installed__ = true
            </script>";

            $html .= "<!-- Vite HMR client + your React entry from DEV SERVER -->";
            $html .= "<script type=\"module\" src=\"http://localhost:5173/@vite/client\"></script>";
            $html .= "<script type=\"module\" src=\"http://localhost:5173/resources/js/app.jsx\"></script>";
        } else {
            $html .= "<!-- PRODUCTION: hashed assets from manifest -->";
            $html .= "<link rel=\"stylesheet\" href=\"".vite('resources/css/app.css')."\">";
            $html .= "<script type=\"module\" src=\"".vite('resources/js/app.jsx')."\"></script>";
        }

        return $html;
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

if(!function_exists('resources')) {
    /**
     * Retrieves all resources needed for views to operate.  This function is called in the head section of the view in the templates.
     *
     * @return string The stylesheets and scripts for the view.
     */
    function resources(): string {
        $html = '<link rel="stylesheet" href="'.env('APP_DOMAIN', '/').'node_modules/bootstrap/dist/css/bootstrap.min.css" media="screen" title="no title" charset="utf-8">';
        $html .= '<link rel="stylesheet" href="'.env('APP_DOMAIN', '/').'resources/css/alerts/alertMsg.min.css?v='.config('config.version').'" media="screen" title="no title" charset="utf-8">';
        $html .= '<link rel="stylesheet" href="'.env('APP_DOMAIN', '/').'node_modules/@fortawesome/fontawesome-free/css/all.min.css" media="screen" title="no title" charset="utf-8">';
        $html .= '<script src="'.env('APP_DOMAIN', '/').'resources/js/alerts/alertMsg.min.js?v='.config('config.version').'"></script>';
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