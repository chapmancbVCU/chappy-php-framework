import React, { useEffect, useRef, useState } from 'react';
import $ from 'jquery';
import 'jquery-ui-dist/jquery-ui.css'; // styles only (static import)
import { getCsrf } from '@chappy/utils/csrf';
import '@css/profileImage.css';
import asset from '@chappy/utils/asset'
/**
 * Displays and handles deletion of images.
 * @property {object} initialImages The initial collection of images.
 * @property {object} deleteEndpoint Controller and action for deleting images.
 * @param {InputProps} param0 
 * @returns 
 */
export default function ProfileImageSorter({
    initialImages = [],
    deleteEndpoint = '/profile/deleteImage',
}) {
    const [images, setImages] = useState(initialImages);
    const listRef = useRef(null);
    const sortedRef = useRef(null);

    useEffect(() => {
        const el = listRef.current;
        if (!el) return;

        // expose the SAME jQuery instance globally
        window.$ = window.jQuery = $;

        let destroyed = false;
        (async () => {
            // ✅ jQuery-UI runs AFTER jQuery is global
            await import('jquery-ui-dist/jquery-ui');

            if (destroyed) return;

            const $el = $(el);
            // Guard against double init (React StrictMode/HMR)
            if ($el.data('ui-sortable')) {
                $el.sortable('refresh');
            } else {
                $el.sortable({
                axis: 'x',
                placeholder: 'sortable-placeholder',
                update: updateHidden,
                });
            }
            updateHidden();
        })();

        return () => {
            destroyed = true;
            try { $(el).sortable('destroy'); } catch {}
        };
    }, [images.length]); // rebind if item count changes

    function updateHidden() {
        const arr = $(listRef.current).sortable('toArray');
        if (sortedRef.current) sortedRef.current.value = JSON.stringify(arr);
    }

    async function handleDelete(id) {
        if (!confirm('Are you sure? This cannot be undone!')) return;
        const fd = new FormData();
        fd.append('image_id', id);
        fd.append('csrf_token', getCsrf());

        const res = await fetch(deleteEndpoint, { method: 'POST', body: fd, credentials: 'same-origin' });
        let data = null; try { data = await res.json(); } catch {}
        if (data?.success) {
            setImages(prev => prev.filter(img => img.id !== id));
            window.alert?.('Image Deleted.');
        }
    }


    return (
        <>
            <input ref={sortedRef} type="hidden" name="images_sorted" />
            <div id="sortableImages" className="row align-items-center justify-content-start p-2" ref={listRef}>
                {images.map(img => (
                <div key={img.id} className="col flex-grow-0" id={`image_${img.id}`}>
                    <button type="button" className="btn btn-danger btn-sm mb-2" onClick={() => handleDelete(img.id)}>
                    <i className="fa fa-times" />
                    </button>
                    <div className={`edit-image-wrapper ${img.sort === 0 ? 'current-profile-img' : ''}`} data-id={img.id}>
                    <img src={asset(img.url)} alt="" />
                    </div>
                </div>
                ))}
            </div>
        </>
    );
}