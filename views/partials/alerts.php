<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

if (!isset($_SESSION['flash'])) return;

$flash   = $_SESSION['flash'];
$type    = $flash['type']    ?? 'info';
$message = $flash['message'] ?? '';

// Map our type names to Bootstrap / AdminLTE alert classes
$alertClass = match($type) {
    'success' => 'alert-success',
    'error'   => 'alert-danger',
    'warning' => 'alert-warning',
    default   => 'alert-info',
};

$iconClass = match($type) {
    'success' => 'fas fa-check-circle',
    'error'   => 'fas fa-exclamation-circle',
    'warning' => 'fas fa-exclamation-triangle',
    default   => 'fas fa-info-circle',
};

unset($_SESSION['flash']);
?>

<div class="alert <?= $alertClass ?> alert-dismissible fade show mx-3 mt-2" role="alert">
  <i class="<?= $iconClass ?> mr-2"></i>
  <?= e($message) ?>
  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
    <span aria-hidden="true">&times;</span>
  </button>
</div>
