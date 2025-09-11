import { useEffect } from 'react';

/**
 * Sets the document title in tab.
 * @param {string} title The title to be used. 
 */
export default function documentTitle(title) {
  useEffect(() => {
    if (!title) return;
    const prev = document.title;
    document.title = title;
    return () => { document.title = prev; };
  }, [title]);
}