<?php
require_once __DIR__ . '/../../core/Auth.php';
Auth::requireRole('admin');
require_once __DIR__ . '/../../core/helpers.php';
require_once __DIR__ . '/../../core/CSRF.php';
$pageTitle = 'Create User';
require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/navbar.php';
require_once __DIR__ . '/../partials/sidebar.php';
?>

<div class="content-wrapper">
  <div class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6"><h1 class="m-0">Create New User</h1></div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="index.php?page=dashboard">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="index.php?page=users">Users</a></li>
            <li class="breadcrumb-item active">Create</li>
          </ol>
        </div>
      </div>
    </div>
  </div>

  <section class="content">
    <div class="container-fluid">
      <?php require_once __DIR__ . '/../partials/alerts.php'; ?>

      <div class="row justify-content-center">
        <div class="col-lg-8">
          <div class="card card-outline card-primary">
            <div class="card-header">
              <h3 class="card-title"><i class="fas fa-user-plus mr-2"></i>User Information</h3>
            </div>
            <form method="POST" action="index.php?page=users&action=store" enctype="multipart/form-data">
              <?= \CSRF::field() ?>
              <div class="card-body">

                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label>Full Name <span class="text-danger">*</span></label>
                      <input type="text" name="name" class="form-control"
                             value="<?= sanitize($_POST['name'] ?? '') ?>" required minlength="2">
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label>Email Address <span class="text-danger">*</span></label>
                      <input type="email" name="email" class="form-control"
                             value="<?= sanitize($_POST['email'] ?? '') ?>" required>
                    </div>
                  </div>
                </div>

                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label>Password <span class="text-danger">*</span></label>
                      <input type="password" name="password" class="form-control"
                             required minlength="8" placeholder="Min. 8 characters">
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label>Phone</label>
                      <input type="text" name="phone" class="form-control"
                             value="<?= sanitize($_POST['phone'] ?? '') ?>">
                    </div>
                  </div>
                </div>

                <div class="form-group">
                  <label>Role <span class="text-danger">*</span></label>
                  <select name="role" id="roleSelect" class="form-control" required>
                    <?php foreach (['patient','doctor','admin'] as $r): ?>
                      <option value="<?= $r ?>" <?= ($_POST['role'] ?? 'patient') === $r ? 'selected' : '' ?>>
                        <?= ucfirst($r) ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>

                <!-- Doctor-only fields (shown/hidden via JS) -->
                <div id="doctorFields" style="display:none">
                  <hr><h5 class="text-primary"><i class="fas fa-user-md mr-2"></i>Doctor Profile</h5>
                  <div class="row">
                    <div class="col-md-6">
                      <div class="form-group">
                        <label>Specialization <span class="text-danger">*</span></label>
                        <select name="specialization_id" class="form-control">
                          <option value="">Select…</option>
                          <?php foreach ($specializations as $s): ?>
                            <option value="<?= (int)$s['id'] ?>"><?= sanitize($s['name']) ?></option>
                          <?php endforeach; ?>
                        </select>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="form-group">
                        <label>Consultation Fee ($)</label>
                        <input type="number" name="consultation_fee" class="form-control"
                               step="0.01" min="0" value="0.00">
                      </div>
                    </div>
                  </div>

                  <div class="form-group">
                    <label>Available Days</label>
                    <div class="d-flex flex-wrap gap-2">
                      <?php foreach (['Sun','Mon','Tue','Wed','Thu','Fri','Sat'] as $day): ?>
                        <div class="custom-control custom-checkbox mr-3">
                          <input type="checkbox" class="custom-control-input"
                                 id="day_<?= $day ?>" name="available_days[]" value="<?= $day ?>"
                                 <?= in_array($day, ['Sun','Mon','Tue','Wed','Thu']) ? 'checked' : '' ?>>
                          <label class="custom-control-label" for="day_<?= $day ?>"><?= $day ?></label>
                        </div>
                      <?php endforeach; ?>
                    </div>
                  </div>

                  <div class="form-group">
                    <label>Bio</label>
                    <textarea name="bio" class="form-control" rows="3"
                              placeholder="Short biography…"><?= sanitize($_POST['bio'] ?? '') ?></textarea>
                  </div>
                </div>

              </div>
              <div class="card-footer d-flex justify-content-between">
                <a href="index.php?page=users" class="btn btn-secondary">
                  <i class="fas fa-arrow-left mr-1"></i> Cancel
                </a>
                <button type="submit" class="btn btn-primary">
                  <i class="fas fa-save mr-1"></i> Create User
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>

    </div>
  </section>
</div>

<?php
$extraScripts = '
<script>
  const roleSelect = document.getElementById("roleSelect");
  const doctorFields = document.getElementById("doctorFields");
  function toggleDoctor(){
    doctorFields.style.display = roleSelect.value === "doctor" ? "block" : "none";
  }
  roleSelect.addEventListener("change", toggleDoctor);
  toggleDoctor();
</script>';
require_once __DIR__ . '/../partials/footer.php';
?>
