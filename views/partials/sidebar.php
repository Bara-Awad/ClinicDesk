<?php
require_once __DIR__ . '/../../core/Auth.php';
$currentUser = Auth::currentUser();
$role        = $currentUser['role'];
$currentPage = $_GET['page']   ?? 'dashboard';
$currentAct  = $_GET['action'] ?? '';

/**
 * Returns 'active' if the given page matches the current request.
 */
function sidebarActive(string $page, string $action = ''): string {
    global $currentPage, $currentAct;
    if ($currentPage !== $page) return '';
    if ($action && $currentAct !== $action) return '';
    return 'active';
}
?>
<!-- Main Sidebar -->
<aside class="main-sidebar sidebar-dark-primary elevation-4">
  <!-- Brand logo -->
  <a href="index.php?page=dashboard" class="brand-link">
    <i class="fas fa-hospital-user ml-3 mr-2 text-white"></i>
    <span class="brand-text font-weight-bold">ClinicDesk</span>
  </a>

  <!-- Sidebar user info -->
  <div class="sidebar">
    <div class="user-panel mt-3 pb-3 mb-3 d-flex">
      <div class="image">
        <div class="img-circle bg-white text-center d-flex align-items-center justify-content-center"
             style="width:35px;height:35px;border-radius:50%;overflow:hidden">
          <?php if (!empty($currentUser['avatar']) && file_exists($currentUser['avatar'])): ?>
            <img src="<?= BASE_URL ?>/<?= e($currentUser['avatar']) ?>" alt="Avatar"
                 style="width:100%;height:100%;object-fit:cover">
          <?php else: ?>
            <i class="fas fa-user text-primary"></i>
          <?php endif; ?>
        </div>
      </div>
      <div class="info">
        <a href="index.php?page=profile" class="d-block text-white font-weight-bold">
          <?= e($currentUser['name']) ?>
        </a>
        <small class="text-muted text-capitalize"><?= e($role) ?></small>
      </div>
    </div>

    <!-- Navigation -->
    <nav class="mt-2">
      <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">

        <!-- ====== COMMON ====== -->
        <li class="nav-item">
          <a href="index.php?page=dashboard" class="nav-link <?= sidebarActive('dashboard') ?>">
            <i class="nav-icon fas fa-tachometer-alt"></i>
            <p>Dashboard</p>
          </a>
        </li>

        <!-- ====== ADMIN ====== -->
        <?php if ($role === 'admin'): ?>
          <li class="nav-header">ADMINISTRATION</li>

          <li class="nav-item">
            <a href="index.php?page=users" class="nav-link <?= sidebarActive('users') ?>">
              <i class="nav-icon fas fa-users"></i>
              <p>Users</p>
            </a>
          </li>

          <li class="nav-item">
            <a href="index.php?page=doctors" class="nav-link <?= sidebarActive('doctors') ?>">
              <i class="nav-icon fas fa-user-md"></i>
              <p>Doctors</p>
            </a>
          </li>

          <li class="nav-item">
            <a href="index.php?page=doctors&action=specializations" class="nav-link <?= sidebarActive('doctors','specializations') ?>">
              <i class="nav-icon fas fa-stethoscope"></i>
              <p>Specializations</p>
            </a>
          </li>

          <li class="nav-header">OPERATIONS</li>

          <li class="nav-item">
            <a href="index.php?page=appointments" class="nav-link <?= sidebarActive('appointments') ?>">
              <i class="nav-icon fas fa-calendar-check"></i>
              <p>Appointments</p>
            </a>
          </li>

          <li class="nav-item">
            <a href="index.php?page=reports" class="nav-link <?= sidebarActive('reports') ?>">
              <i class="nav-icon fas fa-chart-bar"></i>
              <p>Reports</p>
            </a>
          </li>
        <?php endif; ?>

        <!-- ====== DOCTOR ====== -->
        <?php if ($role === 'doctor'): ?>
          <li class="nav-header">CLINIC</li>

          <li class="nav-item">
            <a href="index.php?page=appointments" class="nav-link <?= sidebarActive('appointments') ?>">
              <i class="nav-icon fas fa-calendar-alt"></i>
              <p>My Schedule</p>
            </a>
          </li>

          <li class="nav-item">
            <a href="index.php?page=doctors&action=edit" class="nav-link <?= sidebarActive('doctors','edit') ?>">
              <i class="nav-icon fas fa-id-card"></i>
              <p>My Profile</p>
            </a>
          </li>
        <?php endif; ?>

        <!-- ====== PATIENT ====== -->
        <?php if ($role === 'patient'): ?>
          <li class="nav-header">MY HEALTH</li>

          <li class="nav-item">
            <a href="index.php?page=appointments&action=book" class="nav-link <?= sidebarActive('appointments','book') ?>">
              <i class="nav-icon fas fa-plus-circle"></i>
              <p>Book Appointment</p>
            </a>
          </li>

          <li class="nav-item">
            <a href="index.php?page=appointments" class="nav-link <?= sidebarActive('appointments') ?>">
              <i class="nav-icon fas fa-calendar-check"></i>
              <p>My Appointments</p>
            </a>
          </li>

          <li class="nav-item">
            <a href="index.php?page=prescriptions" class="nav-link <?= sidebarActive('prescriptions') ?>">
              <i class="nav-icon fas fa-prescription-bottle-alt"></i>
              <p>My Prescriptions</p>
            </a>
          </li>
        <?php endif; ?>

      </ul>
    </nav>
  </div>
</aside>
