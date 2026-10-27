<?php
declare(strict_types=1);
namespace Console\Helpers\Stubs\AdminDashboard;

/**
 * Contains stubs for admin dashboard.
 */
class Views {
    /**
     * Generates the React.js version of the attachment details view.
     *
     * @return string The view's contents.
     */
    public static function makeAttachmentDetailsJS(): string {
        return <<<JSX
import React from "react";
import SafeHtml from '@chappy/components/SafeHtml';
import { DateTime, route, documentTitle } from '@chappy/utils';

/**
 * Renders attachment details view.
 * 
 * @property {object} attachment The attachment object
 * @property {string} size The size of the file.
 * @property {object} uploader The upload of the attachment.
 * @param {*} param0 
 * @returns 
 */
function AttachmentDetails({ attachment, size, uploader }) {
    documentTitle("Details for " + attachment.attachment_name);
    return (
        <div className="row align-items-center justify-content-center">
            <div className="col-md-6 bg-light p-3">
                <a href={route('admindashboard.Attachments')}  className="btn btn-sm btn-secondary">Back</a>
                <h2 className="text-center">{attachment.attachment_name}</h2>
                <div className="row">
                    <div className="col-md-6">
                        <p title={DateTime.formatTime(attachment.created_at, DateTime.FORMAT_HUMAN_READABLE)}>
                            <strong>Created at: </strong>
                            {DateTime.timeAgo(attachment.created_at)}
                        </p>
                        <p title={DateTime.formatTime(attachment.updated_at, DateTime.FORMAT_HUMAN_READABLE)}>
                            <strong>Updated at: </strong>
                            {DateTime.timeAgo(attachment.updated_at)}
                        </p>
                        <p><strong>Size: </strong>{size}</p>
                        <p><strong>MIME Type: </strong>{attachment.mime_type}</p>
                        <p><strong>Uploader: </strong>{uploader.username}</p>
                        <div>
                            <strong>Description: </strong>
                            <SafeHtml html={attachment.description} decode className="prose"/>
                        </div>
                        <p>
                            <strong>Preview: </strong>
                            <a href={route('admindashboard.preview', [attachment.id])} target="_blank">
                                {attachment.attachment_name}    
                            </a> 
                        </p>
                    </div>
                </div>
            </div>
        </div>
    );
}        
export default AttachmentDetails;
JSX;
    }

    /**
     * Generates the PHP version of the attachment details view.
     *
     * @return string The view's contents.
     */
    public static function makeAttachmentDetailsPHP(): string {
        return <<<PHP
<?php use Core\Models\EmailAttachments; ?>
<?php \$this->setSiteTitle("Details for " . \$this->attachment->attachment_name); ?>

<!-- Head content between these two function calls.  Remove if not needed. -->
<?php \$this->start('head'); ?>

<?php \$this->end(); ?>


<!-- Body content between these two function calls. -->
<?php \$this->start('body'); ?>
<div class="row align-items-center justify-content-center">
    <div class="col-md-6 bg-light p-3">
        <a href="<?=route('admindashboard.attachments')?>" class="btn btn-sm btn-secondary">Back</a>
        <h2 class="text-center"><?=\$this->attachment->attachment_name?></h2>
        <div class="row">
            <div class="col-md-6">
                <p><strong>Created at: </strong><?= \$this->attachment->date('created_at')->diffForHumans() ?></p>
                <p><strong>Updated at: </strong><?= \$this->attachment->date('updated_at')->diffForHumans() ?></p>
                <p><strong>Size: </strong><?= EmailAttachments::formatBytes(\$this->attachment->size) ?></p>
                <p><strong>MIME Type: </strong><?= \$this->attachment->mime_type ?></p>
                <p><strong>Uploader: </strong><?= \$this->uploader->username ?></p>
                <p><strong>Description: </strong><?= htmlspecialchars_decode(stripslashes(\$this->attachment->description)) ?></p>
                <p><strong>Preview: </strong><a href="<?= route('admindashboard.preview', [\$this->attachment->id]) ?>" target="_blank"><?= \$this->attachment->attachment_name ?></a></p>
            </div>
        </div>
    </div>
</div>

<div class="widget-container">
    <?= \$this->renderWidgets('dashboard.attachments', \$this->widgets) ?>
</div>

<?php \$this->end(); ?>
PHP;
    }

    /**
     * Generates the React.js version of the attachments form view.
     *
     * @return string The view's contents.
     */
    public static function makeAttachmentsFormJS(): string {
        return <<<JSX
import React from "react";
import { 
    CSRFInput, 
    documentTitle, 
    DisplayErrors,
    FileSelector,
    RichText,
    route,
    SubmitTag
} from "@chappy/utils";

/**
 * The attachments form component.
 * 
 * @property {object} attachment The attachment object.
 * @property {object} errors The errors object.
 * @property {number|string} id The id of an existing record or the string "new" 
 * to tell the view we are uploading a file.
 * @param {*} param0 
 * @returns 
 */
function AttachmentsForm({ attachment, errors, id }) {
    const isNew = (id === 'new') ? true : false;
    const header = isNew ? "Add Attachment" : "Edit Attachment";
    attachment.description = (isNew) ? '' : attachment.description;
    documentTitle(header);

    return (
        <>
            <h1 className="text-center mb-4">{header}</h1>
            <div className="row align-items-center justify-content-center">
                <div className="col-md-6 bg-light p-3">
                    <form className="form" action="" method="POST" encType="multipart/form-data">
                        <DisplayErrors errors={errors}/>
                        <CSRFInput />
                        <RichText 
                            label="Description"
                            name="description"
                            value={attachment.description}
                            inputAttrs={{className: 'form-control input-sm'}}
                            divAttrs={{className: 'form-group mb-3'}}
                        />
                        {isNew && (
                            <FileSelector 
                                label="Upload"
                                name="attachment_name"
                                inputAttrs={{className: 'form-control', accept: 'image/giv image/jpeg image/png'}}
                                outputAttrs={{className: 'form-group'}}
                            />
                        )}
                        <div className="col-md-12 text-end mt-3">
                            <a href={route('admindashboard.Attachments')} className="btn btn-default">Cancel</a>
                            <SubmitTag label="Save" inputAttrs={{className: 'btn btn-primary'}} />
                        </div>
                    </form>
                </div>
            </div>
        </>
    );
}        
export default AttachmentsForm;
JSX;
    }

    /**
     * Generates the PHP version of the attachments form view.
     *
     * @return string The view's contents.
     */
    public static function makeAttachmentsFormPHP(): string {
        return <<<PHP
<?php \$this->setSiteTitle(\$this->header); ?>

<!-- Head content between these two function calls.  Remove if not needed. -->
<?php \$this->start('head'); ?>
<?= loadTinyMCE() ?>
<?php \$this->end(); ?>


<!-- Body content between these two function calls. -->
<?php \$this->start('body'); ?>
<h1 class="text-center mb-4"><?= \$this->header ?></h1>
<div class="row align-items-center justify-content-center">
    <div class="col-md-6 bg-light p-3">
        <form class="form" action="" method="POST" enctype="multipart/form-data">
            <?= errorBag(\$this->errors) ?>
            <?= csrf() ?>
            <?= textarea(
                "Description",
                "description",
                \$this->attachment->description,
                ['class' => 'form-control input-sm'], 
                ['class' => 'form-group mb-3']
            ) ?>

            <?php if(\$this->attachment->isNew()): ?>
                <?= fileSelector(
                    \$this->uploadMessage, 
                    'attachment_name', 
                    ['class' => 'form-control', 'accept' => 'image/gif image/jpeg image/png'], 
                    ['class' => 'form-group mb-3']
                ) ?>
            <?php endif; ?>
            <div class="col-md-12 text-end">
                <a href="<?=route('admindashboard.attachments')?>" class="btn btn-default">Cancel</a>
                <?= submit('Save', ['class' => 'btn btn-primary']) ?>
            </div>
        </form>
    </div>
</div>

<!-- Wait until content is loaded before we initialize script -->
<script>
    initializeTinyMCE('description');
</script>
<?php \$this->end(); ?>
PHP;
    }

    /**
     * Generates the React.js version of the attachments view.
     *
     * @return string The view's contents.
     */
    public static function makeAttachmentsJS(): string {
        return <<<JSX
import React, { useState } from "react";
import { documentTitle, route, CSRFInput, CSRFToken, apiError, apiDelete } from "@chappy/utils";

/**
 * Renders list of all attachments that have been uploaded.
 * 
 * @property {array} attachments An array of attachments available
 * @param {*} param0 
 * @returns {HTMLDivElement} View for listing and managing Email Attachments.
 */
function Attachments({ attachments }) {
    documentTitle("Attachments");

    /**
     * Manages state for error.  Errors are generated when there is a failure 
     * deleting an Attachments record.
     */
    const [error, setError] = useState(null);

    /**
     * Creates a click handler that deletes an Attachment after the user confirms.
     *
     * On confirmation, sends a DELETE request with the CSRF token to
     * `/admindashboard/deleteAttachment/{id}` and reloads the page so the list and any
     * flash message reflect the change. Errors are displayed with `setError`.
     *
     * @example
     * <button onClick={onDeleteClick(acl)}>Delete</button>
     *
     * @param {{ id: number, attachment: string }} attachment The Attachment to delete.
     * @returns {(event: React.MouseEvent) => Promise<void>} Click handler for the delete button.
     */
    const onDeleteClick = (attachment) => async (event) => {
        event.preventDefault();
        if(window.confirm(`Are you sure you want to delete the attachment: \${attachment.name}`)) {
            try {
                const payload = {
                    csrf_token: CSRFToken(event)
                }
                const json = await apiDelete(`/admindashboard/deleteAttachment/\${attachment.id}`, payload);
                window.location.reload();
            } catch(err) {
                setError(apiError(err));
            }
        }
    }

    return (
        <>
            <h1 className="text-center mb-4">Attachments
                <a href={route('admindashboard.editAttachments', ['new'])} className="btn btn-primary btn-sm me-5">
                    <i className="fa fa-plus"></i> Add Attachment
                </a>
            </h1>
            <div className="d-flex justify-content-center">
                <table className="w-75 table table-striped table-bordered table-hover table-sm">
                    <thead>
                        <tr>
                            <th>Original Name</th>
                            <th>Uploader</th>
                            <th>size</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        {attachments.map((attachment) => (
                            <tr key={attachment.id}>
                                <td>
                                    <a href={route('admindashboard.attachmentDetails', [attachment.id])}>
                                        {attachment.attachment_name}
                                    </a>
                                </td>
                                <td>{attachment.uploadUsername}</td>
                                <td>{attachment.formattedBytes}</td>
                                <td className="text-end">
                                    <a href={route('admindashboard.editAttachments', [attachment.id])}
                                        className="btn btn-secondary btn-sm"><i className="fas fa-edit"></i> Edit
                                    </a>
                                    {error ? (<p className="text-danger">{error}</p>) : 
                                        <form method="POST" onSubmit={onDeleteClick(attachment)} className="d-inline-block">
                                            <CSRFInput />
                                            <button type="submit" className="btn btn-danger btn-sm">
                                                <i className="fa fa-trash"></i> Delete
                                            </button>
                                        </form>
                                    }
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
        </>
    );
}        
export default Attachments;
JSX;
    }

    /**
     * Generates the PHP version of the attachments view.
     *
     * @return string The view's contents.
     */
    public static function makeAttachmentsPHP(): string {
        return <<<PHP
<?php use Core\Models\EmailAttachments; ?>
<?php \$this->setSiteTitle("Attachments"); ?>

<!-- Head content between these two function calls.  Remove if not needed. -->
<?php \$this->start('head'); ?>

<?php \$this->end(); ?>


<!-- Body content between these two function calls. -->
<?php \$this->start('body'); ?>
<h1 class="text-center mb-4">Attachments
    <a href="<?=route('admindashboard.editAttachments', ['new'])?>" class="btn btn-primary btn-sm me-5">
        <i class="fa fa-plus"></i> Add Attachment
    </a>
</h1>
<div class="d-flex justify-content-center">
    <table class="w-75 table table-striped table-bordered table-hover table-sm">
        <thead>
            <th>Original Name</th>
            <th>Uploader</th>
            <th>Size</th>
            <th></th>
        </thead>
        <tbody>
            <?php foreach(\$this->attachments as \$attachment): ?>
                <tr>
                    <td>
                        <a href="<?=route('admindashboard.attachmentDetails', [\$attachment->id])?>"><?=\$attachment->attachment_name?></a>
                    </td>
                    <td><?=EmailAttachments::uploadUsername(\$attachment->user_id)?></td>
                    <td><?=EmailAttachments::formatBytes(\$attachment->size)?></td>
                    <td class="text-end">
                        <a href="<?=route('admindashboard.editAttachments', [\$attachment->id])?>"
                            class="btn btn-secondary btn-sm"><i class="fas fa-edit"></i> Edit
                        </a>
                        <form method="POST"
                            action="<?=route('admindashboard.deleteAttachment', [\$attachment->id])?>"
                            class="d-inline-block"
                            onsubmit="return confirm('Are you sure you want to delete this attachment?');">
                            <?=csrf()?>
                            <button type="submit" class="btn btn-danger btn-sm">
                                <i class="fa fa-trash"></i> Delete
                            </button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php \$this->end(); ?>
PHP;
    }

    /**
     * Generates the React.js version of the details view.
     *
     * @return string The view's contents.
     */
    public static function makeDetailsJS(): string {
        return <<<JSX
import React, { useState } from "react";
import { 
    apiDelete, 
    apiError, 
    asset, 
    CSRFInput, 
    CSRFToken,
    DateTime, 
    documentTitle, 
    redirect, 
    route, 
    SafeHtml 
} from "@chappy/utils";

/**
 * Returns user details component.
 * 
 * @property {object} profileImage The profileImage object.
 * @property {object} user The user whose details wer are viewing.
 * @param {*} param0 
 * @returns 
 */
function Details({ profileImage, user }) {
    documentTitle(`\${user.username} Details`);
    const [error, setError] = useState(null);
 
    async function onDeleteClick(e) {
        e.preventDefault();
        if(window.confirm(`Are you sure you want to delete the following user? \${user.username}`)) {
            try {
                const payload = {
                    csrf_token: CSRFToken(e)
                }
                const json = await apiDelete(`/admindashboard/delete/\${user.id}`, payload);

                // Use route stored in json to redirect.
                redirect(json.redirect);
            } catch (err) {
                setError(apiError(err));
            }
        }
    }

    return (
        <>
            <h1 className="text-center">Details for {user.username}</h1>
            <div className="align-items-center justify-content-center mx-auto my-3 w-50">
                <a href={route('admindashboard.index')} className="btn btn-sm btn-secondary mb-3">Back</a>
                {profileImage &&
                    <img src={asset(profileImage.url)}
                        className="img-thumbnail mx-auto my-5 d-block w-50 rounded border border-primary shadow-lg">
                    </img>
                }
            </div>

            <div className="row align-items-center justify-content-center mx-auto my-3 w-50">
                <div className="col">
                    <table className="table table-striped table-bordered table-hover bg-light my-5 col-md-12">
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
                                <th className="text-center">Status</th>
                                {user.inactive == 0 ? (
                                    <td className="text-center">Active</td>
                                ) : (
                                    <td className="text-center">Inactive</td>
                                )}
                            </tr>
                            <tr>
                                <th className="text-center">ACL</th>
                                <td className="text-center">{user.acl}</td>
                            </tr>
                            <tr>
                                <th className="text-center">Login Attempts</th>
                                <td className="text-center">{user.login_attempts}</td>
                            </tr>
                            <tr>
                                <th className="text-center">Password Reset</th>
                                {user.reset_password == 0 ? (
                                    <td className="text-center">Off</td>
                                ) : (
                                    <td className="text-center">On</td>
                                )}
                            </tr>
                            <tr>
                                <th className="text-center">Created</th>
                                <td title={DateTime.formatTime(user.created_at, DateTime.FORMAT_HUMAN_READABLE)} className="text-center">
                                    {DateTime.timeAgo(user.created_at)}
                                </td>
                            </tr>
                            <tr>
                                <th className="text-center">Last Update</th>
                                <td title={DateTime.formatTime(user.updated_at, DateTime.FORMAT_HUMAN_READABLE)} className="text-center">
                                    {DateTime.timeAgo(user.updated_at)}
                                </td>
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
                </div>
                <div className="col col-md-4 mb-5">
                    <a href={route('admindashboard.edit', [user.id])} className="btn btn-info btn-sm mb-2 w-100 text-left">
                        <i className="fa fa-edit"></i> Edit User Profile
                    </a>
                    <a href={route('admindashboard.setResetPassword', [user.id])} className="btn btn-warning btn-sm my-2 w-100 text-left">
                        <i className="fa fa-key"></i> Reset Password
                    </a>
                    <a href={route('admindashboard.setStatus', [user.id])} className="btn btn-primary btn-sm mb-5 mt-2 w-100 text-left">
                        <i className="fa fa-unlock"></i> Set Account Status
                    </a>
                    {error ? (<p className="text-danger">{error}</p>) : 
                        <form method="POST" onSubmit={onDeleteClick}>
                            <CSRFInput />
                            <button type="submit" className="btn btn-danger btn-sm mt-5 w-100 text-left">
                                <i className="fa fa-trash"></i> Delete
                            </button>
                        </form>
                    }
                </div>
            </div>
        </>
    );
}        
export default Details;
JSX;
    }

    /**
     * Generates the PHP version of the details view.
     *
     * @return string The view's contents.
     */
    public static function makeDetailsPHP(): string {
        return <<<PHP
<?php \$this->setSiteTitle(\$this->user->username . " Details"); ?>
<?php \$this->start('body'); ?>
<h1 class="text-center">Details for <?=\$this->user->username?></h1>

<div class="align-items-center justify-content-center mx-auto my-3 w-50">
    <a href="<?=route('admindashboard.index')?>" class="btn btn-sm btn-secondary mb-3">Back</a>
    <?php if(\$this->profileImage != null):?>
        <img src="<?=asset(\$this->profileImage->url)?>"
            class="img-thumbnail mx-auto my-5 d-block w-50 rounded border border-primary shadow-lg">
        </img>
    <?php endif; ?>
</div>
<div class="row align-items-center justify-content-center mx-auto my-3 w-50">
    <div class="col">
        <table class="table table-striped table-bordered table-hover bg-light my-5 col-md-12">
            <tbody>
                <tr>
                    <th class="text-center">First Name</th>
                    <td class="text-center"><?=\$this->user->fname?></td>
                </tr>
                <tr>
                    <th class="text-center">Last Name</th>
                    <td class="text-center"><?=\$this->user->lname?></td>
                </tr>
                <tr>
                    <th class="text-center">E-mail</th>
                    <td class="text-center"><?=\$this->user->email?></td>
                </tr>
                <tr>
                    <th class="text-center">Status</th>
                    <td class="text-center"><?= (\$this->user->inactive == 0) ? 'Active' : 'Inactive'?></td>
                </tr>
                <tr>
                    <th class="text-center">ACL</th>
                    <td class="text-center"><?=\$this->user->acl?></td>
                </tr>
                <tr>
                    <th class="text-center">Login Attempts</th>
                    <td class="text-center"><?=\$this->user->login_attempts?></td>
                </tr>
                <tr>
                    <th class="text-center">Password Reset</th>
                    <td class="text-center"><?=(\$this->user->reset_password == 0) ? 'Off' : 'On'?></td>
                </tr>
                <tr>
                    <th class="text-center">Created</th>
                    <td class="text-center"><?= \$this->user->date('created_at')->diffForHumans() ?></td>
                </tr>
                <tr>
                    <th class="text-center">Last Update</th>
                    <td class="text-center"><?= \$this->user->date('updated_at')->diffForHumans() ?></td>
                </tr>
                <?php if(\$this->user->description): ?>
                    <tr>
                        <th class="text-center" colspan="2">Description</th>
                    </tr>
                    <tr>
                        <td class="p-4" colspan="2"><?=htmlspecialchars_decode(stripslashes(\$this->user->description));?></td>
                    </tr>
                <?php else: ?>
                    <tr>
                        <td class="text-center" colspan="2">No description</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <div class="col col-md-4 mb-5">
        <a href="<?=route('admindashboard.edit', [\$this->user->id]) ?>" class="btn btn-info btn-sm mb-2 w-100 text-left">
            <i class="fa fa-edit"></i> Edit User Profile
        </a>
        <a href="<?=route('admindashboard.setResetPassword', [\$this->user->id])?>" class="btn btn-warning btn-sm my-2 w-100 text-left">
            <i class="fa fa-key"></i> Reset Password
        </a>
        <a href="<?=route('admindashboard.setStatus', [\$this->user->id])?>" class="btn btn-primary btn-sm mb-5 mt-2 w-100 text-left">
            <i class="fa fa-unlock"></i> Set Account Status
        </a>
        <form method="POST"
            action="<?=route('admindashboard.delete', [\$this->user->id])?>"
            onsubmit="return confirm('Are you sure you want to delete the following user? <?=\$this->user->username?>')">
            <?= csrf() ?>
            <button type="submit" class="btn btn-danger btn-sm mt-5 w-100 text-left">
                <i class="fa fa-trash"></i> Delete
            </button>
        </form>
    </div>
</div>

<div class="widget-container">
    <?= \$this->renderWidgets('dashboard.details', \$this->widgets) ?>
</div>
<?php \$this->end(); ?>
PHP;
    }

    /**
     * Generates the React.js version of the edit acl view.
     *
     * @return string The view's contents.
     */
    public static function makeEditAclJS(): string {
        return <<<JSX
import { CSRFInput, documentTitle, route, SubmitTag, Text } from "@chappy/utils";
import React from "react";

/**
 * Renders form for adding/editing an ACL.
 * 
 * @property {object} acl The acl object.
 * @property {object} errors The errors object
 * @property {number|string} id The id of an existing record or the string "new" 
 * to tell the view we are creating a new record.
 * @param {} param0 
 * @returns 
 */
function EditACL({ acl, errors, id }) {
    const isNew = (id === 'new') ? true : false;
    const header = isNew ? "Add ACL" : "Edit ACL";
    documentTitle(header);

    return (
        <>
            <h1 className="text-center">{header}</h1>
            <div className="row align-items-center justify-content-center">
                <div className="col-md-6 bg-light p-3">
                    <form className="form" action="" method="POST">
                        <CSRFInput />
                        <Text 
                            label="ACL"
                            name="acl"
                            value={acl.acl}
                            inputAttrs={{className: 'form-control input-sm'}}
                            divAttrs={{className: 'form-group'}}
                            errors={errors}
                        />
                        <div className="col-md-12 text-end pt-3">
                            <a href={route('admindashboard.manageAcls')} className="btn btn-default">Cancel</a>
                            <SubmitTag label="Save" inputAttrs={{className: 'btn btn-primary'}} />
                        </div>
                    </form>
                </div>
            </div>
        </>
    );
}        
export default EditACL;
JSX;
    }

    /**
     * Generates the PHP version of the edit acl view.
     *
     * @return string The view's contents.
     */
    public static function makeEditAclPHP(): string {
        return <<<PHP
<?php \$this->setSiteTitle(\$this->header); ?>
<?php \$this->start('body'); ?>
<h1 class="text-center"><?= \$this->header ?></h1>

<div class="row align-items-center justify-content-center">
    <div class="col-md-6 bg-light p-3">
        <form class="form" action="" method="post">
            <?= csrf() ?>
            <?= errorBag(\$this->displayErrors) ?>
            <?= text("ACL", 'acl', \$this->acl->acl, ['class' => 'form-control input-sm'], ['class' => 'form-group'], \$this->displayErrors) ?>
            <div class="col-md-12 text-end pt-3">
                <a href="<?=route('admindashboard.manageACLs')?>" class="btn btn-default">Cancel</a>
                <?= submit('Save',['class'=>'btn btn-primary']) ?>
            </div>
        </form>
    </div>
</div>

<?php \$this->end(); ?>
PHP;
    }

    /**
     * Generates the React.js version of the edit view.
     *
     * @return string The view's contents.
     */
    public static function makeEditJS(): string {
        return <<<JSX
import React from "react";
import { documentTitle, ProfileImageSorter, route } from '@chappy/utils';
import * as Forms from '@chappy/components/Forms'

/**
 * Renders edit view for administrators.
 * 
 * @property {object} acls The acls for the form.
 * @property {object} errors The errors.
 * @property {object} profileImages The user's profile images.
 * @property {object} user The user to be modified.
 * @property {array} userAcls An array of user assigned acls.
 * @param {*} param0 
 * @returns 
 */
function Edit({ acls, errors, profileImages, user, userAcls }) {
    documentTitle(`Edit Details for \${user.username}`);

    return (
        <div className="row align-items-center justify-content-center">
            <div className="col-md-6 bg-light p-3">
                <h1 className="text-center">Edit Details for {user.username}</h1>
                <hr />
                <form className="form" action="" method="post">
                    <Forms.CSRFInput />
                    <Forms.DisplayErrors errors={errors}/>

                    <Forms.Text
                        label="First Name"
                        name="fname"
                        value={user.fname}
                        inputAttrs={{className: 'form-control input-sm'}}
                        divAttrs={{className: 'form-group mb-3'}}
                    />
                    <Forms.Text 
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
                    <div className="form-group mb-3">
                        <label>Manage ACLs:</label>
                        <Forms.CheckboxGroup 
                            name='acls'
                            options={acls}
                            selectedValues={userAcls}
                            divAttrs={{className: 'form-check'}}
                        />
                    </div>
                    <ProfileImageSorter initialImages={profileImages} deleteEndpoint="/profile/deleteImage" />
                    <div className="col-md-12 text-end">
                        <a href={route('admindashboard.details', [user.id])} className="btn btn-default">Cancel</a>
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
     * Generates the PHP version of the edit view.
     *
     * @return string The view's contents.
     */
    public static function makeEditPHP(): string {
        return <<<PHP
<?php \$this->setSiteTitle("Edit Details for ".\$this->user->username); ?>
<?php \$this->start('head') ?>
<?= profileImageSort() ?>
<?= loadTinyMCE() ?>
<?php \$this->end() ?>

<?php \$this->start('body'); ?>
<div class="row align-items-center justify-content-center">
    <div class="col-md-6 bg-light p-3">
        <h1 class="text-center">Edit Details for <?=\$this->user->username?></h1>
        <hr>
        <form class="form" action=<?=\$this->postAction?> method="post">
            <?= csrf() ?>
            <?= hidden('images_sorted', '') ?>
            <?= errorBag(\$this->displayErrors) ?>
            
            <!-- Primary profile details -->
            <?= \$this->component('edit_profile_details', true); ?>

            <!-- ACL Management Section -->
            <div class="form-group mb-3">
                <label>Manage ACLs:</label>
                <?= checkboxGroup(
                    'acls',
                    \$this->acls,              // [value => label] map of all ACLs
                    \$this->user->getAcls(),   // the set of ACLs this user currently has
                    [],
                    ['class' => 'form-check'],
                    \$this->displayErrors
                ); ?>
            </div>
            
            <!-- Manage profile images section -->
            <?= \$this->component('manage_profile_images', true); ?>

            <div class="col-md-12 text-end">
                <a href="<?=route('admindashboard.details', [\$this->user->id])?>" class="btn btn-default">Cancel</a>
                <?= submit('Update', ['class' => 'btn btn-primary'])  ?>
            </div>
        </form>
    </div>
</div>

<!-- Wait until content is loaded before we initialize script -->
<script>
    initializeTinyMCE('description');
</script>

<?php \$this->end(); ?>
PHP;
    }

    /**
     * Generates the React.js version of the index view.
     *
     * @return string The view's contents.
     */
    public static function makeIndexJS(): string {
        return <<<JSX
import React, { useEffect, useState } from 'react';
import { DateTime, documentTitle, route } from '@chappy/utils';
import usePaginatedQuery from '@chappy/utils/usePaginatedQuery';
import Pagination, { PaginationSummary, SortableTh } from '@chappy/components/Pagination';
import SafeHtml from '@chappy/components/SafeHtml';

/**
 * Admin dashboard: searchable, filterable, sortable, paginated user list.
 *
 * @param {object} props
 * @param {object[]} props.users Current page of users (DashboardService::userToArray()).
 * @param {object} props.pagination Pagination::toArray().
 * @param {object} props.filter SearchFilter::toArray().
 * @param {{value: string, label: string}[]} props.aclOptions Access level options.
 * @param {{value: string, label: string}[]} props.statusOptions Status options.
 * @param {string} [props.widgetsHtml] Server-rendered dashboard widgets.
 * @returns {JSX.Element}
 */
export default function Index({
    users = [],
    pagination,
    filter,
    aclOptions = [],
    statusOptions = [],
    widgetsHtml = '',
}) {
    documentTitle('Administration');

    const { data, params, loading, error, update, goToPage, reset } =
        usePaginatedQuery({ users, pagination, filter });

    const searchParam = data.filter?.params?.search ?? 'q';
    const filters = data.filter?.filters ?? {};

    // Search box is local until submitted; resync on back/forward or Clear.
    const [term, setTerm] = useState(params[searchParam] ?? '');
    useEffect(() => setTerm(params[searchParam] ?? ''), [params[searchParam]]);

    function onSearch(e) {
        e.preventDefault();
        update({ [searchParam]: term.trim() });
    }

    function onSort(column, dir) {
        const { sort, dir: dirParam } = data.filter?.params ?? { sort: 'sort', dir: 'dir' };
        update({ [sort]: column, [dirParam]: dir });
    }

    const showClear = data.filter?.active || Object.keys(params).length > 0;

    return (
        <>
            <h1 className="text-center">Administration Dashboard</h1>

            <form className="row g-2 mb-3 align-items-end" onSubmit={onSearch} role="search">
                <div className="col-md-5">
                    <label htmlFor="q" className="form-label">Search</label>
                    <input
                        type="search"
                        id="q"
                        className="form-control"
                        placeholder="Username, email, or name"
                        value={term}
                        onChange={(e) => setTerm(e.target.value)}
                    />
                </div>
                <div className="col-md-2">
                    <label htmlFor="acl" className="form-label">Access Level</label>
                    <select
                        id="acl"
                        className="form-select"
                        value={filters.acl ?? ''}
                        onChange={(e) => update({ acl: e.target.value })}
                    >
                        <option value="">All</option>
                        {aclOptions.map((o) => (
                            <option key={o.value} value={o.value}>{o.label}</option>
                        ))}
                    </select>
                </div>
                <div className="col-md-2">
                    <label htmlFor="inactive" className="form-label">Status</label>
                    <select
                        id="inactive"
                        className="form-select"
                        value={filters.inactive ?? ''}
                        onChange={(e) => update({ inactive: e.target.value })}
                    >
                        <option value="">All</option>
                        {statusOptions.map((o) => (
                            <option key={o.value} value={o.value}>{o.label}</option>
                        ))}
                    </select>
                </div>
                <div className="col-md-3 d-flex gap-2">
                    <button type="submit" className="btn btn-primary flex-fill">
                        <i className="fa fa-search"></i> Search
                    </button>
                    {showClear && (
                        <button type="button" className="btn btn-outline-secondary flex-fill" onClick={reset}>
                            Clear
                        </button>
                    )}
                </div>
            </form>

            {error && <div className="alert alert-danger" role="alert">{error}</div>}

            <PaginationSummary pagination={data.pagination} noun="users" />

            <div aria-busy={loading} style={{ opacity: loading ? 0.5 : 1, transition: 'opacity .15s' }}>
                <table className="table table-striped table-bordered table-hover">
                    <thead>
                        <tr>
                            <SortableTh column="username" filter={data.filter} onSort={onSort}>Username</SortableTh>
                            <SortableTh column="acl" filter={data.filter} onSort={onSort}>Access Level</SortableTh>
                            <SortableTh column="inactive" filter={data.filter} onSort={onSort}>Status</SortableTh>
                            <SortableTh column="created_at" filter={data.filter} onSort={onSort}>Created</SortableTh>
                            <SortableTh column="updated_at" filter={data.filter} onSort={onSort}>Last Update</SortableTh>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        {data.users.map((user) => (
                            <tr key={user.id}>
                                <td>{user.username}</td>
                                <td>
                                    {user.acl.length === 0
                                        ? <span className="text-muted">—</span>
                                        : user.acl.map((acl) => (
                                            <span key={acl} className="badge text-bg-secondary me-1">{acl}</span>
                                        ))}
                                </td>
                                <td>{user.inactive ? 'Inactive' : 'Active'}</td>
                                <td title={DateTime.formatTime(user.created_at, DateTime.FORMAT_HUMAN_READABLE)}>
                                    {DateTime.timeAgo(user.created_at)}
                                </td>
                                <td title={DateTime.formatTime(user.updated_at, DateTime.FORMAT_HUMAN_READABLE)}>
                                    {DateTime.timeAgo(user.updated_at)}
                                </td>
                                <td className="text-center">
                                    <a href={route('admindashboard.details', [user.id])} className="btn btn-info btn-sm w-100">
                                        <i className="fa fa-user"></i> Details
                                    </a>
                                </td>
                            </tr>
                        ))}
                        {data.users.length === 0 && (
                            <tr>
                                <td colSpan={6} className="text-center text-muted py-4">
                                    {data.filter?.active ? 'No users match your search.' : 'No other users yet.'}
                                </td>
                            </tr>
                        )}
                    </tbody>
                </table>
            </div>

            <Pagination pagination={data.pagination} onPageChange={goToPage} />

            {widgetsHtml && (
                <SafeHtml html={widgetsHtml} className="widget-container" decode={false} addTargetBlank={false} />
            )}
        </>
    );
}

JSX;
    }

    /**
     * Generates the PHP version of the index view.
     *
     * @return string The view's contents.
     */
    public static function makeIndexPHP(): string {
        return <<<PHP
<?php
\$e = fn(\$value): string => htmlspecialchars((string)\$value, ENT_QUOTES, 'UTF-8');
\$filters = \$this->filter->activeFilters();

/** Sortable column header with a direction indicator. */
\$sortHeader = function (string \$column, string \$label) use (\$e): string {
    \$arrow = '';
    if (\$this->filter->sortColumn() === \$column) {
        \$arrow = \$this->filter->sortDirection() === 'asc' ? ' &#9650;' : ' &#9660;';
    }
    return '<a href="' . \$e(\$this->filter->sortUrl(\$column)) . '" class="text-reset text-decoration-none">'
        . \$e(\$label) . \$arrow . '</a>';
};
?>
<?php \$this->setSiteTitle("Administration"); ?>
<?php \$this->start('body'); ?>
<h1 class="text-center">Administration Dashboard</h1>

<form method="get" class="row g-2 mb-3 align-items-end">
    <div class="col-md-5">
        <label for="q" class="form-label">Search</label>
        <input type="search" id="q" name="q" class="form-control"
               placeholder="Username, email, or name" value="<?= \$e(\$this->filter->term()) ?>">
    </div>
    <div class="col-md-2">
        <label for="acl" class="form-label">Access Level</label>
        <select id="acl" name="acl" class="form-select">
            <option value="">All</option>
            <?php foreach (\$this->aclOptions as \$value => \$label): ?>
                <option value="<?= \$e(\$value) ?>" <?= (\$filters['acl'] ?? null) === (string)\$value ? 'selected' : '' ?>>
                    <?= \$e(\$label) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-2">
        <label for="inactive" class="form-label">Status</label>
        <select id="inactive" name="inactive" class="form-select">
            <option value="">All</option>
            <?php foreach (\$this->statusOptions as \$value => \$label): ?>
                <option value="<?= \$e(\$value) ?>" <?= (\$filters['inactive'] ?? null) === (string)\$value ? 'selected' : '' ?>>
                    <?= \$e(\$label) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <?php // Keep the current sort when a new search is submitted. ?>
    <?php if (isset(\$this->filter->query()['sort'])): ?>
        <input type="hidden" name="sort" value="<?= \$e(\$this->filter->sortColumn()) ?>">
        <input type="hidden" name="dir" value="<?= \$e(\$this->filter->sortDirection()) ?>">
    <?php endif; ?>
    <div class="col-md-3 d-flex gap-2">
        <button type="submit" class="btn btn-primary flex-fill">
            <i class="fa fa-search"></i> Search
        </button>
        <?php if (\$this->filter->isActive()): ?>
            <a href="?" class="btn btn-outline-secondary flex-fill">Clear</a>
        <?php endif; ?>
    </div>
</form>

<?php if (\$this->pagination->totalItems() > 0): ?>
    <p class="text-muted small mb-2">
        Showing <?= \$this->pagination->firstItem() ?>–<?= \$this->pagination->lastItem() ?>
        of <?= \$this->pagination->totalItems() ?> users
    </p>
<?php endif; ?>

<table class="table table-striped table-bordered table-hover">
    <thead>
        <tr>
            <th><?= \$sortHeader('username', 'Username') ?></th>
            <th><?= \$sortHeader('acl', 'Access Level') ?></th>
            <th><?= \$sortHeader('inactive', 'Status') ?></th>
            <th><?= \$sortHeader('created_at', 'Created') ?></th>
            <th><?= \$sortHeader('updated_at', 'Last Update') ?></th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach (\$this->users as \$user): ?>
            <tr>
                <td><?= \$e(\$user->username) ?></td>
                <td><?= \$e(\$user->acl) ?></td>
                <td><?= (\$user->inactive == 0) ? 'Active' : 'Inactive' ?></td>
                <td><?= \$user->date('created_at')->diffForHumans() ?></td>
                <td><?= \$user->date('updated_at')->diffForHumans() ?></td>
                <td class="text-center">
                    <a href="<?= route('admindashboard.details', [\$user->id]) ?>" class="btn btn-info btn-sm w-100">
                        <i class="fa fa-user"></i> Details
                    </a>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty(\$this->users)): ?>
            <tr>
                <td colspan="6" class="text-center text-muted py-4">
                    <?= \$this->filter->isActive() ? 'No users match your search.' : 'No other users yet.' ?>
                </td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>
<?= \$this->pagination->render() ?>

<div class="widget-container">
    <?= \$this->renderWidgets('dashboard.index', \$this->widgets) ?>
</div>
<?php \$this->end(); ?>
PHP;
    }

    /**
     * Generates JS version of job details view.
     *
     * @return string The contents of the view.
     */
    public static function makeJobDetailsJS(): string {
        return <<<JSX
import { documentTitle, route } from "@chappy/utils";
import { DateTime } from "@chappy/utils/datetime";
import React from "react";

/**
 * 
 * @param {*} param0 
 * @returns 
 */
function JobDetails({ job }) {
    const payload = JSON.parse(job.payload);
    documentTitle(`Job ID: \${job.id} Details`)
    return (
        <>
            <a href={route('admindashboard.queueManager') }class="ms-5 btn btn-sm btn-secondary">Back</a>
            <h1 className="text-center">Queued Job Details</h1>
            <div className="d-flex justify-content-center w-75 mx-auto">
                <table className="table table-striped table-bordered table-hover bg-light my-5 col-md-12">
                    <tbody>
                        <tr>
                            <th className="text-center w-25">ID</th>
                            <td>{job.id}</td>
                        </tr>
                        <tr>
                            <th className="text-center w-25">Queue</th>
                            <td>{job.queue}</td>
                        </tr>
                        <tr>
                            <th className="text-center w-25">"payload.job":</th>
                            <td>{payload.job}</td>
                        </tr>
                        <tr>
                            <th className="text-center w-25">"payload.data":</th>
                            <td>
                                {Object.entries(payload.data).map(([k, v]) => (
                                    <p key={k}>"{k}": {v}</p>
                                ))}
                            </td>
                        </tr>
                        <tr>
                            <th className="text-center w-25">"payload.available_at":</th>
                            <td title={DateTime.formatTime(payload.available_at, DateTime.FORMAT_HUMAN_READABLE)} className="text-start">
                                {DateTime.formatTime(payload.available_at, DateTime.FORMAT_HUMAN_READABLE)}
                            </td>
                        </tr>
                        <tr>
                            <th className="text-center w-25">"payload.max_attempts":</th>
                            <td>{payload.max_attempts}</td>
                        </tr>
                        <tr>
                            <th className="text-center w-25">Attempts</th>
                            <td>{job.attempts}</td>
                        </tr>
                        {job.reserved_at && (
                            <tr>
                                <th className="text-center w-25">Reserved At</th>
                                <td title={DateTime.formatTime(job.reserved_at, DateTime.FORMAT_HUMAN_READABLE)} className="text-start">
                                    {DateTime.formatTime(job.reserved_at, DateTime.FORMAT_HUMAN_READABLE)}
                                </td>
                            </tr>
                        )}
                        {job.failed_at && (
                            <tr>
                                <th className="text-center w-25">Failed At</th>
                                <td title={DateTime.formatTime(job.failed_at, DateTime.FORMAT_HUMAN_READABLE)} className="text-start">
                                    {DateTime.formatTime(job.failed_at, DateTime.FORMAT_HUMAN_READABLE)}
                                </td>
                            </tr>
                        )}
                        <tr>
                            <th className="text-center w-25">Available At</th>
                            <td title={DateTime.formatTime(job.available_at, DateTime.FORMAT_HUMAN_READABLE)} className="text-start">
                                {DateTime.formatTime(job.available_at, DateTime.FORMAT_HUMAN_READABLE)}
                            </td>
                        </tr>
                        <tr>
                            <th className="text-center w-25">Created At</th>
                            <td title={DateTime.formatTime(job.created_at, DateTime.FORMAT_HUMAN_READABLE)} className="text-start">
                                {DateTime.formatTime(job.created_at, DateTime.FORMAT_HUMAN_READABLE)}
                            </td>
                        </tr>
                        <tr>
                            <th className="text-center w-25">Updated At</th>
                            <td title={DateTime.formatTime(job.updated_at, DateTime.FORMAT_HUMAN_READABLE)} className="text-start">
                                {DateTime.formatTime(job.updated_at, DateTime.FORMAT_HUMAN_READABLE)}
                            </td>
                        </tr>
                        <tr>
                            <th className="text-center w-25">Exception</th>
                            <td>{job.exception}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </>
    );
}        
export default JobDetails;
JSX;
    }

    /**
     * Generates PHP version of job details view.
     *
     * @return string The contents of the view.
     */
    public static function makeJobDetailsPHP(): string {
        return <<<PHP
<?php use Core\Lib\Utilities\DateTime; ?>
<?php \$this->setSiteTitle("Queued Job Details"); ?>
<?php \$this->start('body'); ?>
<a href="<?= route('admindashboard.queueManager') ?>" class="ms-5 btn btn-sm btn-secondary">Back</a>
<h1 class="text-center">Queued Job Details</h1>

<div class="d-flex justify-content-center w-75 mx-auto">
    <table class="table table-striped table-bordered table-hover bg-light my-5 col-md-12">
        <tbody>
            <tr>
                <th class="text-center w-25">ID</th>
                <td class="text-center" colspan="2"><?= \$this->job->id ?></td>
            </tr>
            <tr>
                <th class="text-center w-25">Queue</th>
                <td class="text-center" colspan="2"><?= \$this->job->queue ?></td>
            </tr>
            <tr>
                <th class="text-center align-middle w-25" rowspan="5">Payload</th>
                <tr>
                    <td class="text-end">"job":</td>
                    <td class="text-start"><?= \$this->payload->job ?></td>
                </tr>
                <tr>
                    <td class="text-end">"data":</td>
                    <td class="text-start">
                        <?php foreach(\$this->payload->data as \$k => \$v): ?>
                            <?= "\"{\$k}\": {\$v}" ?>
                        <?php endforeach; ?>
                    </td>
                </tr>
                <tr>
                    <td class="text-end">"available_at":</td>
                    <td class="text-start">
                        <?= DateTime::formatTime(\$this->payload->available_at, DateTime::FORMAT_HUMAN_READABLE) ?>
                    </td>
                </tr>
                <tr>
                    <td class="text-end">"max_attempts":</td>
                    <td class="text-start"><?= \$this->payload->max_attempts ?></td>
                </tr>
            </tr>
            <tr>
                <th class="text-center w-25">Attempts</th>
                <td class="text-center" colspan="2"><?= \$this->job->attempts ?></td>
            </tr>
            <?php if(\$this->job->reserved_at): ?>
                <tr>
                    <th class="text-center w-25">Reserved At</th>
                    <td class="text-center" colspan="2"><?= \$this->job->reserved_at ?></td>
                </tr>
            <?php endif; ?>
            <?php if(\$this->job->failed_at): ?>
                <tr>
                    <th class="text-center w-25">Failed At</th>
                    <td class="text-center" colspan="2"><?= \$this->job->reserved_at ?></td>
                </tr>
            <?php endif; ?>
            <tr>
                <th class="text-center w-25">Available At</th>
                <td class="text-center" colspan="2"><?= \$this->job->date('available_at')->format(DateTime::FORMAT_FRIENDLY_DATE) ?></td>
            </tr>
            <tr>
                <th class="text-center w-25">Created At</th>
                <td class="text-center" colspan="2"><?= \$this->job->date('created_at')->format(DateTime::FORMAT_HUMAN_READABLE) ?></td>
            </tr>
            <tr>
                <th class="text-center w-25">Updated At</th>
                <td class="text-center" colspan="2"><?= \$this->job->date('updated_at')->format(DateTime::FORMAT_HUMAN_READABLE) ?></td>
            </tr>
            <tr>
                <th class="text-center w-25">Exception</th>
                <td class="text-center" colspan="2"><?= \$this->job->exception ?></td>
            </tr>
        </tbody>
    </table>
</div>
<?php \$this->end(); ?>
PHP;
    }

    /**
     * Generates the React.js version of the manage acls view.
     *
     * @return string The view's contents.
     */
    public static function makeManageAclsJS(): string {
        return <<<JSX
import React, { useState } from "react";
import { apiError, apiDelete, CSRFInput, DateTime, documentTitle, route, CSRFToken } from "@chappy/utils";

/**
 * Renders manage ACLs view.
 * 
 * @property {array} usedAcls The array of used ACLs.
 * @property {array} unUsedAcls The array of unUsedAcls ACLs.
 * @param {*} param0 
 * @returns {HTMLDivElement} View containing features for managing ACLs.
 */
function ManageAcls({ usedAcls, unUsedAcls }) {
    documentTitle("Manage ACLs");
    
    /**
     * Manages state for error.  Errors are generated when there is a failure 
     * deleting an ACL record.
     */
    const [error, setError] = useState(null);

    /**
     * Creates a click handler that deletes an ACL after the user confirms.
     *
     * On confirmation, sends a DELETE request with the CSRF token to
     * `/admindashboard/deleteAcl/{id}` and reloads the page so the list and any
     * flash message reflect the change. Errors are displayed with `setError`.
     *
     * @example
     * <button onClick={onDeleteClick(acl)}>Delete</button>
     *
     * @param {{ id: number, acl: string }} acl The ACL to delete.
     * @returns {(event: React.MouseEvent) => Promise<void>} Click handler for the delete button.
     */
    const onDeleteClick = (acl) => async (event) => {
        event.preventDefault();
        if(window.confirm(`Are you sure you want to delete the ACL: \${acl.acl}`)) {
            try {
                const payload = {
                    csrf_token: CSRFToken(event)
                }
                const json = await apiDelete(`/admindashboard/deleteAcl/\${acl.id}`, payload);
                window.location.reload();
            } catch(err) {
                setError(apiError(err));
            }
        }
    }

    return (
        <>
            <h1 className="text-center">Manage ACLs
                <a href={route('admindashboard.editAcl', ['new'])} className="btn btn-primary btn-sm me-5">
                    <i className="fa fa-plus"></i> Add ACL
                </a>
            </h1>

            <table className="table table-striped table-bordered table-hover mb-5">
                <thead>
                    <tr>
                        <th className="text-center">ACL</th>
                        <th className="text-center">Created</th>
                        <th className="text-center">Updated</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    {unUsedAcls.map((acl) => (
                        <tr key={acl.id}>
                            {acl.acl !== 'Admin' && (
                                <>
                                    <td className="text-center w-50">{acl.acl}</td>
                                    <td title={DateTime.formatTime(acl.created_at, DateTime.FORMAT_HUMAN_READABLE)} className="text-center">
                                        {DateTime.timeAgo(acl.created_at)}
                                    </td>
                                    <td title={DateTime.formatTime(acl.updated_at, DateTime.FORMAT_HUMAN_READABLE)} className="text-center">
                                        {DateTime.timeAgo(acl.updated_at)}
                                    </td>
                                    <td className="text-center">
                                        <a href={route('admindashboard.editAcl', [acl.id])} className="btn btn-info btn-sm">
                                            <i className="fa fa-edit"></i> Edit ACL
                                        </a>
                                        {error ? (<p className="text-danger">{error}</p>) : 
                                            <form method="POST" onSubmit={onDeleteClick(acl)} className="d-inline-block">
                                                <CSRFInput />
                                                <button type="submit" className="btn btn-danger btn-sm">
                                                    <i className="fa fa-trash"></i> Delete
                                                </button>
                                            </form>
                                        }
                                    </td>
                                </>
                            )}
                        </tr>
                    ))}
                </tbody>
            </table>

            <h2 className="text-center">ACLs In Use</h2>
            <table className="table table-striped table-bordered table-hover mb-5">
                <thead>
                    <tr>
                        <th className="text-center">ACL</th>
                        <th className="text-center">Created</th>
                        <th className="text-center">Updated</th>
                    </tr>
                </thead>
                <tbody>
                    {usedAcls.map((acl) => (
                        <tr key={acl.id}>
                            <>
                                <td className="text-center w-50">{acl.acl}</td>
                                <td title={DateTime.formatTime(acl.created_at, DateTime.FORMAT_HUMAN_READABLE)} className="text-center">
                                    {DateTime.timeAgo(acl.created_at)}
                                </td>
                                <td title={DateTime.formatTime(acl.updated_at, DateTime.FORMAT_HUMAN_READABLE)} className="text-center">
                                    {DateTime.timeAgo(acl.updated_at)}
                                </td>
                            </>
                        </tr>
                    ))}
                </tbody>
            </table>
        </>
    );
}        
export default ManageAcls;
JSX;
    }

    /**
     * Generates the PHP version of the manage acls view.
     *
     * @return string The view's contents.
     */
    public static function makeManageAclsPHP(): string {
        return <<<PHP
<?php \$this->setSiteTitle("Manage ACLs"); ?>
<?php \$this->start('body'); ?>
<h1 class="text-center">Manage ACLs
    <a href="<?=route('admindashboard.editAcl', ['new'])?>" class="btn btn-primary btn-sm me-5">
        <i class="fa fa-plus"></i> Add ACL
    </a>
</h1>
<table class="table table-striped  table-bordered table-hover mb-5">
    <thead>
        <th class="text-center">ACL</th>
        <th class="text-center">Created</th>
        <th class="text-center">Updated</th>
        <th></th>
    </thead>
    <tbody>
        <?php foreach(\$this->unUsedAcls as \$acl): ?>
            <tr>
                <?php if(\$acl->acl !== "Admin"): ?>
                    <td class="text-center w-50"><?= \$acl->acl ?></td>
                    <td class="text-center"><?= \$acl->date('created_at')->diffForHumans() ?></td>
                    <td class="text-center"><?= \$acl->date('updated_at')->diffForHumans() ?></td>
                    <td class="text-center">
                        <a href="<?=route('admindashboard.editAcl', [\$acl->id])?>" class="btn btn-info btn-sm">
                            <i class="fa fa-edit"></i> Edit ACL
                        </a>
                        <form method="POST" 
                            action="<?=route('admindashboard.deleteAcl')?>" 
                            class="d-inline-block" 
                            onsubmit="if(!confirm('Are you sure?')){return false;}">
                            <?= hidden('id', \$acl->id) ?>
                            <?= \$csrfToken = csrf() ?>
                            <button type="submit" class="btn btn-danger btn-sm">
                                <i class="fas fa-trash-alt"></i> Delete
                            </button>
                        </form>
                    </td>
                <?php endif; ?>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<h2 class="text-center">ACLs In Use</h2>
<table class="table table-striped  table-bordered table-hover">
    <thead>
        <th class="text-center">ACL</th>
        <th class="text-center">Created</th>
        <th class="text-center">Updated</th>
    </thead>
    <tbody>
        <?php foreach(\$this->usedAcls as \$acl): ?>
            <tr>
                <td class="text-center w-50"><?= \$acl->acl ?></td>
                <td class="text-center"><?= \$acl->date('created_at')->diffForHumans() ?></td>
                <td class="text-center"><?= \$acl->date('updated_at')->diffForHumans() ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<div class="widget-container">
    <?= \$this->renderWidgets('dashboard.acls', \$this->widgets) ?>
</div>

<?php \$this->end(); ?>
PHP;
    }

    /**
     * Generates the JS version of the queue manager view.
     *
     * @return string The contents of the view.
     */
    public static function makeQueueManagerJS(): string {
        return <<<JSX
import React, { useState } from "react";
import { apiDelete, apiError, CSRFInput, CSRFToken, DateTime, documentTitle, route } from "@chappy/utils";
import usePaginatedQuery from '@chappy/utils/usePaginatedQuery';
import Pagination, { PaginationSummary } from '@chappy/components/Pagination';

/**
 * Renders Queue Manager view.
 * 
 * @property {object} pagination The serialized state of paginated data for React.vs views.
 * @property {array} queue The list of queued jobs.
 * @param {*} param0 
 * @returns {HTMLDivElement} View containing features for managing queues. 
 */
function QueueManager({ pagination, queue }) {
    documentTitle("Queue Manager");

    // Paginated query for supporting traversal of pages without reload.
    const { data, loading, error, goToPage } = usePaginatedQuery({ queue, pagination });

    /**
     * Manages state for error.  Errors are generated when there is a failure 
     * deleting an ACL record.
     */
    const [formError, setFormError] = useState(null);

    /**
         * Creates a click handler that deletes a job after the user confirms.
         *
         * On confirmation, sends a DELETE request with the CSRF token to
         * `/admindashboard/deleteJob/{id}` and reloads the page so the list and any
         * flash message reflect the change. Errors are displayed with `setError`.
         *
         * @example
         * <button onClick={onDeleteClick(job)}>Delete</button>
         *
         * @param {{ id: number, job: string }} job The job to delete.
         * @returns {(event: React.MouseEvent) => Promise<void>} Click handler for the delete button.
         */
    const onDeleteClick = (job) => async (event) => {
        event.preventDefault();
        if(window.confirm('Are you sure you want to delete this job?')) {
            try {
                const payload = {
                    csrf_token: CSRFToken(event)
                }
                const json = await apiDelete(`/admindashboard/deleteJob/\${job.id}`, payload);
                window.location.reload();
            } catch(err) {
                setFormError(apiError(err));
            }
        }
    }

    return (
        <>
            {error && <div className="alert alert-danger">{error}</div>}
            <h1 className="text-center mb-5">Queue Manager</h1>
            {queue ? (
                <div>
                    <div className="d-flex flex-column justify-content-center" style={{ opacity: loading ? 0.5 : 1 }}>
                        <div className="w-75 mx-auto mb-3">
                            <PaginationSummary pagination={data.pagination} noun="queue" />
                        </div>
                        <table className="w-75 mx-auto table table-striped table-bordered table-hover table-sm">
                            <thead>
                                <tr>
                                    <td>ID</td>
                                    <th>Queue</th>
                                    <th>Attempts</th>
                                    <th>Created</th>
                                    <th>Updated</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                {data.queue.map((job) => (
                                    <tr key={job.id}>
                                        <td>{job.id}</td>
                                        <td>{job.queue}</td>
                                        <td>{job.attempts}</td>
                                        <td title={DateTime.formatTime(job.created_at, DateTime.FORMAT_HUMAN_READABLE)}>
                                            {DateTime.timeAgo(job.created_at)}
                                        </td>
                                        <td title={DateTime.formatTime(job.updated_at, DateTime.FORMAT_HUMAN_READABLE)}>
                                            {DateTime.timeAgo(job.updated_at)}
                                        </td>
                                        <td className="d-flex gap-3 justify-content-center">
                                            <a href={route('admindashboard.jobDetails', [job.id])} className="btn btn-info btn-sm">
                                                <i className="fa fa-info"></i> Details
                                            </a>
                                            {formError ? (<p className="text-danger">{formError}</p>) : 
                                                <form method="POST" onSubmit={onDeleteClick(job)} className="d-inline-block">
                                                    <CSRFInput />
                                                    <button type="submit" className="btn btn-danger btn-sm">
                                                        <i className="fa fa-trash"></i> Delete
                                                    </button>
                                                </form>
                                            }
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                        <Pagination pagination={data.pagination} onPageChange={goToPage} />
                    </div>
                </div>
            ) : (
                <h3 className="text-center">There are no pending jobs in the queue table.</h3>
            )}
        </>
    );
}        
export default QueueManager;
JSX;
    }

    /**
     * Generates the PHP version of the queue manager view.
     *
     * @return string The contents of the view.
     */
    public static function makeQueueManagerPHP(): string {
        return <<<PHP
<?php \$this->setSiteTitle("Queue Manager"); ?>
<?php \$this->start('body'); ?>
<h1 class="text-center mb-5">Queue Manager</h1>

<?php if(\$this->queue): ?>
    <div class="d-flex justify-content-center">
        <table class="w-75 table table-striped table-bordered table-hover table-striped table-sm">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Queue</th>
                    <th>Attempts</th>
                    <th>Created</th>
                    <th>Updated</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach(\$this->queue as \$job): ?>
                    <tr>
                        <td><?= \$job->id ?></td>
                        <td><?= \$job->queue ?></td>
                        <td><?= \$job->attempts ?></td>
                        <td><?= \$job->date('created_at')->diffForHumans() ?></td>
                        <td><?= \$job->date('updated_at')->diffForHumans() ?></td>
                        <td class="text-end">
                            <a href="<?= route('admindashboard.jobDetails', [\$job->id]) ?>"
                                class="btn btn-primary btn-sm"><i class="fa fa-info"></i> Details
                            </a>
                            <form method="POST"
                                action="<?= route('admindashboard.deleteJob', [\$job->id]) ?>"
                                class="d-inline-block"
                                onsubmit="return confirm('Are you sure you want to delete this job?')">
                                <?= csrf() ?>
                                <button type="submit" class="btn btn-danger btn-sm">
                                    <i class="fa fa-trash"></i> Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?= \$this->pagination->render(); ?>
<?php else: ?>
    <h3 class="text-center">There are no pending jobs in the queue table.</h3>
<?php endif; ?>
<?php \$this->end(); ?>
PHP;
    }

    /**
     * Generates the React.js version of the set account status view.
     *
     * @return string The view's contents.
     */
    public static function makeSetAccountStatusJS(): string { 
        return <<<JSX
import React from "react";
import { CheckBoxLeftLabel, CSRFInput, documentTitle, route, SubmitTag } from "@chappy/utils";

/**
 * Renders set account status view.
 * 
 * @property {boolean} checked The checked status for the checkbox.
 * @property {object} errors The errors object.
 * @property {object} users The users object.
 * @param {*} param0 
 * @returns 
 */
function SetAccountStatus({ checked, errors, user }) {
    const title = `Set status for \${user.username}`;
    documentTitle(title);
    return (
        <>
            <h1 className="text-center">{title}</h1>

            <div className="row align-items-center justify-content-center">
                <div className="col-md-3 bg-light p-3">
                    <form className="form" method="POST" action="">
                        <CSRFInput />
                        <CheckBoxLeftLabel 
                            label="Select to activate/deactivate account"
                            name="inactive"
                            value="on"
                            checked={checked}
                            divAttrs={{className: 'form-group mb-3'}}
                            errors={errors}
                        />
                        <div className="col-md-12 text-end pt-3">
                            <a href={route('admindashboard.details', [user.id])} className="btn btn-default">Cancel</a>
                            <SubmitTag label="Submit" inputAttrs={{className: 'btn btn-primary'}} />
                        </div>
                    </form>
                </div>
            </div>
        </>
    );
}        
export default SetAccountStatus;
JSX;
    }

    /**
     * Generates the PHP version of the set account status view.
     *
     * @return string The view's contents.
     */
    public static function makeSetAccountStatusPHP(): string {
        return <<<PHP
<?php \$this->setSiteTitle("Set Status for ".\$this->user->username); ?>
<?php \$this->start('body') ?>

<h1 class="text-center">Set Status for <?=\$this->user->username?></h1>

<div class="row align-items-center justify-content-center">
    <div class="col-md-3 bg-light p-3">
        <form class="form" action=<?=\$this->postAction?> method="post">
            <?= csrf() ?>
            <?= checkboxLabelLeft('Select to activate/deactivate account', 'inactive', "on", \$this->user->isInactiveChecked(), [], ['class' => 'form-group mb-3'], \$this->displayErrors); ?>
            <div class="col-md-12 text-end">
                <a href="<?=route('admindashboard.details', [\$this->user->id])?>" class="btn btn-default">Cancel</a>
                <?= submit('Submit',['class'=>'btn btn-primary']) ?>
            </div>
        </form>
    </div>
</div>
<?php \$this->end(); ?>
PHP;
    }

    /**
     * Generates the React.js version of the set reset password view.
     *
     * @return string The view's contents.
     */
    public static function makeSetResetPasswordJS(): string {
        return <<<JSX
import React from "react";
import { CheckBoxLeftLabel, CSRFInput, documentTitle, route, SubmitTag } from "@chappy/utils";

/**
 * Renders set reset password view.
 * 
 * @property {boolean} checked The checked status for the checkbox.
 * @property {object} errors The errors object.
 * @property {object} users The users object.
 * @param {*} param0 
 * @returns 
 */
function SetResetPassword({ checked, errors, user }) {
    const title = `Reset password for \${user.username}`;
    documentTitle(title);

    return (
        <>
            <h1 className="text-center">{title}</h1>

            <div className="row align-items-center justify-content-center">
                <div className="col-md-3 bg-light p-3">
                    <form className="form" method="POST" action="">
                        <CSRFInput />
                        <CheckBoxLeftLabel 
                            label="Select to confirm reset password"
                            name="reset_password"
                            value="on"
                            checked={checked}
                            divAttrs={{className: 'form-group mb-3'}}
                            errors={errors}
                        />
                        <div className="col-md-12 text-end pt-3">
                            <a href={route('admindashboard.details', [user.id])} className="btn btn-default">Cancel</a>
                            <SubmitTag label="Submit" inputAttrs={{className: 'btn btn-primary'}} />
                        </div>
                    </form>
                </div>
            </div>
        </>
    );
}        
export default SetResetPassword;
JSX;
    }

    /**
     * Generates the PHP version of the set reset password view.
     *
     * @return string The view's contents.
     */
    public static function makeSetResetPasswordPHP(): string {
        return <<<PHP
<?php \$this->setSiteTitle("Reset Password for ".\$this->user->username); ?>
<?php \$this->start('body') ?>

<h1 class="text-center">Reset Password for <?=\$this->user->username?></h1>

<div class="row align-items-center justify-content-center">
    <div class="col-md-3 bg-light p-3">
        <form class="form" action=<?=\$this->postAction?> method="post">
            <?= csrf() ?>
            <?= checkboxLabelLeft('Select to confirm reset password', 'reset_password', "on", \$this->user->isResetPWChecked(), [], ['class' => 'form-group mb-3'], \$this->displayErrors); ?>
            
            <div class="col-md-12 text-end">
                <a href="<?=route('admindashboard.details', [\$this->user->id])?>" class="btn btn-default">Cancel</a>
                <?= submit('Submit',['class'=>'btn btn-primary']) ?>
            </div>
        </form>
    </div>
</div>
<?php \$this->end(); ?>
PHP;
    }
}