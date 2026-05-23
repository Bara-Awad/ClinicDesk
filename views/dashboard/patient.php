<?php
require_once __DIR__ . '/../../core/Auth.php';
Auth::requireRole('patient');
require_once __DIR__ . '/../../core/helpers.php';
$pageTitle = $pageTitle ?? 'My Dashboard';
require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/navbar.php';
require_once __DIR__ . '/../partials/sidebar.php';

$nextAppt = $stats['upcoming'][0] ?? null;
?>

<div class="content-wrapper">
  <div class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1 class="m-0">My Health Dashboard</h1>
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

      <!-- Next Appointment Banner -->
      <?php if ($nextAppt): ?>
      <div class="row">
        <div class="col-12">
          <div class="alert alert-info border-0 shadow-sm d-flex align-items-center" style="border-radius:10px">
            <i class="fas fa-calendar-check fa-2x mr-3"></i>
            <div>
              <strong>Next Appointment:</strong>
              Dr. <?= e($nextAppt['doctor_name']) ?> (<?= e($nextAppt['specialization_name']) ?>)
              &mdash; <?= e(formatDate($nextAppt['appt_date'])) ?> at <?= e(formatTime($nextAppt['appt_time'])) ?>
              <span class="badge <?= statusBadge($nextAppt['status']) ?> ml-2">
                <?= e(ucfirst($nextAppt['status'])) ?>
              </span>
            </div>
            <a href="index.php?page=appointments&action=show&id=<?= (int)$nextAppt['id'] ?>"
               class="btn btn-sm btn-light ml-auto">View</a>
          </div>
        </div>
      </div>
      <?php endif; ?>

      <!-- Stats -->
      <div class="row">
        <div class="col-lg-4 col-6">
          <div class="small-box bg-warning">
            <div class="inner">
              <h3><?= (int) $stats['active_count'] ?></h3>
              <p>Active Appointments</p>
            </div>
            <div class="icon"><i class="fas fa-calendar-alt"></i></div>
            <a href="index.php?page=appointments" class="small-box-footer">
              View <i class="fas fa-arrow-circle-right"></i>
            </a>
          </div>
        </div>

        <div class="col-lg-4 col-6">
          <div class="small-box bg-success">
            <div class="inner">
              <h3><?= (int) $stats['completed'] ?></h3>
              <p>Completed Appointments</p>
            </div>
            <div class="icon"><i class="fas fa-check-circle"></i></div>
          </div>
        </div>

        <div class="col-lg-4 col-6">
          <div class="small-box bg-info">
            <div class="inner">
              <h3><?= count($stats['prescriptions']) ?></h3>
              <p>Prescriptions</p>
            </div>
            <div class="icon"><i class="fas fa-prescription-bottle-alt"></i></div>
            <a href="index.php?page=prescriptions" class="small-box-footer">
              View <i class="fas fa-arrow-circle-right"></i>
            </a>
          </div>
        </div>
      </div>

      <!-- Upcoming appointments list -->
      <div class="row">
        <div class="col-lg-8">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title"><i class="fas fa-calendar mr-2 text-primary"></i>Upcoming Appointments</h3>
              <div class="card-tools">
                <a href="index.php?page=appointments&action=book" class="btn btn-sm btn-success">
                  <i class="fas fa-plus mr-1"></i> Book New
                </a>
              </div>
            </div>
            <div class="card-body p-0">
              <?php if (empty($stats['upcoming'])): ?>
                <div class="text-center py-5 text-muted">
                  <i class="fas fa-calendar-plus fa-3x mb-3 d-block"></i>
                  No upcoming appointments.
                  <div class="mt-2">
                    <a href="index.php?page=appointments&action=book" class="btn btn-primary btn-sm">Book Now</a>
                  </div>
                </div>
              <?php else: ?>
                <table class="table table-hover mb-0">
                  <thead class="thead-light">
                    <tr><th>Doctor</th><th>Date</th><th>Time</th><th>Status</th></tr>
                  </thead>
                  <tbody>
                    <?php foreach ($stats['upcoming'] as $a): ?>
                    <tr>
                      <td>
                        <strong>Dr. <?= e($a['doctor_name']) ?></strong>
                        <small class="text-muted d-block"><?= e($a['specialization_name']) ?></small>
                      </td>
                      <td><?= e(formatDate($a['appt_date'])) ?></td>
                      <td><?= e(formatTime($a['appt_time'])) ?></td>
                      <td><span class="badge <?= statusBadge($a['status']) ?>"><?= ucfirst(e($a['status'])) ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <!-- Recent prescriptions -->
        <div class="col-lg-4">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title"><i class="fas fa-pills mr-2 text-info"></i>Recent Prescriptions</h3>
            </div>
            <div class="card-body p-0">
              <?php if (empty($stats['prescriptions'])): ?>
                <div class="text-center py-4 text-muted small">No prescriptions yet.</div>
              <?php else: ?>
                <ul class="list-group list-group-flush">
                  <?php foreach (array_slice($stats['prescriptions'], 0, 5) as $p): ?>
                  <li class="list-group-item">
                    <div class="d-flex justify-content-between">
                      <div>
                        <strong class="small">Dr. <?= e($p['doctor_name']) ?></strong>
                        <div class="text-muted small"><?= e(formatDate($p['appt_date'])) ?></div>
                        <div class="text-muted small"><?= e(truncate($p['diagnosis'], 40)) ?></div>
                      </div>
                      <?php if ($p['file_path']): ?>
                      <a href="index.php?page=prescriptions&action=download&id=<?= (int)$p['id'] ?>"
                         class="btn btn-xs btn-outline-info align-self-center">
                        <i class="fas fa-download"></i>
                      </a>
                      <?php endif; ?>
                    </div>
                  </li>
                  <?php endforeach; ?>
                </ul>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>

    </div>
  </section>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
