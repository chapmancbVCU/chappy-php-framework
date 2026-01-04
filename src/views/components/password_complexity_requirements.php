<div>
    <h4 class="text-center">Password Requirements</h4>
    <ul class="pl-3">
        <?php if ($this->user->isMinLength() === 'true'): ?>
            <li>Minimum <?= $this->user->minLength() ?> characters in length</li>
        <?php endif; ?>

        <?php if ($this->user->isMaxLength() === 'true'): ?>
            <li>Maximum of <?= $this->user->maxLength() ?> characters in length</li>
        <?php endif; ?>

        <?php if ($this->user->upperChar() === 'true'): ?>
            <li>At least 1 upper case character</li>
        <?php endif; ?>

        <?php if ($this->user->lowerChar() === 'true'): ?>
            <li>At least 1 lower case character</li>
        <?php endif; ?>

        <?php if ($this->user->numericChar() === 'true'): ?>
            <li>At least 1 number</li>
        <?php endif; ?>

        <?php if ($this->user->specialChar() === 'true'): ?>
            <li>Must contain at least 1 special character</li>
        <?php endif; ?>  

        <li>Must not contain any spaces</li>
    </ul>
</div>
