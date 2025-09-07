let _csrf = null;

/**
 * Retrieves csrf token from data-csrf attribute.
 * @returns {string} Hidden element with csrf token as value.
 */
export function getCsrf() {
  if (_csrf !== null) return _csrf; // cache it
  _csrf = document.getElementById('app')?.getAttribute('data-csrf') || '';
  return _csrf;
}