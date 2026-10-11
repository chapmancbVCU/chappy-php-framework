<?php
declare(strict_types=1);
namespace Console\Helpers\Stubs\Components;

/**
 * Collection of component stubs for profiles.
 */
class ProfileStubs {
    /**
     * Stub for contents of edit_profile_details component.
     *
     * @return string The contents of the edit_profile_details component.
     */
    public static function profileDetails(): string {
        return <<<PHP
<?= text("First Name", 'fname', \$this->user->fname, ['class' => 'form-control input-sm'], ['class' => 'form-group mb-3']) ?>
<?= text("Last Name", 'lname', \$this->user->lname, ['class' => 'form-control input-sm'], ['class' => 'form-group mb-3']) ?>
<?= email("Email", 'email', \$this->user->email, ['class' => 'form-control input-sm'], ['class' => 'form-group mb-3']) ?>

<?= textarea("Description", 
    'description', 
    \$this->user->description, 
    ['class' => 'form-control input-sm', 'placeholder' => 'Describe yourself here...'], 
    ['class' => 'form-group mb-3']); 
?>
PHP;
    }
}