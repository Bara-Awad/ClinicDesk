<?php
/**
 * ClinicDesk - Global Helper Functions
 */

/**
 * Redirects to a URL and terminates execution.
 *
 * @param string $url  Relative or absolute URL.
 */
function redirect(string $url): never
{
    header('Location: ' . $url);
    exit();
}

/**
 * Sanitizes a string for safe output in HTML لمنع هجمات XSS
 * Always use this when echoing user-supplied data.
 */

function sanitize(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

/**
 * Stores a flash message in the session.
 * Rendered once by views/partials/alerts.php, then deleted.
 *
 * @param string $type     نوع الرسالة: Bootstrap/AdminLTE colour: 'success'|'error'|'warning'|'info'.
 * @param string $message  نص الرسالة: Human-readable message.
 */
function flashMessage(string $type, string $message): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

/**
 * Formats a MySQL DATE string (YYYY-MM-DD) to a human-readable form.
 *
 * @param string $date  e.g. "2025-06-15"
 * @return string       e.g. "15 Jun 2025"
 */
function formatDate(string $date): string
{
    if (empty($date)) return '—';
    return date('d M Y', strtotime($date));
}

/**
 * Formats a MySQL TIME string (HH:MM:SS) to 12-hour format.
 *
 * @param string $time  e.g. "09:30:00"
 * @return string       e.g. "09:30 AM"
 */
function formatTime(string $time): string
{
    if (empty($time)) return '—';
    return date('h:i A', strtotime($time));
}

/**
 * Returns the current page number from $_GET, clamped to >= 1  يعيد رقم الصفحة الحالي مع ضمان انه اكبر او يساوي 1
 *
 * @param string $param  Query-string key (default 'page_num').
 */
function currentPageNum(string $param = 'page_num'): int
{
    return max(1, (int) ($_GET[$param] ?? 1));
}

/**
 * Returns an AdminLTE badge class for an appointment status يعيد لون الشارة حسب حالة الموعد
 */
function statusBadge(string $status): string
{
    return match ($status) {
        'pending'   => 'badge-warning',
        'confirmed' => 'badge-info',
        'completed' => 'badge-success',
        'cancelled' => 'badge-danger',
        default     => 'badge-secondary',
    };
}

/**
 * Returns an AdminLTE small-box colour for an appointment status يعيد لون الصندوق حسب حالة الموعد
 */
function statusColor(string $status): string
{
    return match ($status) {
        'pending'   => 'warning',
        'confirmed' => 'info',
        'completed' => 'success',
        'cancelled' => 'danger',
        default     => 'secondary',
    };
}

/**
 * Truncates a string to $length characters and appends '…'.
 */
function truncate(string $text, int $length = 60): string
{
    if (mb_strlen($text) <= $length) return $text;
    return mb_substr($text, 0, $length) . '…';
}

/**
 * Builds a query string from the current $_GET, overriding given keys.
 * 
 * @param array $overrides  Keys to add/replace.
 * @return string           
 */
function buildQueryString(array $overrides = []): string
{
    $params = array_merge($_GET, $overrides);
    return '?' . http_build_query($params);
}
