<?php

use App\Enums\UserRole;

/**
 * Linimasa riwayat status dan tanggapan sebuah aduan.
 *
 * @var list<array<string, mixed>> $thread
 * @var bool                       $canSeeReporterName
 */
$thread              = $thread ?? [];
$canSeeReporterName  = $canSeeReporterName ?? false;
?>
<?php if ($thread === []) : ?>
    <p class="text-body-secondary small mb-0"><?= esc(lang('App.noData')) ?></p>
<?php else : ?>
    <ol class="timeline">
        <?php foreach ($thread as $item) : ?>
            <?php
            $isStatus   = ($item['kind'] ?? '') === 'status';
            $actorRole  = UserRole::tryFrom((string) ($item['actor_role'] ?? ''));
            $actorName  = trim((string) ($item['actor'] ?? ''));
            $actorLabel = $actorRole === null ? 'Sistem' : $actorRole->label();
            $shownName  = $actorName === '' ? $actorLabel : $actorName;
            ?>
            <li class="timeline-item <?= $isStatus ? 'timeline-status' : 'timeline-response' ?>">
                <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                    <?php if ($isStatus) : ?>
                        <?php if (($item['from'] ?? null) !== null) : ?>
                            <span class="badge text-bg-light border"><?= esc($item['from']->label()) ?></span>
                            <i class="bi bi-arrow-right small text-body-secondary" aria-hidden="true"></i>
                        <?php endif; ?>
                        <span class="badge rounded-pill <?= esc($item['to']->badgeClass(), 'attr') ?>">
                            <i class="bi <?= esc($item['to']->icon(), 'attr') ?> me-1" aria-hidden="true"></i><?= esc($item['to']->label()) ?>
                        </span>
                    <?php else : ?>
                        <span class="badge text-bg-info-subtle text-info-emphasis border">
                            <i class="bi bi-chat-left-text me-1" aria-hidden="true"></i>Tanggapan
                        </span>
                    <?php endif; ?>
                </div>

                <p class="timeline-meta text-body-secondary mb-1">
                    <?= esc(format_tanggal_waktu($item['at'] ?? null)) ?>
                    <?php if ($actorName !== '') : ?>
                        &middot; <?= esc($shownName) ?>
                        <span class="badge text-bg-light border ms-1"><?= esc($actorLabel) ?></span>
                    <?php else : ?>
                        &middot; <?= esc($actorLabel) ?>
                    <?php endif; ?>
                </p>

                <?php if ($isStatus) : ?>
                    <?php if (! empty($item['note'])) : ?>
                        <p class="mb-0 text-pre-wrap"><?= esc((string) $item['note']) ?></p>
                    <?php endif; ?>
                <?php else : ?>
                    <p class="mb-0 text-pre-wrap"><?= esc((string) ($item['message'] ?? '')) ?></p>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ol>
<?php endif; ?>
