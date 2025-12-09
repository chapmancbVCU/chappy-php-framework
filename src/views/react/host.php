<?php $this->start('head') ?>

<?php $this->end(); ?>

<?php $this->start('body') ?>
  <div
    id="app"
    data-csrf="<?= Core\Lib\React\Vite::csrfToken() ?>"
    data-component="<?= htmlspecialchars($this->component ?? 'home', ENT_QUOTES, 'UTF-8'); ?>"
    data-props='<?= htmlspecialchars(json_encode($this->props ?? []), ENT_QUOTES, "UTF-8"); ?>'>
  </div>
                            

<?php $this->end(); ?>
