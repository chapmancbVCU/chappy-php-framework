<?php
declare(strict_types=1);
namespace Console\Helpers\Stubs\AdminDashboard;

/**
 * Contains stubs for admin dashboard.
 */
class Views {
    public static function makeAttachmentDetailsJS(): string {
        return <<<JSX
import React from "react";
import SafeHtml from '@chappy/components/SafeHtml';
import { route } from '@chappy/utils';

/**
 * Renders attachment details view.
 * 
 * @property {object} attachment The attachment object
 * @property {string} createdAt When the record was created.
 * @property {string} size The size of the file.
 * @property {string} updatedAt When the record was last updated.
 * @property {object} uploader The upload of the attachment.
 * @param {*} param0 
 * @returns 
 */
function AttachmentDetails({ attachment, createdAt, size, updatedAt, uploader }) {

    return (
        <div className="row align-items-center justify-content-center">
            <div className="col-md-6 bg-light p-3">
                <a href={route('admindashboard.Attachments')}  className="btn btn-sm btn-secondary">Back</a>
                <h2 className="text-center">{attachment.attachment_name}</h2>
                <div className="row">
                    <div className="col-md-6">
                        <p><strong>Created at: </strong>{createdAt}</p>
                        <p><strong>Updated at: </strong>{updatedAt}</p>
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

    public static function makeAttachmentDetailsPHP(): string {
        return <<<PHP
<?php use Core\Models\EmailAttachments; ?>
<?php use Core\Lib\Utilities\DateTime; ?>
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
                <p><strong>Created at: </strong><?= DateTime::timeAgo(\$this->attachment->created_at) ?></p>
                <p><strong>Updated at: </strong><?= DateTime::timeAgo(\$this->attachment->updated_at) ?></p>
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

    public static function makeAttachmentsFormPHP(): string {
        return <<<PHP
<?php \$this->setSiteTitle(\$this->header); ?>

<!-- Head content between these two function calls.  Remove if not needed. -->
<?php \$this->start('head'); ?>
<script src="<?=env('APP_DOMAIN', '/')?>vendor/tinymce/tinymce/tinymce.min.js?v=<?=config('config.version')?>"></script>
<script src='<?=env('APP_DOMAIN', '/')?>vendor/chappy-php/chappy-php-framework/src/React/utils/phpTinyMCE.js'></script>
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
                ['class' => 'form-control input-sm', 'placeholder' => 'Describe yourself here...'], 
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
    <table class="w-75 table table-striped table-bordered table-hover table-striped table-sm">
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
                            <?=hidden('id', \$attachment->id)?>
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

    public static function makeDetailsPHP(): string {
        return <<<PHP
<?php

use Core\Lib\Utilities\DateTime; ?>
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
        <table class="table table-striped  table-bordered table-hover bg-light my-5 col-md-12">
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
                    <td class="text-center"><?= DateTime::timeAgo(\$this->user->created_at) ?></td>
                </tr>
                <tr>
                    <th class="text-center">Last Update</th>
                    <td class="text-center"><?= DateTime::timeAgo(\$this->user->updated_at) ?></td>
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

    public static function makeIndexPHP(): string {
        return <<<PHP
<?php use Core\Lib\Utilities\DateTime; ?>
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
                <td><?= DateTime::timeAgo(\$user->created_at) ?></td>
                <td><?= DateTime::timeAgo(\$user->updated_at) ?></td>
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

    public static function makeManageAclsPHP(): string {
        return <<<PHP
<?php use Core\Lib\Utilities\DateTime; ?>
<?php use Core\FormHelper; ?>
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
                    <td class="text-center"><?= DateTime::timeAgo(\$acl->created_at) ?></td>
                    <td class="text-center"><?= DateTime::timeAgo(\$acl->updated_at) ?></td>
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
                <td class="text-center"><?= DateTime::timeAgo(\$acl->created_at) ?></td>
                <td class="text-center"><?= DateTime::timeAgo(\$acl->updated_at) ?></td>
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
                <?= submit('Activate / Deactivate',['class'=>'btn btn-primary']) ?>
            </div>
        </form>
    </div>
</div>
<?php \$this->end(); ?>
PHP;
    }

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
                <?= submit('Reset Password',['class'=>'btn btn-primary']) ?>
            </div>
        </form>
    </div>
</div>
<?php \$this->end(); ?>
PHP;
    }
}