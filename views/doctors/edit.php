<?php
require_once __DIR__ . '/../../core/Auth.php';
Auth::requireRole('admin','doctor');
require_once __DIR__ . '/../../core/helpers.php';
require_once __DIR__ . '/../../core/CSRF.php';
$pageTitle = 'Edit Doctor Profile';
require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/navbar.php';
require_once __DIR__ . '/../partials/sidebar.php';

$availDays = array_map('trim', explode(',', $doctor['available_days'] ?? ''));
?>

<div class="content-wrapper">
  <div class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6"><h1 class="m-0">Edit Doctor: Dr. <?= sanitize($doctor['name']) ?></h1></div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="index.php?page=doctors">Doctors</a></li>
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
          <div class="card card-outline card-info">
            <div class="card-header">
              <h3 class="card-title"><i class="fas fa-id-card mr-2"></i>Doctor Profile</h3>
            </div>
            <form method="POST" action="index.php?page=doctors&action=update" enctype="multipart/form-data">
              <?= \CSRF::field() ?>
              <input type="hidden" name="doctor_id" value="<?= (int)$doctor['id'] ?>">

              <div class="card-body">

                <!-- Profile photo -->
                <div class="form-group text-center mb-4">
                  <div class="mb-2">
                    <?php if (!empty($doctor['avatar']) && file_exists($doctor['avatar'])): ?>
                      <img src="<?= BASE_URL ?>/<?= sanitize($doctor['avatar']) ?>"
                           class="img-thumbnail rounded-circle"
                           style="width:100px;height:100px;object-fit:cover">
                    <?php else: ?>
                      <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-info text-white"
                           style="width:100px;height:100px;font-size:2.5rem">
                        <?= strtoupper(mb_substr($doctor['name'], 0, 1)) ?>
                      </div>
                    <?php endif; ?>
                  </div>
                  <div class="input-group" style="max-width:350px;margin:0 auto">
                    <div class="custom-file">
                      <input type="file" class="custom-file-input" name="doctor_photo"
                             id="photoInput" accept="image/jpeg,image/png">
                      <label class="custom-file-label" for="photoInput">Update photo (JPEG/PNG)…</label>
                    </div>
                  </div>
                </div>

                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label>Specialization <span class="text-danger">*</span></label>
                      <select name="specialization_id" class="form-control" required>
                        <?php foreach ($specializations as $s): ?>
                          <option value="<?= (int)$s['id'] ?>"
                            <?= (int)$s['id'] === (int)$doctor['specialization_id'] ? 'selected' : '' ?>>
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
                             step="0.01" min="0" value="<?= sanitize($doctor['consultation_fee']) ?>">
                    </div>
                  </div>
                </div>

                <div class="form-group">
                  <label>Available Days</label>
                  <div class="d-flex flex-wrap">
                    <?php foreach (['Sun','Mon','Tue','Wed','Thu','Fri','Sat'] as $day): ?>
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
                  <textarea name="bio" class="form-control" rows="4"
                            placeholder="Professional biography…"><?= sanitize($doctor['bio'] ?? '') ?></textarea>
                </div>

              </div>
              <div class="card-footer d-flex justify-content-between">
                <a href="index.php?page=<?= \Auth::role() === 'doctor' ? 'dashboard' : 'doctors' ?>"
                   class="btn btn-secondary">
                  <i class="fas fa-arrow-left mr-1"></i> Back
                </a>
                <button type="submit" class="btn btn-info">
                  <i class="fas fa-save mr-1"></i> Save Profile
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
  document.getElementById("photoInput")?.addEventListener("change", function(){
    var name = this.files[0]?.name || "Update photo…";
    this.nextElementSibling.textContent = name;
  });
</script>';
require_once __DIR__ . '/../partials/footer.php';
?>
