<?php
/**
 * Flash message + ringkasan error validasi.
 *
 * @var list<string>|null $errors
 */
$success   = session()->getFlashdata('success');
$error     = session()->getFlashdata('error');
$info      = session()->getFlashdata('info');
$warning   = session()->getFlashdata('warning');
$evalErrors = session()->getFlashdata('errors');
?>
<?php if (is_string($success) && $success !== '') : ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle-fill me-2" aria-hidden="true"></i><?= esc($success) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="<?= esc(lang('App.close')) ?>"></button>
    </div>
<?php endif; ?>

<?php if (is_string($error) && $error !== '') : ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2" aria-hidden="true"></i><?= esc($error) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="<?= esc(lang('App.close')) ?>"></button>
    </div>
<?php endif; ?>

<?php if (is_string($warning) && $warning !== '') : ?>
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-circle-fill me-2" aria-hidden="true"></i><?= esc($warning) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="<?= esc(lang('App.close')) ?>"></button>
    </div>
<?php endif; ?>

<?php if (is_string($info) && $info !== '') : ?>
    <div class="alert alert-info alert-dismissible fade show" role="alert">
        <i class="bi bi-info-circle-fill me-2" aria-hidden="true"></i><?= esc($info) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="<?= esc(lang('App.close')) ?>"></button>
    </div>
<?php endif; ?>

<?php if (is_array($evalErrors) && $evalErrors !== []) : ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <p class="fw-semibold mb-1"><i class="bi bi-exclamation-triangle-fill me-2" aria-hidden="true"></i>Periksa kembali data berikut:</p>
        <ul class="mb-0 ps-4">
            <?php foreach ($evalErrors as $message) : ?>
                <li><?= esc((string) $message) ?></li>
            <?php endforeach; ?>
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="<?= esc(lang('App.close')) ?>"></button>
    </div>
<?php endif; ?>
