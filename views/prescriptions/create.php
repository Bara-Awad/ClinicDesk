<?php
require_once __DIR__ . '/../../core/Auth.php';
Auth::requireRole('doctor');
require_once __DIR__ . '/../../core/helpers.php';
require_once __DIR__ . '/../../core/CSRF.php';
$pageTitle = 'Add Prescription';
require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/navbar.php';
require_once __DIR__ . '/../partials/sidebar.php';
?>

<div class="content-wrapper">
  <div class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6"><h1 class="m-0">Add Prescription</h1></div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="index.php?page=appointments">Appointments</a></li>
            <li class="breadcrumb-item active">Add Prescription</li>
          </ol>
        </div>
      </div>
    </div>
  </div>

  <section class="content">
    <div class="container-fluid">
      <?php require_once __DIR__ . '/../partials/alerts.php'; ?>

      <!-- Appointment summary -->
      <div class="alert alert-light border mb-3">
        <strong><i class="fas fa-calendar-check mr-2 text-success"></i>Appointment #<?= (int)$appointment['id'] ?></strong>
        &mdash; Patient: <strong><?= sanitize($appointment['patient_name']) ?></strong>
        &mdash; Date: <?= sanitize(formatDate($appointment['appt_date'])) ?> at <?= sanitize(formatTime($appointment['appt_time'])) ?>
      </div>

      <div class="row justify-content-center">
        <div class="col-lg-8">
          <div class="card card-outline card-success">
            <div class="card-header">
              <h3 class="card-title"><i class="fas fa-prescription-bottle-alt mr-2"></i>Prescription Details</h3>
            </div>
            <form method="POST" action="index.php?page=prescriptions&action=store" enctype="multipart/form-data">
              <?= \CSRF::field() ?>
              <input type="hidden" name="appointment_id" value="<?= (int)$appointment['id'] ?>">

              <div class="card-body">

                <div class="form-group">
                  <label>Diagnosis <span class="text-danger">*</span></label>
                  <textarea name="diagnosis" class="form-control" rows="3"
                            placeholder="Patient diagnosis…" required><?= sanitize($_POST['diagnosis'] ?? '') ?></textarea>
                </div>

                <div class="form-group">
                  <label>Medications <span class="text-danger">*</span></label>
                  <textarea name="medications" class="form-control" rows="4"
                            placeholder="List medications, dosage, and instructions…" required><?= sanitize($_POST['medications'] ?? '') ?></textarea>
                </div>

                <div class="form-group">
                  <label>Additional Notes</label>
                  <textarea name="notes" class="form-control" rows="2"
                            placeholder="Follow-up instructions, diet, etc."><?= sanitize($_POST['notes'] ?? '') ?></textarea>
                </div>

                <div class="form-group">
                  <label>Upload Prescription PDF (optional, max 3MB)</label>
                  <div class="input-group">
                    <div class="custom-file">
                      <input type="file" class="custom-file-input" name="prescription_file"
                             id="prescFile" accept="application/pdf">
                      <label class="custom-file-label" for="prescFile">Choose PDF file…</label>
                    </div>
                  </div>
                  <small class="text-muted">Only PDF files accepted. Validated server-side via finfo.</small>
                </div>

              </div>
              <div class="card-footer d-flex justify-content-between">
                <a href="index.php?page=appointments&action=show&id=<?= (int)$appointment['id'] ?>"
                   class="btn btn-secondary">
                  <i class="fas fa-arrow-left mr-1"></i> Cancel
                </a>
                <button type="submit" class="btn btn-success">
                  <i class="fas fa-save mr-1"></i> Save Prescription
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
  document.getElementById("prescFile")?.addEventListener("change", function(){
    var name = this.files[0]?.name || "Choose PDF file…";
    this.nextElementSibling.textContent = name;
  });
</script>';
require_once __DIR__ . '/../partials/footer.php';
?>
