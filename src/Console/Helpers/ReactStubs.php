<?php
declare(strict_types=1);
namespace Console\Console\Helpers;

class ReactStubs {
    /**
     * Stub for ProfileImageSorter.jsx component.
     *
     * @return string The contents of ProfileImageSorter.jsx file.
     */
    public function profileImageSorter(): string {
        return <<<'JSX'
        import React, { useEffect, useRef, useState } from 'react';
import $ from 'jquery';
import 'jquery-ui-dist/jquery-ui.css';
import { getCsrf } from '@chappy/utils/csrf';
import '@css/profileImage.css';
import asset from '@chappy/utils/asset';

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

    /**
     * Stub for profile/Edit.jsx page component.
     *
     * @return string The contents of the profile/Edit.jsx page component.
     */
    public static function profileEdit(): string {
        return <<<'JSX'
        import React from "react";
        import Forms from "@chappy/components/Forms";
        import route from "@chappy/utils/route";
        import ProfileImageSorter from '@/components/ProfileImageSorter';
        import documentTitle from '@chappy/utils/documentTitle';

        /**
         * Generates the edit profile component
         * @property {object} user The users model object.
         * @property {object} errors The errors generated by the users model.
         * @property {array} profileImage Array that contains object for currently 
         * selected profile image.
         * @param {InputProps} param0 
         * @returns {JSX.Element} The edit profile view component.
         */
        function Edit({user, errors, profileImages}) {
            documentTitle(`Edit Details for ${user.username}`);

            return (
                <div className="row align-items-center justify-content-center mb-5">
                    <div className="col-md-6 bg-light p-3">
                        <h1 className="text-center">Edit Details for {user.username}</h1>
                        <hr />
                        <form className="form" action="" method="post" encType="multipart/form-data">
                            <Forms.CSRF />
                            <Forms.DisplayErrors errors={errors}/>
                            <Forms.Input 
                                type="text"
                                label="First Name"
                                name="fname"
                                value={user.fname}
                                inputAttrs={{className: 'form-control input-sm'}}
                                divAttrs={{className: 'form-group mb-3'}}
                            />
                            <Forms.Input 
                                type="text"
                                label="Last Name"
                                name="lname"
                                value={user.lname}
                                inputAttrs={{className: 'form-control input-sm'}}
                                divAttrs={{className: 'form-group mb-3'}}
                            />
                            <Forms.Email 
                                label="Email"
                                name="email"
                                value={user.email}
                                inputAttrs={{className: 'form-control input-sm', placeholder: 'joe@example.com'}}
                                divAttrs={{className: 'form-group mb-3'}}
                            />
                            <Forms.RichText
                                label="Description"
                                name="description"
                                value={user.description}
                                inputAttrs={{ placeholder: 'Describe yourself here...' }}
                                divAttrs={{ className: 'form-group mb-3' }}
                            />

                            <Forms.Input 
                                type="file"
                                label="Upload Profile Image (Optional)"
                                name="profileImage"
                                value=""
                                inputAttrs={{className: 'form-control', accept: 'image/gif image/jpeg image/png'}}
                                divAttrs={{className: 'form-group mb-3'}}
                            />

                            <ProfileImageSorter initialImages={profileImages} deleteEndpoint="/profile/deleteImage" />
                            <div className="col-md-12 text-end">
                                <a href={route('profile')} className="btn btn-default">Cancel</a>
                                <Forms.SubmitTag label={"Submit"} inputAttrs={{className: 'btn btn-primary'}}/>
                            </div>
                        </form>
                    </div>
                </div>
            );
        }

        export default Edit;
        JSX;
    }

    /**
     * Stub for profile/Index.jsx page component.
     *
     * @return string The contents of the profile/Index.jsx component.
     */
    public static function profileIndex(): string {
        return <<<'JSX'
        import React from "react";
        import SafeHtml from '@chappy/components/SafeHtml.jsx';
        import asset from '@chappy/utils/asset'
        import route from "@chappy/utils/route";
        import documentTitle from "@chappy/utils/documentTitle";

        /**
         * Renders index view for profile controller.
         * @param {string} param0 Props for user and current profile image URL.
         * @returns {JSX.Element} The component for the profile index view.
         */
        function Index({ user, profileImage }) {
            documentTitle(`Profile Details for ${user.username}`);

            return (
                <>
                    <h1 className="text-center">Profile Details for {user.username}</h1>
                    <div className="col align-items-center justify-content-center mx-auto my-3 w-50">
                        {profileImage && (
                            <img src={asset(profileImage.url)}
                                className="img-thumbnail mx-auto my-5 d-block w-50 rounded border border-primary shadow-lg"
                                loading="lazy"
                            />
                        )}

                        <table className="table table-striped  table-bordered table-hover bg-light my-5">
                            <tbody>
                                <tr>
                                    <th className="text-center">First Name</th>
                                    <td className="text-center">{user.fname}</td>
                                </tr>
                                <tr>
                                    <th className="text-center">Last Name</th>
                                    <td className="text-center">{user.lname}</td>
                                </tr>
                                <tr>
                                    <th className="text-center">E-mail</th>
                                    <td className="text-center">{user.email}</td>
                                </tr>
                                <tr>
                                    <th className="text-center">ACL</th>
                                    <td className="text-center">{user.acl}</td>
                                </tr>
                                <tr>
                                    <th className="text-center" colSpan={2}>
                                        {user.description ? 'Description' : 'No description'}
                                    </th>
                                    
                                </tr>
                                {user.description && (
                                    <tr>
                                        <td id="description" className="p-4" colSpan={2}>
                                            <SafeHtml html={user.description} decode className="prose"/>
                                        </td>
                                    </tr>
                                )}
                            </tbody>
                        </table>
                        <div className="mb-5 d-flex justify-content-around">
                            <a href={route('profile.edit')} className="btn btn-info btn-sm mx-2 mb-3">
                                <i className="fa fa-edit"></i> Edit User Profile
                            </a>
                            <a href={route('profile.updatePassword')} className="btn btn-danger btn-sm mx-2 mb-3">
                                <i className="fa fa-key"></i> Update Password
                            </a>
                        </div>
                    </div>
                </>
            );
        }

        export default Index;
        JSX;
    }

    /**
     * Stub for profile/UpdatePassword.jsx page component
     *
     * @return string The contents of the profile/UpdatePassword.jsx component.
     */
    public static function profileUpdatePassword(): string {
        return <<<'JSX'
        import React from "react";
        import Forms from "@chappy/components/Forms";
        import {PasswordComplexityRequirements} from '@chappy/components/PasswordComplexityRequirements';
        import route from "@chappy/utils/route";
        import documentTitle from "@chappy/utils/documentTitle";

        /**
         * Render component for password reset view.
         * 
         * @property {object} user The user whose password to be reset.
         * @property {array} errors The errors generated by the users model.
         * @param {InputProps} param0 
         * @returns {JSX.Element} Component for password reset view.
         */
        function UpdatePassword({user, errors}) {
            documentTitle(`Change Password for ${user.username}`);

            return (
                <div className="row align-items-center justify-content-center">
                    <div className="col-md-6 bg-light p-3">
                        <h1 className="text-center">Change Password for {user.username}</h1>
                        <hr />
                        <PasswordComplexityRequirements />
                        <form className="form" action="" method="post">
                            <Forms.CSRF />
                            <Forms.DisplayErrors errors={errors}/>
                            <Forms.Input 
                                type="password"
                                label="Current Password"
                                name="current_password"
                                value=""
                                inputAttrs={{className: "form-control input-sm"}}
                                divAttrs={{className: "form-group mb-3"}}
                            />
                            <Forms.Input 
                                type="password"
                                label="Password"
                                name="password"
                                value=""
                                inputAttrs={{className: "form-control input-sm"}}
                                divAttrs={{className: "form-group mb-3"}}
                            />
                            <Forms.Input 
                                type="password"
                                label="Confirm Password"
                                name="confirm"
                                value=""
                                inputAttrs={{className: "form-control input-sm"}}
                                divAttrs={{className: "form-group mb-4"}}
                            />
                            <div className="col-md-12 text-end">
                                <a href={route("profile")} className="btn btn-default">Cancel</a>
                                <Forms.SubmitTag label="Update" inputAttrs={{className: "btn btn-primary"}} />
                            </div>
                        </form>
                    </div>
                </div>
            );
        }        
        export default UpdatePassword;
        JSX;
    }
}