<?php
declare(strict_types=1);
namespace Console\Helpers;

/**
 * A collection of React.js view, component, and util stubs.
 */
class ReactStubs {
    /**
     * Generates a component with default export.
     *
     * @param string $componentName The name of the component.
     * @return string The contents of the component.
     */
    public static function defaultComponentTemplate(string $componentName): string {
        return <<<JSX
import React from "react";
function {$componentName}() {

    return (
        <>
        
        </>
    );
}        
export default {$componentName};
JSX;
    }

    /**
     * Stub for restoring required error/NotFound.jsx page component if 
     * accidentally deleted.
     *
     * @return string Contents of the error/NotFound.jsx page component.
     */
    public static function errorNotFound(): string {
        return <<<'JSX'
import React from "react";

/**
 * Renders error page with "The view was not found message".
 * @returns {JSX.Element} H1 element with "The view was not found" message.
 */
export default function NotFound() {
    return <h1 className="text-center">The view was not found</h1>
}
JSX;
    }

    /**
     * Returns contents for a new hook file.
     *
     * @param string $hookName The name of the hook.
     * @return string The contents of the new hook file.
     */
    public static function hookTemplate($hookName): string {
        return <<<JS
const use{$hookName} = () => {

    return {  }
}

export default use{$hookName};
JS;
    }

    /**
     * Generates a named export component.
     *
     * @param string $componentName The name of the component.
     * @return string The contents of the component.
     */
    public static function namedComponentTemplate(string $componentName): string {
        return <<<JSX
import React from "react";
export const {$componentName} = () => {

    return (
        <>
        
        </>
    );
}
JSX;
    }
    /**
     * Stub for ProfileImageSorter.jsx component.
     *
     * @return string The contents of ProfileImageSorter.jsx file.
     */
    public static function profileImageSorter(): string {
        return <<<'JSX'
import React, { useEffect, useRef, useState } from 'react';
import $ from 'jquery';
import 'jquery-ui-dist/jquery-ui.css';
import '@css/profileImage.css';
import { asset, getCsrf } from '@chappy/utils';

/**
 * A single profile image row used by the sorter.
 * @typedef {Object} ProfileImage
 * @property {number|string} id     - Unique image identifier.
 * @property {string}        url    - Public URL for the image.
 * @property {number}       [sort]  - Optional sort index (0 = current profile image).
 */

/**
 * Props for {@link ProfileImageSorter}.
 * @typedef {Object} ProfileImageSorterProps
 * @property {ProfileImage[]} [initialImages=[]]
 *   Initial list of images to display and sort.
 * @property {string} [deleteEndpoint='/profile/deleteImage']
 *   Endpoint that deletes an image. Expects `POST image_id, csrf_token`
 *   and returns JSON `{ success: boolean, model_id?: string|number }`.
 */

/**
 * Drag-to-reorder gallery with a hidden `<input name="images_sorted">` that
 * mirrors the order in a PHP-friendly format (e.g. `["image_1","image_9",...]`).
 *
 * - Uses jQuery UI `sortable` (horizontal).
 * - On delete, POSTs `image_id` + CSRF, removes from UI on success, and refreshes order.
 *
 * @param {ProfileImageSorterProps} props
 * @returns {JSX.Element}
 */
export default function ProfileImageSorter({
    initialImages = [],
    deleteEndpoint = '/profile/deleteImage',
}) {
    /** 
     * The working list of images shown in the UI. 
     */
    const [images, setImages] = useState(initialImages);

    /** 
     * Root element that receives the jQuery UI `sortable()` binding. 
     */
    const listRef = useRef(null);

    /** 
     * Hidden input that carries the serialized order for the PHP controller. 
     */
    const sortedRef = useRef(null);

    /**
     * Initialize / re-initialize sortable whenever the number of items changes.
     * Cleans up the jQuery UI instance on unmount.
     */
    useEffect(() => {
        const el = listRef.current;
        if (!el) return;

        window.$ = window.jQuery = $;
        let destroyed = false;

        (async () => {
            await import('jquery-ui-dist/jquery-ui');

            if (destroyed) return;

            const $el = $(el);
            if ($el.data('ui-sortable')) {
                $el.sortable('refresh');
            } else {
                $el.sortable({
                axis: 'x',
                placeholder: 'sortable-placeholder',
                update: updateHidden,
                });
            }

            // Seed initial hidden value
            updateHidden();
        })();

        return () => {
            destroyed = true;
            try { $(el).sortable('destroy'); } catch {}
        };
    }, [images.length]);

    /**
     * Compute the current DOM order and write it into the hidden field as JSON.
     * The IDs follow the original PHP pattern: container IDs like "image_<id>".
     * @returns {void}
     */
    function updateHidden() {
        const arr = $(listRef.current).sortable('toArray');
        if (sortedRef.current) sortedRef.current.value = JSON.stringify(arr);
    }

    /**
     * Delete an image from the server and update local state if successful.
     *
     * @param {number|string} id - The image ID to remove.
     * @returns {Promise<void>}
     */
    async function handleDelete(id) {
        if (!confirm('Are you sure? This cannot be undone!')) return;
        const fd = new FormData();
        fd.append('image_id', id);
        fd.append('csrf_token', getCsrf());

        const res = await fetch(deleteEndpoint, { method: 'POST', body: fd, credentials: 'same-origin' });
        let data = null; 
        try { 
            data = await res.json(); 
        } catch {}

        if (data?.success) {
            // Remove from UI, `useEffect` will refresh hidden order
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
JSX;
    }
}