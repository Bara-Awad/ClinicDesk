<?php
/**
 * ClinicDesk - Paginator
 *
 * Calculates pagination values for LIMIT/OFFSET queries.
 *
 * Usage:
 *   $paginator = new Paginator($totalItems, ITEMS_PER_PAGE, $currentPage);
 *   $appointments = $model->getAll($paginator->offset(), ITEMS_PER_PAGE);
 */

class Paginator
{
    private int $totalItems;
    private int $perPage;
    private int $currentPage;

    /**
     * @param int $totalItems   Total number of rows (from COUNT query) إجمالي عدد السجلات
     * @param int $perPage      Rows per page (use ITEMS_PER_PAGE constant) عدد السجلات في كل صفحة
     * @param int $currentPage  Current page number (1-based, from $_GET['page_num']) (يبدا من 1)رقم الصفحة الحالية  
     */
    public function __construct(int $totalItems, int $perPage, int $currentPage)
    {
        $this->totalItems  = max(0, $totalItems);
        $this->perPage     = max(1, $perPage);
        $this->currentPage = max(1, $currentPage);

        // Clamp to valid range
        $total = $this->totalPages();
        if ($total > 0 && $this->currentPage > $total) {
            $this->currentPage = $total;
        }
    }

    /**
     * SQL OFFSET value for the current page.
     */
    public function offset(): int
    {
        return ($this->currentPage - 1) * $this->perPage;
    }

    /**
     * Total number of pages.
     */
    public function totalPages(): int
    {
        if ($this->totalItems === 0) {
            return 1;
        }
        return (int) ceil($this->totalItems / $this->perPage);
    }

    /**
     * True if there is a page before the current one.
     */
    public function hasPrev(): bool
    {
        return $this->currentPage > 1;
    }

    /**
     * True if there is a page after the current one.
     */
    public function hasNext(): bool
    {
        return $this->currentPage < $this->totalPages();
    }

    public function currentPage(): int  { return $this->currentPage; }
    public function perPage(): int      { return $this->perPage; }
    public function totalItems(): int   { return $this->totalItems; }
    public function prevPage(): int     { return max(1, $this->currentPage - 1); }
    public function nextPage(): int     { return min($this->totalPages(), $this->currentPage + 1); }
}
