/**
 * Retrieves assets from project.
 * @param {string} path The path to the asset.
 * @returns The full path to the asset.
 */
export default function asset(path) {
    return import.meta.env.VITE_APP_DOMAIN + path;
}