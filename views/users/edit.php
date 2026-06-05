<?php
require_once __DIR__ . '/../../core/Auth.php';
Auth::requireRole('admin');
require_once __DIR__ . '/../../core/helpers.php';
require_once __DIR__ . '/../../core/CSRF.php';
$pageTitle = 'Edit User';
require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/navbar.php';
require_once __DIR__ . '/../partials/sidebar.php';
?>

<div class="content-wrapper">
  <div class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6"><h1 class="m-0">Edit User: <?= sanitize($targetUser['name']) ?></h1></div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="index.php?page=users">Users</a></li>
            <li class="breadcrumb-item active">Edit</li>
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

          <!-- Profile Form -->
          <div class="card card-outline card-primary">
            <div class="card-header">
              <h3 class="card-title"><i class="fas fa-user-edit mr-2"></i>Profile Information</h3>
              <div class="card-tools">
                <span class="badge <?=
                  $targetUser['role'] === 'admin'   ? 'badge-danger' :
                  ($targetUser['role'] === 'doctor' ? 'badge-info'   : 'badge-success')
                ?> p-2">
                  <?= sanitize(ucfirst($targetUser['role'])) ?>
                </span>
              </div>
            </div>
            <form method="POST" action="index.php?page=users&action=update" enctype="multipart/form-data">
              <?= \CSRF::field() ?>
              <input type="hidden" name="user_id" value="<?= (int) $targetUser['id'] ?>">

              <div class="card-body">
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label>Full Name <span class="text-danger">*</span></label>
                      <input type="text" name="name" class="form-control"
                             value="<?= sanitize($targetUser['name']) ?>" required>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label>Email Address</label>
                      <input type="email" class="form-control"
                             value="<?= sanitize($targetUser['email']) ?>" disabled>
                      <small class="text-muted">Email cannot be changed.</small>
                    </div>
                  </div>
                </div>

                <div class="form-group">
                  <label>Phone</label>
                  <input type="text" name="phone" class="form-control"
                         value="<?= sanitize($targetUser['phone'] ?? '') ?>">
                </div>

                <div class="form-group">
                  <label>Avatar (JPEG/PNG, max 1MB)</label>
                  <div class="input-group">
                    <div class="custom-file">
                      <input type="file" class="custom-file-input" name="avatar"
                             id="avatarInput" accept="image/jpeg,image/png">
                      <label class="custom-file-label" for="avatarInput">Choose file…</label>
                    </div>
                  </div>
                  <?php if (!empty($targetUser['avatar']) && file_exists($targetUser['avatar'])): ?>
                    <div class="mt-2">
                      <img src="<?= BASE_URL ?>/<?= sanitize($targetUser['avatar']) ?>"
                           alt="Current avatar" class="img-thumbnail" style="max-height:80px">
                    </div>
                  <?php endif; ?>
                </div>

                <!-- Doctor fields -->
                <?php if ($targetUser['role'] === 'doctor' && $doctorRecord): ?>
                <hr>
                <h5 class="text-primary"><i class="fas fa-user-md mr-2"></i>Doctor Profile</h5>
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label>Specialization</label>
                      <select name="specialization_id" class="form-control">
                        <?php foreach ($specializations as $s): ?>
                          <option value="<?= (int)$s['id'] ?>"
                            <?= (int)$s['id'] === (int)$doctorRecord['specialization_id'] ? 'selected' : '' ?>>
                            <?= sanitize($s['name']) ?>
                          </option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label>Consultation Fee ($)</label>
                      <input type="number" name="consultation_fee" class="form-control"
                             step="0.01" min="0" value="<?= sanitize($doctorRecord['consultation_fee']) ?>">
                    </div>
                  </div>
                </div>

                <div class="form-group">
                  <label>Available Days</label>
                  <div class="d-flex flex-wrap">
                    <?php
                    $availDays = array_map('trim', explode(',', $doctorRecord['available_days'] ?? ''));
                    foreach (['Sun','Mon','Tue','Wed','Thu','Fri','Sat'] as $day):
                    ?>
                      <div class="custom-control custom-checkbox mr-3 mb-2">
                        <input type="checkbox" class="custom-control-input"
                               id="day_<?= $day ?>" name="available_days[]" value="<?= $day ?>"
                               <?= in_array($day, $availDays) ? 'checked' : '' ?>>
                        <label class="custom-control-label" for="day_<?= $day ?>"><?= $day ?></label>
                      </div>
                    <?php endforeach; ?>
                  </div>
                </div>

                <div class="form-group">
                  <label>Bio</label>
                  <textarea name="bio" class="form-control" rows="3"><?= sanitize($doctorRecord['bio'] ?? '') ?></textarea>
                </div>
                <?php endif; ?>

              </div>
              <div class="card-footer d-flex justify-content-between">
                <a href="index.php?page=users" class="btn btn-secondary">
                  <i class="fas fa-arrow-left mr-1"></i> Back
                </a>
                <button type="submit" class="btn btn-primary">
                  <i class="fas fa-save mr-1"></i> Save Changes
                </button>
              </div>
            </form>
          </div>

          <!-- Change Password -->
          <div class="card card-outline card-warning">
            <div class="card-header">
              <h3 class="card-title"><i class="fas fa-key mr-2"></i>Change Password</h3>
            </div>
            <form method="POST" action="index.php?page=users&action=change_password">
              <?= \CSRF::field() ?>
              <input type="hidden" name="user_id" value="<?= (int) $targetUser['id'] ?>">
              <div class="card-body">
                <div class="form-group">
                  <label>New Password</label>
                  <input type="password" name="new_password" class="form-control"
                         required minlength="8" placeholder="Min. 8 characters">
                </div>
              </div>
              <div class="card-footer text-right">
                <button type="submit" class="btn btn-warning">
                  <i class="fas fa-key mr-1"></i> Update Password
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
  // Show file name in custom file input
  document.getElementById("avatarInput")?.addEventListener("change", function(){
    var name = this.files[0]?.name || "Choose file…";
    this.nextElementSibling.textContent = name;
  });
</script>';
require_once __DIR__ . '/../partials/footer.php';
?>
