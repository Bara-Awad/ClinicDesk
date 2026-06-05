<?php
require_once __DIR__ . '/../../core/Auth.php';
Auth::requireRole('doctor');
require_once __DIR__ . '/../../core/helpers.php';
$pageTitle = $pageTitle ?? 'Doctor Dashboard';
require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/navbar.php';
require_once __DIR__ . '/../partials/sidebar.php';
?>

<div class="content-wrapper">
  <div class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1 class="m-0">My Dashboard <small class="text-muted">Doctor View</small></h1>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item active">Dashboard</li>
          </ol>
        </div>
      </div>
    </div>
  </div>

  <section class="content">
    <div class="container-fluid">

      <?php require_once __DIR__ . '/../partials/alerts.php'; ?>

      <!-- Stats -->
      <div class="row">
        <div class="col-lg-3 col-6">
          <div class="small-box bg-info">
            <div class="inner">
              <h3><?= count($stats['today']) ?></h3>
              <p>Today's Appointments</p>
            </div>
            <div class="icon"><i class="fas fa-calendar-day"></i></div>
          </div>
        </div>
        <div class="col-lg-3 col-6">
          <div class="small-box bg-primary">
            <div class="inner">
              <h3><?= (int) $stats['month_total'] ?></h3>
              <p>This Month</p>
            </div>
            <div class="icon"><i class="fas fa-calendar-alt"></i></div>
          </div>
        </div>
        <div class="col-lg-3 col-6">
          <div class="small-box bg-warning">
            <div class="inner">
              <h3><?= (int) ($stats['week_status']['pending'] ?? 0) ?></h3>
              <p>Pending This Week</p>
            </div>
            <div class="icon"><i class="fas fa-hourglass-half"></i></div>
          </div>
        </div>
        <div class="col-lg-3 col-6">
          <div class="small-box bg-success">
            <div class="inner">
              <h3><?= (int) ($stats['week_status']['completed'] ?? 0) ?></h3>
              <p>Completed This Week</p>
            </div>
            <div class="icon"><i class="fas fa-check-circle"></i></div>
          </div>
        </div>
      </div>

      <!-- Today's Appointments -->
      <div class="row">
        <div class="col-12">
          <div class="card border-top border-primary border-3">
            <div class="card-header">
              <h3 class="card-title text-primary">
                <i class="fas fa-sun mr-2"></i>Today's Schedule — <?= date('l, d M Y') ?>
              </h3>
              <div class="card-tools">
                <a href="index.php?page=appointments" class="btn btn-sm btn-outline-primary">Full Schedule</a>
              </div>
            </div>
            <div class="card-body p-0">
              <?php if (empty($stats['today'])): ?>
                <div class="text-center py-5 text-muted">
                  <i class="fas fa-calendar-times fa-3x mb-3 d-block"></i>
                  No appointments scheduled for today.
                </div>
              <?php else: ?>
                <table class="table table-hover mb-0">
                  <thead class="thead-light">
                    <tr>
                      <th>Time</th><th>Patient</th><th>Reason</th><th>Status</th><th>Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($stats['today'] as $a): ?>
                    <tr>
                      <td><strong><?= sanitize(formatTime($a['appt_time'])) ?></strong></td>
                      <td><?= sanitize($a['patient_name']) ?></td>
                      <td><small><?= sanitize(truncate($a['reason'] ?? '—', 50)) ?></small></td>
                      <td>
                        <span class="badge <?= statusBadge($a['status']) ?>">
                          <?= sanitize(ucfirst($a['status'])) ?>
                        </span>
                      </td>
                      <td>
                        <a href="index.php?page=appointments&action=show&id=<?= (int)$a['id'] ?>"
                           class="btn btn-xs btn-outline-primary">
                          <i class="fas fa-eye"></i> View
                        </a>
                      </td>
                    </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>

    </div>
  </section>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
