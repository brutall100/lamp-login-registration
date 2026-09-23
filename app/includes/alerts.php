<?php
/** @var string[] $errors */
/** @var ?string $notice */
?>
<?php if (!empty($errors)) : ?>
    <div class="alert alert--error" role="alert">
        <?php foreach ($errors as $error) : ?>
            <p><?= e($error) ?></p>
        <?php endforeach ?>
    </div>
<?php endif ?>
<?php if (!empty($notice)) : ?>
    <div class="alert alert--success" role="status">
        <p><?= e($notice) ?></p>
    </div>
<?php endif ?>
