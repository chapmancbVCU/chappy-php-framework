<?php
declare(strict_types=1);
namespace Console\Helpers;

/**
 * Collection of stubs for view files.
 */
class ViewStubs {
    /**
     * Generates a layout compatible with React.js
     *
     * @param string $menuName The name of the menu for the layout
     * @return string The contents of the layout.
     */
    public static function layout(string $menuName): string {
        return <<<PHP
<?php use Core\Session; ?>
<?php use Core\Lib\React\Vite; ?>
<?php \$isDev = Vite::isDev(); ?>
<!DOCTYPE html>

<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <title><?=\$this->siteTitle()?></title>
    <link rel="icon" href="<?= env('APP_DOMAIN', '/')?>public/noun-mvc-5340614.png">
    <?= initVite(\$isDev) ?>
    <?= resources() ?>
    <?= \$this->content('head'); ?>

  </head>
  <body class="d-flex flex-column min-vh-100">
    <?= \$this->component('{$menuName}_menu') ?>
    <div class="container-fluid" style="min-height:calc(100% - 125px);">
      <?= Session::displayMessage() ?>
      <?= \$this->content('body'); ?>
    </div>
  </body>
</html>
PHP;     
    }

    /**
     * Returns a string containing contents for a menu.
     *
     * @param string $menuName The name for a new menu.
     * @return string The contents for a new menu.
     */
    public static function menu(string $menuName): string {
        return <<<PHP
<?php
use Core\Router;
use Core\Helper;
\$profileImage = Helper::getProfileImage();
\$menu = Router::getMenu('{$menuName}_menu_acl');
\$userMenu = Router::getMenu('user_menu');
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark bg-gradient sticky-top mb-5">
  <!-- Brand and toggle get grouped for better mobile display -->
  <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#main_menu" aria-controls="main_menu" aria-expanded="false" aria-label="Toggle navigation">
    <span class="navbar-toggler-icon"></span>
  </button>
  <a class="navbar-brand" href="<?=route(env('DEFAULT_CONTROLLER'))?>"><?=env('MENU_BRAND', 'My Brand')?></a>

  <!-- Collect the nav links, forms, and other content for toggling -->
  <div class="collapse navbar-collapse" id="main_menu">
    <ul class="navbar-nav me-auto">
      <?= Helper::buildMenuListItems(\$menu); ?>
    </ul>
    <ul class="navbar-nav me-2"> <!-- Align items vertically -->
      <?= Helper::buildMenuListItems(\$userMenu, "dropdown-menu-end"); ?>
      <li class="nav-item">
        <a class="nav-link p-0" href="<?=route('profile')?>">
          <?php if (\$profileImage != null): ?>
            <img class="rounded-circle profile-img ms-2"
              style="width: 40px; height: 40px; object-fit: cover; border: 2px solid #ddd; transition: opacity 0.3s;"
              src="<?=env('APP_DOMAIN', '/') . \$profileImage->url?>"
              alt="Profile Picture">
          <?php endif; ?>
        </a>
      </li>
    </ul>
  </div><!-- /.navbar-collapse -->
</nav>
PHP;
    }

    /**
     * Returns a string containing contents of a json menu acl file.
     *
     * @param string $menuName The name of the acl file that matches your 
     * menu name
     * @return string The contents of the json menu acl file.
     */
    public static function menuAcl(string $menuName): string {
        return <<<JSON
{
    "Home" : "home",
    "{$menuName}" : ""     
}      
JSON;
    }

    /**
     * Generates content for view file.
     *
     * @return string The content for the view file.
     */
    public static function viewContent(): string {
        return <<<PHP
'<?php \$this->setSiteTitle("My title here"); ?>

<!-- Head content between these two function calls.  Remove if not needed. -->
<?php \$this->start('head'); ?>

<?php \$this->end(); ?>


<!-- Body content between these two function calls. -->
<?php \$this->start('body'); ?>

<?php \$this->end(); ?>
PHP;
    }
}