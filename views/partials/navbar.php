<?php
require_once __DIR__ . '/../../core/Auth.php';
$currentUser = Auth::currentUser();
?>
<!-- Navbar -->
<nav class="main-header navbar navbar-expand navbar-white navbar-light">
  <!-- Left side: toggle + breadcrumb -->
  <ul class="navbar-nav">
    <li class="nav-item">
      <a class="nav-link" data-widget="pushmenu" href="#" role="button">
        <i class="fas fa-bars"></i>
      </a>
    </li>
    <li class="nav-item d-none d-sm-inline-block">
      <a href="index.php?page=dashboard" class="nav-link">
        <i class="fas fa-home mr-1"></i> Dashboard
      </a>
    </li>
  </ul>

  <!-- Right side: user dropdown + logout -->
  <ul class="navbar-nav ml-auto">
    <!-- Role badge -->
    <li class="nav-item d-flex align-items-center mr-2">
      <span class="badge badge-<?=
        $currentUser['role'] === 'admin'   ? 'danger' :
        ($currentUser['role'] === 'doctor' ? 'info'   : 'success')
      ?> p-2 text-uppercase">
        <?= sanitize($currentUser['role']) ?>
      </span>
    </li>

    <!-- User dropdown -->
    <li class="nav-item dropdown">
      <a class="nav-link" data-toggle="dropdown" href="#">
        <i class="far fa-user-circle fa-lg mr-1"></i>
        <span class="d-none d-sm-inline"><?= sanitize($currentUser['name']) ?></span>
        <i class="fas fa-caret-down ml-1"></i>
      </a>
      <div class="dropdown-menu dropdown-menu-right shadow-sm">
        <span class="dropdown-item-text text-muted small">
          <?= sanitize($currentUser['email']) ?>
        </span>
        <div class="dropdown-divider"></div>
        <a href="index.php?page=profile" class="dropdown-item">
          <i class="fas fa-user-edit mr-2"></i> Edit Profile
        </a>
        <div class="dropdown-divider"></div>
        <!-- Logout must be a POST with CSRF — not a plain link -->
        <form method="POST" action="index.php?page=logout" class="mb-0">
          <?= \CSRF::field() ?>
          <button type="submit" class="dropdown-item text-danger">
            <i class="fas fa-sign-out-alt mr-2"></i> Logout
          </button>
        </form>
      </div>
    </li>
  </ul>
</nav>
