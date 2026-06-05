<?php
/**
 * Pagination component.
 * Requires: $paginator (Paginator instance)
 * Preserves all existing $_GET parameters, only updates page_num.
 */
if (!isset($paginator) || $paginator->totalPages() <= 1) return;
?>

<nav aria-label="Page navigation" class="mt-3">
  <ul class="pagination pagination-sm justify-content-center flex-wrap">

    <!-- Previous -->
    <li class="page-item <?= !$paginator->hasPrev() ? 'disabled' : '' ?>">
      <a class="page-link" href="<?= sanitize(buildQueryString(['page_num' => $paginator->prevPage()])) ?>">
        <i class="fas fa-chevron-left"></i>
      </a>
    </li>

    <!-- Page numbers -->
    <?php for ($p = 1; $p <= $paginator->totalPages(); $p++): ?>
      <?php
        // Show first, last, and pages around current
        $showPage = $p === 1
            || $p === $paginator->totalPages()
            || abs($p - $paginator->currentPage()) <= 2;

        if (!$showPage): ?>
          <?php if (abs($p - $paginator->currentPage()) === 3): ?>
            <li class="page-item disabled"><span class="page-link">…</span></li>
          <?php endif; ?>
          <?php continue; ?>
      <?php endif; ?>

      <li class="page-item <?= $p === $paginator->currentPage() ? 'active' : '' ?>">
        <a class="page-link" href="<?= sanitize(buildQueryString(['page_num' => $p])) ?>">
          <?= $p ?>
        </a>
      </li>
    <?php endfor; ?>

    <!-- Next -->
    <li class="page-item <?= !$paginator->hasNext() ? 'disabled' : '' ?>">
      <a class="page-link" href="<?= sanitize(buildQueryString(['page_num' => $paginator->nextPage()])) ?>">
        <i class="fas fa-chevron-right"></i>
      </a>
    </li>

  </ul>

  <p class="text-center text-muted small">
    Page <?= $paginator->currentPage() ?> of <?= $paginator->totalPages() ?>
    &mdash; <?= $paginator->totalItems() ?> total records
  </p>
</nav>
