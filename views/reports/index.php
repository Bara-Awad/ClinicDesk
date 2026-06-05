<?php
require_once __DIR__ . '/../../core/Auth.php';
Auth::requireRole('admin');
require_once __DIR__ . '/../../core/helpers.php';
require_once __DIR__ . '/../../core/CSRF.php';
$pageTitle = 'Appointment Reports';
require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/navbar.php';
require_once __DIR__ . '/../partials/sidebar.php';
?>

<div class="content-wrapper">
  <div class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6"><h1 class="m-0">Reports</h1></div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="index.php?page=dashboard">Dashboard</a></li>
            <li class="breadcrumb-item active">Reports</li>
          </ol>
        </div>
      </div>
    </div>
  </div>

  <section class="content">
    <div class="container-fluid">
      <?php require_once __DIR__ . '/../partials/alerts.php'; ?>

      <!-- Filter Form -->
      <div class="card card-outline card-primary">
        <div class="card-header">
          <h3 class="card-title"><i class="fas fa-filter mr-2"></i>Report Filters</h3>
        </div>
        <form method="GET" action="index.php">
          <input type="hidden" name="page" value="reports">
          <div class="card-body">
            <?php if (!empty($errors)): ?>
              <div class="alert alert-danger">
                <?php foreach ($errors as $e): ?><div><?= sanitize($e) ?></div><?php endforeach; ?>
              </div>
            <?php endif; ?>
            <div class="row">
              <div class="col-md-3">
                <div class="form-group">
                  <label>Start Date <span class="text-danger">*</span></label>
                  <input type="date" name="start_date" class="form-control"
                         value="<?= sanitize($_GET['start_date'] ?? '') ?>" required>
                </div>
              </div>
              <div class="col-md-3">
                <div class="form-group">
                  <label>End Date <span class="text-danger">*</span></label>
                  <input type="date" name="end_date" class="form-control"
                         value="<?= sanitize($_GET['end_date'] ?? '') ?>" required>
                </div>
              </div>
              <div class="col-md-3">
                <div class="form-group">
                  <label>Doctor (optional)</label>
                  <select name="doctor_id" class="form-control">
                    <option value="">All Doctors</option>
                    <?php foreach ($doctorsList as $d): ?>
                      <option value="<?= (int)$d['id'] ?>"
                        <?= (int)($_GET['doctor_id'] ?? 0) === (int)$d['id'] ? 'selected' : '' ?>>
                        Dr. <?= sanitize($d['name']) ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>
              <div class="col-md-3">
                <div class="form-group">
                  <label>Status (optional)</label>
                  <select name="status" class="form-control">
                    <option value="">All</option>
                    <?php foreach (['pending','confirmed','completed','cancelled'] as $s): ?>
                      <option value="<?= $s ?>" <?= ($_GET['status'] ?? '') === $s ? 'selected' : '' ?>>
                        <?= ucfirst($s) ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>
            </div>
          </div>
          <div class="card-footer d-flex gap-2">
            <button type="submit" class="btn btn-primary">
              <i class="fas fa-search mr-1"></i> Generate Report
            </button>
            <?php if ($filtered && empty($errors) && !empty($results)): ?>
              <?php
                $csvParams = array_merge($_GET, ['export' => 'csv']);
                $csvUrl = 'index.php?' . http_build_query($csvParams);
              ?>
              <a href="<?= sanitize($csvUrl) ?>" class="btn btn-success ml-2">
                <i class="fas fa-file-csv mr-1"></i> Export CSV
              </a>
            <?php endif; ?>
          </div>
        </form>
      </div>

      <!-- Results -->
      <?php if ($filtered && empty($errors)): ?>
      <div class="card">
        <div class="card-header">
          <h3 class="card-title">
            <i class="fas fa-table mr-2"></i>
            Results — <?= (int)($summary['total'] ?? 0) ?> appointment(s) found
          </h3>
        </div>
        <div class="card-body p-0">
          <?php if (empty($results)): ?>
            <div class="text-center py-5 text-muted">No appointments match the selected filters.</div>
          <?php else: ?>
            <table class="table table-hover table-sm mb-0">
              <thead class="thead-light">
                <tr>
                  <th>#</th><th>Patient</th><th>Doctor</th><th>Specialization</th>
                  <th>Date</th><th>Time</th><th>Status</th><th>Reason</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($results as $r): ?>
                <tr>
                  <td><?= (int)$r['id'] ?></td>
                  <td><?= sanitize($r['patient_name']) ?></td>
                  <td>Dr. <?= sanitize($r['doctor_name']) ?></td>
                  <td><small><?= sanitize($r['specialization_name']) ?></small></td>
                  <td><?= sanitize(formatDate($r['appt_date'])) ?></td>
                  <td><?= sanitize(formatTime($r['appt_time'])) ?></td>
                  <td>
                    <span class="badge <?= statusBadge($r['status']) ?>">
                      <?= sanitize(ucfirst($r['status'])) ?>
                    </span>
                  </td>
                  <td><small><?= sanitize(truncate($r['reason'] ?? '—', 40)) ?></small></td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          <?php endif; ?>
        </div>

        <!-- Summary row -->
        <?php if (!empty($summary['by_status'])): ?>
        <div class="card-footer">
          <strong>Summary:</strong>
          <?php foreach ($summary['by_status'] as $st => $cnt): ?>
            <span class="badge <?= statusBadge($st) ?> mr-2">
              <?= ucfirst($st) ?>: <?= (int)$cnt ?>
            </span>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
      </div>
      <?php endif; ?>

    </div>
  </section>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
