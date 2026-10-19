<?php

/**
 * Templat paginasi Bootstrap 5 untuk SIPALA.
 *
 * Dirender oleh CodeIgniter melalui `$pager->links('default', 'bootstrap')`,
 * sehingga `$pager` di sini adalah instance PagerRenderer (bukan Pager).
 *
 * @var \CodeIgniter\Pager\PagerRenderer $pager
 */
$pager->setSurroundCount(2);

$totalPages = $pager->getPageCount();
?>
<?php if ($totalPages > 1) : ?>
    <nav aria-label="Navigasi halaman" class="d-flex flex-wrap justify-content-between align-items-center gap-2">
        <p class="text-body-secondary small mb-0">
            <?= esc(lang('App.showing')) ?>
            <?= esc(angka_ringkas($pager->getPerPageStart() ?? 0)) ?>&ndash;<?= esc(angka_ringkas($pager->getPerPageEnd() ?? 0)) ?>
            <?= esc(lang('App.of')) ?> <?= esc(angka_ringkas($pager->getTotal() ?? 0)) ?>
            <?= esc(lang('App.entries')) ?>
        </p>

        <ul class="pagination pagination-sm mb-0">
            <li class="page-item <?= $pager->hasPrevious() ? '' : 'disabled' ?>">
                <a class="page-link" href="<?= $pager->hasPrevious() ? esc($pager->getFirst(), 'attr') : '#' ?>"
                   aria-label="Halaman pertama">&laquo;</a>
            </li>
            <li class="page-item <?= $pager->hasPrevious() ? '' : 'disabled' ?>">
                <a class="page-link" href="<?= $pager->hasPrevious() ? esc($pager->getPrevious(), 'attr') : '#' ?>"
                   aria-label="Halaman sebelumnya">&lsaquo;</a>
            </li>

            <?php foreach ($pager->links() as $link) : ?>
                <li class="page-item <?= $link['active'] ? 'active' : '' ?>">
                    <a class="page-link" href="<?= esc($link['uri'], 'attr') ?>"
                       <?= $link['active'] ? 'aria-current="page"' : '' ?>><?= esc($link['title']) ?></a>
                </li>
            <?php endforeach; ?>

            <li class="page-item <?= $pager->hasNext() ? '' : 'disabled' ?>">
                <a class="page-link" href="<?= $pager->hasNext() ? esc($pager->getNext(), 'attr') : '#' ?>"
                   aria-label="Halaman berikutnya">&rsaquo;</a>
            </li>
            <li class="page-item <?= $pager->hasNext() ? '' : 'disabled' ?>">
                <a class="page-link" href="<?= $pager->hasNext() ? esc($pager->getLast(), 'attr') : '#' ?>"
                   aria-label="Halaman terakhir">&raquo;</a>
            </li>
        </ul>
    </nav>
<?php endif; ?>
