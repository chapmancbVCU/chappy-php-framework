<?php $this->setSiteTitle("404 - Controller Not Found"); ?>
<?php $this->start('body'); ?>
<h1 class="text-center">404 - Not found</h1>
<h2 class="text-center red">The controller <?= $this->controllerName ?> does not exist.</h2>
<?php $this->end(); ?>