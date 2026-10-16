import { useEffect } from 'react';

/**
 * Retrieves assets from project or S3 bucket.
 * @param {string} path The path to the asset.
 * @param {bool} local If true use APP_DOMAIN instead of S3_BUCKET.
 * @returns The full path to the asset.
 */
export function asset(path, local = false) {
    if(local == true) {
        return import.meta.env.VITE_APP_DOMAIN + path;    
    }
    return import.meta.env.VITE_S3_BUCKET + path;
}

/**
 * Sets the document title in tab.
 * @param {string} title The title to be used. 
 */
export function documentTitle(title) {
  useEffect(() => {
    if (!title) return;
    const prev = document.title;
    document.title = title;
    return () => { document.title = prev; };
  }, [title]);
}

/**
 * Redirect browser to the specified path.
 * 
 * @param {path} path The path to be redirected.
 */
export function redirect(path) {
    window.location.assign(path);
}

/**
 * Route function for views that supports dot notation and array of parameters.
 * @param {string} path The controller name concatenated with the action 
 * name using dot notation.
 * @param {array} params The parameters for the action.
 * @returns {string} The URL
 */
export function route(path, params = []) {
    const parts = path.split('.');
    const controller = parts[0];
    const action = (parts.length === 2) ? parts[1] : 'index';

    let domain = trimDomain(import.meta.env.VITE_APP_DOMAIN);
    let url = domain + '/' + trimParts(controller) + "/" + trimParts(action);

    if(url.length !== 0) {
        url += '/' + params.map(encodeURIComponent).join('/');
    } 

    return url;
}

/**
 * Trims domain if it contains trailing forward slashes.
 * @param {string} domain The application domain.
 * @returns {string} The updated domain string.
 */
function trimDomain(domain) {
    return (domain.endsWith('/')) ? domain.slice(0, -1) : domain;
}

/**
 * Trims any forward slashes from action or controller names.
 * @param {string} part The part of the URL to be trimmed.
 * @returns {string} The trimmed action or controller name.
 */
function trimParts(part) {
    return part.replace(/\//g, "");
}
