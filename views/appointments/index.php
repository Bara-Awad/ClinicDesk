<?php
require_once __DIR__ . '/../../core/Auth.php';
Auth::requireRole('admin','doctor','patient');
require_once __DIR__ . '/../../core/helpers.php';
require_once __DIR__ . '/../../core/CSRF.php';
$pageTitle = $pageTitle ?? 'Appointments';
require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/navbar.php';
require_once __DIR__ . '/../partials/sidebar.php';

$role = \Auth::role();
?>

<div class="content-wrapper">
  <div class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1 class="m-0">
            <?= $role === 'doctor' ? 'My Schedule' : ($role === 'patient' ? 'My Appointments' : 'All Appointments') ?>
          </h1>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="index.php?page=dashboard">Dashboard</a></li>
            <li class="breadcrumb-item active">Appointments</li>
          </ol>
        </div>
      </div>
    </div>
  </div>

  <section class="content">
    <div class="container-fluid">

      <?php require_once __DIR__ . '/../partials/alerts.php'; ?>

      <!-- Filter Bar -->
      <div class="card card-outline card-secondary">
        <div class="card-body py-2">
          <form method="GET" action="index.php" class="form-inline flex-wrap">
            <input type="hidden" name="page" value="appointments">

            <div class="form-group mr-2 mb-2">
              <select name="status" class="form-control form-control-sm">
                <option value="">All Statuses</option>
                <?php foreach (['pending','confirmed','completed','cancelled'] as $s): ?>
                  <option value="<?= $s ?>" <?= ($_GET['status'] ?? '') === $s ? 'selected' : '' ?>>
                    <?= ucfirst($s) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <?php if ($role === 'admin'): ?>
            <div class="form-group mr-2 mb-2">
              <select name="doctor_id" class="form-control form-control-sm">
                <option value="">All Doctors</option>
                <?php foreach ($doctorsList as $d): ?>
                  <option value="<?= (int)$d['id'] ?>" <?= (int)($_GET['doctor_id'] ?? 0) === (int)$d['id'] ? 'selected' : '' ?>>
                    Dr. <?= e($d['name']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="form-group mr-2 mb-2">
              <input type="text" name="patient_name" class="form-control form-control-sm"
                     placeholder="Patient name…" value="<?= e($_GET['patient_name'] ?? '') ?>">
            </div>
            <?php endif; ?>

            <div class="form-group mr-2 mb-2">
              <input type="date" name="start_date" class="form-control form-control-sm"
                     value="<?= e($_GET['start_date'] ?? '') ?>">
            </div>
            <div class="form-group mr-2 mb-2">
              <input type="date" name="end_date" class="form-control form-control-sm"
                     value="<?= e($_GET['end_date'] ?? '') ?>">
            </div>

            <button type="submit" class="btn btn-sm btn-primary mr-2 mb-2">
              <i class="fas fa-filter mr-1"></i> Filter
            </button>
            <a href="index.php?page=appointments" class="btn btn-sm btn-outline-secondary mb-2">
              <i class="fas fa-times mr-1"></i> Clear
            </a>

            <?php if ($role === 'patient'): ?>
              <a href="index.php?page=appointments&action=book" class="btn btn-sm btn-success ml-auto mb-2">
                <i class="fas fa-plus mr-1"></i> Book Appointment
              </a>
            <?php endif; ?>
          </form>
        </div>
      </div>

      <!-- Appointments Table -->
      <div class="card">
        <div class="card-body p-0">
          <table class="table table-hover table-striped mb-0">
            <thead class="thead-light">
              <tr>
                <?php if ($role !== 'patient'): ?><th>Patient</th><?php endif; ?>
                <?php if ($role !== 'doctor'): ?><th>Doctor</th><?php endif; ?>
                <th>Specialization</th>
                <th>Date</th>
                <th>Time</th>
                <th>Status</th>
                <th>Reason</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($apptList)): ?>
                <tr>
                  <td colspan="8" class="text-center text-muted py-5">
                    <i class="fas fa-calendar-times fa-2x d-block mb-2"></i>
                    No appointments found.
                  </td>
                </tr>
              <?php else: ?>
                <?php foreach ($apptList as $a): ?>
                <tr>
                  <?php if ($role !== 'patient'): ?>
                    <td><?= e($a['patient_name']) ?></td>
                  <?php endif; ?>
                  <?php if ($role !== 'doctor'): ?>
                    <td>Dr. <?= e($a['doctor_name']) ?></td>
                  <?php endif; ?>
                  <td><small class="text-muted"><?= e($a['specialization_name']) ?></small></td>
                  <td><?= e(formatDate($a['appt_date'])) ?></td>
                  <td><?= e(formatTime($a['appt_time'])) ?></td>
                  <td>
                    <span class="badge <?= statusBadge($a['status']) ?>">
                      <?= e(ucfirst($a['status'])) ?>
                    </span>
                  </td>
                  <td><small><?= e(truncate($a['reason'] ?? '—', 40)) ?></small></td>
                  <td>
                    <a href="index.php?page=appointments&action=show&id=<?= (int)$a['id'] ?>"
                       class="btn btn-xs btn-outline-primary" title="View">
                      <i class="fas fa-eye"></i>
                    </a>

                    <?php if ($role === 'patient' && $a['status'] === 'pending'): ?>
                      <form method="POST" action="index.php?page=appointments&action=cancel"
                            class="d-inline" onsubmit="return confirm('Cancel this appointment?')">
                        <?= \CSRF::field() ?>
                        <input type="hidden" name="appointment_id" value="<?= (int)$a['id'] ?>">
                        <button type="submit" class="btn btn-xs btn-outline-danger" title="Cancel">
                          <i class="fas fa-times"></i>
                        </button>
                      </form>
                    <?php endif; ?>

                    <?php if ($role === 'patient' && $a['status'] === 'completed' && $a['prescription_id']): ?>
                      <a href="index.php?page=prescriptions&action=download&id=<?= (int)$a['prescription_id'] ?>"
                         class="btn btn-xs btn-outline-info" title="Download Prescription">
                        <i class="fas fa-file-pdf"></i>
                      </a>
                    <?php endif; ?>

                    <?php if ($role === 'doctor'): ?>
                      <?php if ($a['status'] === 'completed' && !$a['prescription_id']): ?>
                        <a href="index.php?page=prescriptions&action=create&appointment_id=<?= (int)$a['id'] ?>"
                           class="btn btn-xs btn-outline-success" title="Add Prescription">
                          <i class="fas fa-prescription"></i>
                        </a>
                      <?php endif; ?>
                    <?php endif; ?>
                  </td>
                </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>

        <?php if (isset($paginator)): ?>
          <div class="card-footer">
            <?php require_once __DIR__ . '/../partials/pagination.php'; ?>
          </div>
        <?php endif; ?>
      </div>

    </div>
  </section>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
