<?php
declare(strict_types=1);
namespace Console\Helpers\Stubs\Profile;

/**
 * Contains stubs for profile php views.
 */
class Views {
    /**
     * Stub for PHP version of edit.
     *
     * @return string The contents of the edit.php file.
     */
    public static function makeEditPHP(): string {
        return <<<PHP
<?php \$this->setSiteTitle("Edit Details for ".\$this->user->username); ?>

<?php \$this->start('head') ?>
<?= profileImageSort() ?>
<?= loadTinyMCE() ?>
<?php \$this->end() ?>

<?php \$this->start('body'); ?>
<div class="row align-items-center justify-content-center mb-5">
    <div class="col-md-6 bg-light p-3">
        <h1 class="text-center">Edit Details for <?=\$this->user->username?></h1>
        <hr>
        <form class="form" action="" method="POST" enctype="multipart/form-data">
            <?= csrf() ?>
            <?= hidden('images_sorted', '') ?>
            <?= errorBag(\$this->displayErrors) ?>
            
            <!-- Primary profile details -->
            <?= \$this->component('edit_profile_details'); ?>
            
            <!-- Manage profile images section -->
            <?= fileSelector("Upload Profile Image (Optional)", 'profileImage', ['class' => 'form-control', 'accept' => 'image/gif image/jpeg image/png'], ['class' => 'form-group mb-3']) ?>
            <?= \$this->component('manage_profile_images', true) ?>

            <div class="col-md-12 text-end">
                <a href="<?=route('profile')?>" class="btn btn-default">Cancel</a>
                <?= submit('Update', ['class' => 'btn btn-primary'])  ?>
            </div>
        </form>
    </div>
</div>

<!-- Wait until content is loaded before we initialize script -->
<?= initTinyMCE('description') ?>
<?php \$this->end(); ?>
PHP;
    }

    /**
     * Stub for PHP version of index.
     *
     * @return string The contents of the index.php file.
     */
    public static function makeIndexPHP(): string {
        return <<<PHP
<?php \$this->setSiteTitle("Profile Details for ".\$this->user->username); ?>
<?php \$this->start('body'); ?>
<h1 class="text-center">Profile Details for <?=\$this->user->username?></h1>

<div class="col align-items-center justify-content-center mx-auto my-3 w-50">
    <?php if(\$this->profileImages != null):?>
        <img src="<?=asset(\$this->profileImages[0]->url)?>"
            class="img-thumbnail mx-auto my-5 d-block w-50 rounded border border-primary shadow-lg">
        </img>
    <?php endif; ?>
    <table class="table table-striped  table-bordered table-hover bg-light my-5">
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
                <th class="text-center">ACL</th>
                <td class="text-center"><?=\$this->user->acl?></td>
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
    <div class="mb-5 d-flex justify-content-around">
        <a href="<?=route('profile.edit')?>" class="btn btn-info btn-sm mx-2 mb-3">
            <i class="fa fa-edit"></i> Edit User Profile
        </a>
        <a href="<?=route('profile.updatePassword')?>" class="btn btn-danger btn-sm mx-2 mb-3">
            <i class="fa fa-key"></i> Update Password
        </a>
    </div>
    
</div>
<?php \$this->end(); ?>
PHP;
    }
}