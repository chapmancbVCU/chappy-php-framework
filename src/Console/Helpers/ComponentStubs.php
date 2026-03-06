<?php
declare(strict_types=1);
namespace Console\Helpers;

/**
 * Collection of stubs for available components.
 */
class ComponentStubs {
    /**
     * Returns contents of a card component.
     *
     * @return string The contents of a card component.
     */
    public static function cardComponent(): string {
        return <<<HTML
<div class="card">
  <div class="card-header"><?= \$this->title ?></div>
  <div class="card-body"><?= \$this->slot ?></div>
</div>
HTML;
  }

  /**
   * Generates content of form component.
   *
   * @param string $method The method to be used.
   * @param string $encType The enctype to be used.
   * @return string The contents of the form component.
   */
    public static function formComponent(string $method, string $encType): string {
        $enctypeAttr = !empty($encType) ? ' enctype="'.$encType.'"' : '';
        return <<<PHP
<form class="form" action=<?=\$this->postAction?> method="{$method}"{$enctypeAttr}>
    <?= csrf() ?>

</form>
PHP;
    }

    /**
     * Generates content for table component.
     *
     * @return string The content of the table component.
     */
    public static function tableComponent(): string {
        return <<<HTML
<table class="table">
    <thead>
        <tr><?= \$this->headers ?></tr>
    </thead>
    <tbody>
        <?= \$this->slot ?>
    </tbody>
</table>
HTML;
    }
}