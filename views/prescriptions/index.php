<?php
require_once __DIR__ . '/../../core/Auth.php';
Auth::requireRole('patient');
require_once __DIR__ . '/../../core/helpers.php';
$pageTitle = 'My Prescriptions';
require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/navbar.php';
require_once __DIR__ . '/../partials/sidebar.php';
?>

<div class="content-wrapper">
  <div class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6"><h1 class="m-0">My Prescriptions</h1></div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="index.php?page=dashboard">Dashboard</a></li>
            <li class="breadcrumb-item active">Prescriptions</li>
          </ol>
        </div>
      </div>
    </div>
  </div>

  <section class="content">
    <div class="container-fluid">
      <?php require_once __DIR__ . '/../partials/alerts.php'; ?>

      <div class="card">
        <div class="card-header">
          <h3 class="card-title"><i class="fas fa-pills mr-2 text-primary"></i>Prescription History</h3>
        </div>
        <div class="card-body p-0">
          <?php if (empty($prescList)): ?>
            <div class="text-center py-6 text-muted" style="padding:4rem">
              <i class="fas fa-prescription-bottle-alt fa-3x mb-3 d-block"></i>
              No prescriptions found.
            </div>
          <?php else: ?>
            <table class="table table-hover mb-0">
              <thead class="thead-light">
                <tr>
                  <th>Doctor</th>
                  <th>Specialization</th>
                  <th>Date</th>
                  <th>Diagnosis</th>
                  <th>Medications</th>
                  <th>PDF</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($prescList as $p): ?>
                <tr>
                  <td>
                    <strong>Dr. <?= sanitize($p['doctor_name']) ?></strong>
                  </td>
                  <td><small class="text-muted"><?= sanitize($p['specialization_name']) ?></small></td>
                  <td><?= sanitize(formatDate($p['appt_date'])) ?></td>
                  <td>
                    <span data-toggle="tooltip" title="<?= sanitize($p['diagnosis']) ?>">
                      <?= sanitize(truncate($p['diagnosis'], 40)) ?>
                    </span>
                  </td>
                  <td>
                    <span data-toggle="tooltip" title="<?= sanitize($p['medications']) ?>">
                      <?= sanitize(truncate($p['medications'], 40)) ?>
                    </span>
                  </td>
                  <td>
                    <?php if ($p['file_path']): ?>
                      <a href="index.php?page=prescriptions&action=download&id=<?= (int)$p['id'] ?>"
                         class="btn btn-sm btn-outline-success">
                        <i class="fas fa-file-pdf mr-1"></i> Download
                      </a>
                    <?php else: ?>
                      <span class="text-muted small">No file</span>
                    <?php endif; ?>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          <?php endif; ?>
        </div>
      </div>

    </div>
  </section>
</div>

<?php
$extraScripts = '<script>$(function(){ $("[data-toggle=tooltip]").tooltip(); })</script>';
require_once __DIR__ . '/../partials/footer.php';
?>
