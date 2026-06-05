<?php
require_once __DIR__ . '/../../core/Auth.php';
Auth::requireRole('admin');
require_once __DIR__ . '/../../core/helpers.php';
$pageTitle = 'Manage Doctors';
require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/navbar.php';
require_once __DIR__ . '/../partials/sidebar.php';
?>

<div class="content-wrapper">
  <div class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6"><h1 class="m-0">Manage Doctors</h1></div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="index.php?page=dashboard">Dashboard</a></li>
            <li class="breadcrumb-item active">Doctors</li>
          </ol>
        </div>
      </div>
    </div>
  </div>

  <section class="content">
    <div class="container-fluid">
      <?php require_once __DIR__ . '/../partials/alerts.php'; ?>

      <div class="d-flex justify-content-between mb-3">
        <a href="index.php?page=doctors&action=specializations" class="btn btn-outline-info btn-sm">
          <i class="fas fa-stethoscope mr-1"></i> Manage Specializations
        </a>
        <a href="index.php?page=users&action=create" class="btn btn-success btn-sm">
          <i class="fas fa-user-md mr-1"></i> Add Doctor
        </a>
      </div>

      <div class="card">
        <div class="card-body p-0">
          <table class="table table-hover table-striped mb-0">
            <thead class="thead-light">
              <tr>
                <th>Name</th><th>Specialization</th><th>Fee</th><th>Available Days</th><th>Status</th><th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($doctorsList)): ?>
                <tr><td colspan="6" class="text-center text-muted py-5">No doctors registered.</td></tr>
              <?php else: ?>
                <?php foreach ($doctorsList as $d): ?>
                <tr>
                  <td>
                    <strong>Dr. <?= sanitize($d['name']) ?></strong>
                    <small class="text-muted d-block"><?= sanitize($d['email']) ?></small>
                  </td>
                  <td><?= sanitize($d['specialization_name']) ?></td>
                  <td>$<?= number_format((float)$d['consultation_fee'], 2) ?></td>
                  <td>
                    <?php foreach (explode(',', $d['available_days']) as $day): ?>
                      <span class="badge badge-secondary mr-1"><?= sanitize(trim($day)) ?></span>
                    <?php endforeach; ?>
                  </td>
                  <td>
                    <?php if ($d['is_active']): ?>
                      <span class="badge badge-success">Active</span>
                    <?php else: ?>
                      <span class="badge badge-danger">Suspended</span>
                    <?php endif; ?>
                  </td>
                  <td>
                    <a href="index.php?page=doctors&action=edit&id=<?= (int)$d['id'] ?>"
                       class="btn btn-xs btn-outline-primary">
                      <i class="fas fa-edit"></i> Edit
                    </a>
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
