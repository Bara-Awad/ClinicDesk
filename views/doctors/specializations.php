<?php
require_once __DIR__ . '/../../core/Auth.php';
Auth::requireRole('admin');
require_once __DIR__ . '/../../core/helpers.php';
require_once __DIR__ . '/../../core/CSRF.php';
$pageTitle = 'Manage Specializations';
require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/navbar.php';
require_once __DIR__ . '/../partials/sidebar.php';
?>

<div class="content-wrapper">
  <div class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6"><h1 class="m-0">Specializations</h1></div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="index.php?page=doctors">Doctors</a></li>
            <li class="breadcrumb-item active">Specializations</li>
          </ol>
        </div>
      </div>
    </div>
  </div>

  <section class="content">
    <div class="container-fluid">
      <?php require_once __DIR__ . '/../partials/alerts.php'; ?>

      <div class="row">
        <!-- Add form -->
        <div class="col-lg-4">
          <div class="card card-outline card-primary">
            <div class="card-header">
              <h3 class="card-title"><i class="fas fa-plus mr-2"></i>Add Specialization</h3>
            </div>
            <form method="POST" action="index.php?page=doctors&action=add_specialization">
              <?= \CSRF::field() ?>
              <div class="card-body">
                <div class="form-group">
                  <label>Name <span class="text-danger">*</span></label>
                  <input type="text" name="name" class="form-control"
                         placeholder="e.g. Cardiology" required minlength="2">
                </div>
              </div>
              <div class="card-footer text-right">
                <button type="submit" class="btn btn-primary">
                  <i class="fas fa-save mr-1"></i> Add
                </button>
              </div>
            </form>
          </div>
        </div>

        <!-- List -->
        <div class="col-lg-8">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title"><i class="fas fa-list mr-2"></i>All Specializations</h3>
            </div>
            <div class="card-body p-0">
              <table class="table table-hover mb-0">
                <thead class="thead-light">
                  <tr><th>#</th><th>Name</th><th>Action</th></tr>
                </thead>
                <tbody>
                  <?php if (empty($specsList)): ?>
                    <tr><td colspan="3" class="text-center text-muted py-4">No specializations yet.</td></tr>
                  <?php else: ?>
                    <?php foreach ($specsList as $s): ?>
                    <tr>
                      <td><?= (int)$s['id'] ?></td>
                      <td><?= sanitize($s['name']) ?></td>
                      <td>
                        <form method="POST" action="index.php?page=doctors&action=delete_specialization"
                              class="d-inline"
                              onsubmit="return confirm('Delete specialization \'<?= sanitize(addslashes($s['name'])) ?>\'?')">
                          <?= \CSRF::field() ?>
                          <input type="hidden" name="spec_id" value="<?= (int)$s['id'] ?>">
                          <button type="submit" class="btn btn-xs btn-outline-danger">
                            <i class="fas fa-trash"></i> Delete
                          </button>
                        </form>
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

    </div>
  </section>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
