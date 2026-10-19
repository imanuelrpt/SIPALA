<?php

/**
 * Formulir tanggapan aduan (dipakai masyarakat, Pemda, dan Admin).
 *
 * @var string                $action     URL tujuan POST
 * @var array<string, string> $errors
 * @var string                $buttonText
 */
$action     = $action ?? '';
$errors     = $errors ?? [];
$buttonText = $buttonText ?? lang('App.send');
?>
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <h2 class="h6 mb-3"><i class="bi bi-chat-left-text me-1 text-primary" aria-hidden="true"></i>Tambah Tanggapan</h2>

        <?= form_open($action, ['novalidate' => true]) ?>
            <?= csrf_field() ?>

            <div class="mb-3">
                <label class="form-label" for="message"><?= esc(lang('App.message')) ?></label>
                <textarea class="form-control <?= is_invalid($errors, 'message') ? 'is-invalid' : '' ?>"
                          id="message" name="message" rows="4" required minlength="3" maxlength="2000"
                          placeholder="Tuliskan tanggapan atau informasi tambahan…"><?= esc(old('message') ?? '') ?></textarea>
                <?php if (is_invalid($errors, 'message')) : ?>
                    <div class="invalid-feedback"><?= esc(field_error($errors, 'message')) ?></div>
                <?php endif; ?>
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="bi bi-send me-1" aria-hidden="true"></i><?= esc($buttonText) ?>
            </button>
        <?= form_close() ?>
    </div>
</div>
