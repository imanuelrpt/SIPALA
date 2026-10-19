<?php

/**
 * Galeri lampiran aduan.
 *
 * @var list<\App\Entities\ComplaintAttachment> $attachments
 * @var string                                  $title
 * @var string                                  $emptyText
 */
$attachments = $attachments ?? [];
$title       = $title ?? 'Lampiran';
$emptyText   = $emptyText ?? 'Tidak ada lampiran.';
?>
<div class="mb-4">
    <h2 class="h6 mb-2"><i class="bi bi-images me-1 text-primary" aria-hidden="true"></i><?= esc($title) ?></h2>

    <?php if ($attachments === []) : ?>
        <p class="text-body-secondary small mb-0"><?= esc($emptyText) ?></p>
    <?php else : ?>
        <div class="upload-preview">
            <?php foreach ($attachments as $attachment) : ?>
                <a class="attachment-link" href="<?= esc($attachment->url(), 'attr') ?>" target="_blank" rel="noopener">
                    <img class="attachment-thumb" loading="lazy"
                         src="<?= esc($attachment->url(), 'attr') ?>"
                         alt="<?= esc('Lampiran: ' . (string) $attachment->original_name, 'attr') ?>">
                    <span class="attachment-caption text-truncate">
                        <?= esc(ukuran_berkas((int) $attachment->size)) ?>
                    </span>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
