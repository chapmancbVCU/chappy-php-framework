/**
 * Retrieves assets from project.
 * @param {string} path The path to the asset.
 * @param {bool} local If true use APP_DOMAIN instead of S3_BUCKET.
 * @returns The full path to the asset.
 */
export default function asset(path, local = false) {
    if(local == true) {
        return import.meta.env.VITE_APP_DOMAIN + path;    
    }
    return import.meta.env.VITE_S3_BUCKET + path;
}