<?php
require_once __DIR__ . '/../../core/Auth.php';
Auth::requireRole('admin');
require_once __DIR__ . '/../../core/helpers.php';
require_once __DIR__ . '/../../core/CSRF.php';
$pageTitle = $pageTitle ?? 'Admin Dashboard';
require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/navbar.php';
require_once __DIR__ . '/../partials/sidebar.php';

$chartJson = json_encode(array_values($stats['chart_data']));
$chartLabels = json_encode(array_column($stats['chart_data'], 'appt_date'));

// Prepare chart data as labelled dataset
$chartRows = $stats['chart_data'];
?>

<div class="content-wrapper">
  <!-- Page header -->
  <div class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1 class="m-0">Dashboard <small class="text-muted fs-6">Admin Overview</small></h1>
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

      <!-- ── Stat Cards Row 1: Users ── -->
      <div class="row">
        <div class="col-lg-3 col-6">
          <div class="small-box bg-info">
            <div class="inner">
              <h3><?= (int) ($stats['users_by_role']['doctor'] ?? 0) ?></h3>
              <p>Doctors</p>
            </div>
            <div class="icon"><i class="fas fa-user-md"></i></div>
            <a href="index.php?page=users&role=doctor" class="small-box-footer">
              View Doctors <i class="fas fa-arrow-circle-right"></i>
            </a>
          </div>
        </div>

        <div class="col-lg-3 col-6">
          <div class="small-box bg-success">
            <div class="inner">
              <h3><?= (int) ($stats['users_by_role']['patient'] ?? 0) ?></h3>
              <p>Patients</p>
            </div>
            <div class="icon"><i class="fas fa-users"></i></div>
            <a href="index.php?page=users&role=patient" class="small-box-footer">
              View Patients <i class="fas fa-arrow-circle-right"></i>
            </a>
          </div>
        </div>

        <div class="col-lg-3 col-6">
          <div class="small-box bg-warning">
            <div class="inner">
              <h3><?= (int) $stats['appointments_today'] ?></h3>
              <p>Appointments Today</p>
            </div>
            <div class="icon"><i class="fas fa-calendar-day"></i></div>
            <a href="index.php?page=appointments" class="small-box-footer">
              View All <i class="fas fa-arrow-circle-right"></i>
            </a>
          </div>
        </div>

        <div class="col-lg-3 col-6">
          <div class="small-box bg-danger">
            <div class="inner">
              <h3><?= (int) ($stats['week_by_status']['pending'] ?? 0) ?></h3>
              <p>Pending This Week</p>
            </div>
            <div class="icon"><i class="fas fa-clock"></i></div>
            <a href="index.php?page=appointments&status=pending" class="small-box-footer">
              View Pending <i class="fas fa-arrow-circle-right"></i>
            </a>
          </div>
        </div>
      </div>

      <!-- ── Row 2: Chart + Weekly Status ── -->
      <div class="row">
        <!-- Chart: Appointments last 14 days -->
        <div class="col-lg-8">
          <div class="card">
            <div class="card-header border-0">
              <h3 class="card-title"><i class="fas fa-chart-bar mr-2 text-primary"></i>Appointments – Last 14 Days</h3>
            </div>
            <div class="card-body">
              <canvas id="appointmentsChart" style="height:220px"></canvas>
            </div>
          </div>
        </div>

        <!-- Weekly status summary -->
        <div class="col-lg-4">
          <div class="card">
            <div class="card-header border-0">
              <h3 class="card-title"><i class="fas fa-list mr-2 text-primary"></i>This Week by Status</h3>
            </div>
            <div class="card-body p-0">
              <ul class="list-group list-group-flush">
                <?php foreach (['pending','confirmed','completed','cancelled'] as $s): ?>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                  <span class="text-capitalize"><?= e($s) ?></span>
                  <span class="badge <?= statusBadge($s) ?> badge-pill">
                    <?= (int) ($stats['week_by_status'][$s] ?? 0) ?>
                  </span>
                </li>
                <?php endforeach; ?>
              </ul>
            </div>
            <div class="card-footer text-center">
              <a href="index.php?page=reports" class="btn btn-sm btn-outline-primary">
                <i class="fas fa-file-csv mr-1"></i> Generate Report
              </a>
            </div>
          </div>
        </div>
      </div>

      <!-- ── Recent Appointments Table ── -->
      <div class="row">
        <div class="col-12">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title"><i class="fas fa-history mr-2 text-primary"></i>Recent Appointments</h3>
              <div class="card-tools">
                <a href="index.php?page=appointments" class="btn btn-sm btn-primary">
                  View All
                </a>
              </div>
            </div>
            <div class="card-body p-0">
              <table class="table table-hover table-striped mb-0">
                <thead class="thead-light">
                  <tr>
                    <th>Patient</th>
                    <th>Doctor</th>
                    <th>Specialization</th>
                    <th>Date & Time</th>
                    <th>Status</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (empty($stats['recent'])): ?>
                    <tr><td colspan="6" class="text-center text-muted py-4">No appointments yet.</td></tr>
                  <?php else: ?>
                    <?php foreach ($stats['recent'] as $a): ?>
                    <tr>
                      <td><?= e($a['patient_name']) ?></td>
                      <td><?= e($a['doctor_name']) ?></td>
                      <td><small class="text-muted"><?= e($a['specialization_name']) ?></small></td>
                      <td>
                        <?= e(formatDate($a['appt_date'])) ?>
                        <small class="text-muted d-block"><?= e(formatTime($a['appt_time'])) ?></small>
                      </td>
                      <td>
                        <span class="badge <?= statusBadge($a['status']) ?>">
                          <?= e(ucfirst($a['status'])) ?>
                        </span>
                      </td>
                      <td>
                        <a href="index.php?page=appointments&action=show&id=<?= (int) $a['id'] ?>"
                           class="btn btn-xs btn-outline-secondary">
                          <i class="fas fa-eye"></i>
                        </a>
                      </td>
                    </tr>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

    </div><!-- /.container-fluid -->
  </section>
</div><!-- /.content-wrapper -->

<?php
// Build chart data
$labels = [];
$values = [];
$dateMap = [];
foreach ($chartRows as $row) {
    $dateMap[$row['appt_date']] = (int) $row['total'];
}
// Fill last 14 days (including zeros)
for ($i = 13; $i >= 0; $i--) {
    $d = date('Y-m-d', strtotime("-$i days"));
    $labels[] = date('M d', strtotime($d));
    $values[] = $dateMap[$d] ?? 0;
}
?>

<?php
$extraScripts = '
<script>
$(function(){
  var ctx = document.getElementById("appointmentsChart");
  if(ctx){
    new Chart(ctx, {
      type: "bar",
      data: {
        labels: ' . json_encode($labels) . ',
        datasets: [{
          label: "Appointments",
          data: ' . json_encode($values) . ',
          backgroundColor: "rgba(13,110,253,.65)",
          borderColor: "rgba(13,110,253,1)",
          borderWidth: 1,
          borderRadius: 4
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
          y: { beginAtZero: true, ticks: { stepSize: 1 } },
          x: { ticks: { maxRotation: 45 } }
        }
      }
    });
  }
});
</script>';

require_once __DIR__ . '/../partials/footer.php';
?>
