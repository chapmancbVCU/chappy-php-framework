/**
 * Redirect browser to the specified path.
 * 
 * @param {path} path The path to be redirected.
 */
export default function redirect(path) {
    window.location.assign(path);
}