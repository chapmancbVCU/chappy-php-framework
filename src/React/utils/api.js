/**
 * 
 * @param {*} path 
 * @param {*} param1 
 * @returns 
 */
export async function apiGet(path, { query } = {}) {
    const url = new URL(path, window.location.origin);

    if(query) {
        url.search = new URLSearchParams(query).toString();
    }

    const response = await fetch(url, {
        credentials: 'same-origin',
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    });;

    const json = await response.json().catch(() => ({}));

    if(!response.ok || json?.success === false) {
        const error = new Error(json?.message || response.statusText);
        error.status = response.status;
        error.data = json;
        throw error;
    }
    return json;
}