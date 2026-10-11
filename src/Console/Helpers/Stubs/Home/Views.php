<?php
declare(strict_types=1);
namespace Console\Helpers\Stubs\Home;

/**
 * Contains stubs for home index.
 */
class Views {
    /**
     * The React version of index.
     *
     * @return string The contents of the Index.jsx file.
     */
    public static function makeIndexJS(): string {
        return <<<'JSX'
import React from "react";
import { asset } from '@chappy/utils';

/**
 * The home page.
 * @property {object} user The currently logged in user.
 * @property {string} version The current version of the Chappy.php framework.
 * @param {InputProps} param0 
 * @returns {JSX.Element} The contents of Index view.
 */
export default function Index({ user, version }) {
    const name = user.fname ?? 'Guest';
    return (
        <div className="container">
            <div className="text-center">
                <h1 className="display-4">Hello, {name} 👋</h1>
                <h1 className="display-4">Welcome to</h1>
                <div className="col-12 mx-auto text-center">
                    <img className="w-50" src={asset("public/logo.png", true)} alt="Framework Logo" />
                </div>
                <p className="lead my-3">
                    A lightweight and modern PHP framework built for simplicity, speed, and the power of React.js.
                </p>
                <p className="lead my-3">This view is powered by React + Vite.</p>

                <div className="d-flex justify-content-center mt-4 flex-wrap gap-3">
                    <a className="btn btn-primary" href="https://chapmancbvcu.github.io/chappy-php-starter/">📘 View Documentation</a>
                </div>
            </div>

            <hr className="my-5"/>

            <div className="row text-center g-4">
                <div className="col-md-4">
                    <h4>🔧 MVC Architecture</h4>
                    <p>Familiar routing and controller setup with simple view rendering.</p>
                </div>
                <div className="col-md-4">
                    <h4>🛡️ Custom Forms With Validation</h4>
                    <p>A FormHelper class with support for many commonly used elements and built-in server-side form validation with error message support.</p>
                </div>
                <div className="col-md-4">
                    <h4>⚙️ Project Generator</h4>
                    <p>Generate project skeletons and database migrations using console commands.</p>
                </div>
            </div>

            <div className="row text-center g-4 mt-4">
                <div className="col-md-4">
                    <h4>🧩 Composer and npm Support</h4>
                    <p>Manage your dependencies using Composer and npm.</p>
                </div>
                <div className="col-md-4">
                    <h4>📁 User Management</h4>
                    <p>Includes ACL support and authentication out of the box.</p>
                </div>
                <div className="col-md-4">
                    <h4>📄 Simple Documentation</h4>
                    <p>Markdown and API documentation included and easy to customize.</p>
                </div>
            </div>
            <p className="text-center text-muted mt-5">
                To customize this page, edit <code>resources/js/pages/home/Index.jsx</code> and <code>app/Controllers/HomeController.php</code>.
            </p>
            <p className="text-center text-muted mb-5">
                Chappy.php Framework v{version}
            </p>
        </div>
    );
}
JSX;
    }

    /**
     * Stub for PHP version of index.
     *
     * @return string The contents of the index.php file.
     */
    public static function makeIndexPHP(): string {
        return <<<PHP
<?php \$this->start('head'); ?>
<script src="<?=env('APP_DOMAIN', '/')?>node_modules/jquery/dist/jquery.min.js"></script>
<?php \$this->end(); ?>

<?php \$this->start('body'); ?>

<div class="container">
  <div class="text-center">
    <h1 class="display-4">Welcome to</h1>
    <div class="col-12 mx-auto text-center">
      <img class="w-50" src="<?=asset('public/logo.png', true)?>" alt="Framework Logo">
    </div>
    <p class="lead mt-3">
      A lightweight and modern PHP framework built for simplicity, speed, and developer happiness.
    </p>

    <div class="d-flex justify-content-center mt-4 flex-wrap gap-3">
      <a class="btn btn-primary" href="https://chapmancbvcu.github.io/chappy-php-starter/">📘 View Documentation</a>
    </div>
  </div>

  <hr class="my-5">

  <div class="row text-center g-4">
    <div class="col-md-4">
      <h4>🔧 MVC Architecture</h4>
      <p>Familiar routing and controller setup with simple view rendering.</p>
    </div>
    <div class="col-md-4">
      <h4>🛡️ Custom Forms With Validation</h4>
      <p>A FormHelper class with support for many commonly used elements and built-in server-side form validation with error message support.</p>
    </div>
    <div class="col-md-4">
      <h4>⚙️ Project Generator</h4>
      <p>Generate project skeletons and database migrations using console commands.</p>
    </div>
  </div>

  <div class="row text-center g-4 mt-4">
    <div class="col-md-4">
      <h4>🧩 Composer Support</h4>
      <p>Manage your dependencies using Composer just like Laravel or Symfony.</p>
    </div>
    <div class="col-md-4">
      <h4>📁 User Management</h4>
      <p>Includes ACL support and authentication out of the box.</p>
    </div>
    <div class="col-md-4">
      <h4>📄 Simple Documentation</h4>
      <p>Markdown and API documentation included and easy to customize.</p>
    </div>
  </div>

  <hr class="my-5">

  <div class="text-center">
    <h4>⚡ Ajax In Action</h4>
    <p>Test a sample Ajax call to see the framework response.</p>
    <button class="btn btn-success" onclick="ajaxTest();">Run Ajax Test</button>
  </div>

  <script>
    function ajaxTest() {
      $.ajax({
        type: "POST",
        url: "<?= env('APP_DOMAIN', '/') ?>home/testAjax",
        data: { model_id: 45 },
        success: function (resp) {
          if (resp.success) {
            alert("Server says: " + resp.data.name);
          }
          console.log(resp);
        }
      });
    }
  </script>

  <p class="text-center text-muted mt-5">
    To customize this page, edit <code>resources/views/home/index.php</code> and <code>app/Controllers/HomeController.php</code>.
  </p>
  <p class="text-center text-muted mb-5">
    Chappy.php Framework v<?=config('config.version')?>
  <p>
</div>

<?php \$this->end(); ?>
PHP;
    }
}