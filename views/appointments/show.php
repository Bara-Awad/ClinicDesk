<?php
require_once __DIR__ . '/../../core/Auth.php';
Auth::requireRole('admin','doctor','patient');
require_once __DIR__ . '/../../core/helpers.php';
require_once __DIR__ . '/../../core/CSRF.php';
$pageTitle = 'Appointment Details';
require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/navbar.php';
require_once __DIR__ . '/../partials/sidebar.php';

$role = \Auth::role();
?>

<div class="content-wrapper">
  <div class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6"><h1 class="m-0">Appointment #<?= (int)$appointment['id'] ?></h1></div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="index.php?page=appointments">Appointments</a></li>
            <li class="breadcrumb-item active">Details</li>
          </ol>
        </div>
      </div>
    </div>
  </div>

  <section class="content">
    <div class="container-fluid">
      <?php require_once __DIR__ . '/../partials/alerts.php'; ?>

      <div class="row">
        <!-- Left: Appointment Info -->
        <div class="col-lg-8">
          <div class="card card-outline card-primary">
            <div class="card-header">
              <h3 class="card-title"><i class="fas fa-calendar-alt mr-2"></i>Appointment Details</h3>
              <div class="card-tools">
                <span class="badge badge-lg <?= statusBadge($appointment['status']) ?> p-2">
                  <?= e(ucfirst($appointment['status'])) ?>
                </span>
              </div>
            </div>
            <div class="card-body">
              <div class="row">
                <div class="col-md-6">
                  <table class="table table-borderless table-sm">
                    <tr>
                      <th class="text-muted">Patient</th>
                      <td><?= e($appointment['patient_name']) ?></td>
                    </tr>
                    <tr>
                      <th class="text-muted">Doctor</th>
                      <td>Dr. <?= e($appointment['doctor_name']) ?></td>
                    </tr>
                    <tr>
                      <th class="text-muted">Specialization</th>
                      <td><?= e($appointment['specialization_name']) ?></td>
                    </tr>
                    <tr>
                      <th class="text-muted">Date</th>
                      <td><?= e(formatDate($appointment['appt_date'])) ?></td>
                    </tr>
                    <tr>
                      <th class="text-muted">Time</th>
                      <td><?= e(formatTime($appointment['appt_time'])) ?></td>
                    </tr>
                    <tr>
                      <th class="text-muted">Fee</th>
                      <td>$<?= number_format((float)$appointment['consultation_fee'], 2) ?></td>
                    </tr>
                  </table>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label class="text-muted font-weight-bold">Reason for Visit</label>
                    <p><?= e($appointment['reason'] ?? '—') ?></p>
                  </div>
                  <?php if ($appointment['doctor_notes']): ?>
                  <div class="form-group">
                    <label class="text-muted font-weight-bold">Doctor Notes</label>
                    <p><?= e($appointment['doctor_notes']) ?></p>
                  </div>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </div>

          <!-- Prescription Info -->
          <?php if ($prescription): ?>
          <div class="card card-outline card-success">
            <div class="card-header">
              <h3 class="card-title"><i class="fas fa-prescription-bottle mr-2"></i>Prescription</h3>
            </div>
            <div class="card-body">
              <div class="row">
                <div class="col-md-6">
                  <label class="text-muted font-weight-bold">Diagnosis</label>
                  <p><?= e($prescription['diagnosis']) ?></p>
                </div>
                <div class="col-md-6">
                  <label class="text-muted font-weight-bold">Medications</label>
                  <p><?= e($prescription['medications']) ?></p>
                </div>
              </div>
              <?php if ($prescription['notes']): ?>
              <div>
                <label class="text-muted font-weight-bold">Notes</label>
                <p><?= e($prescription['notes']) ?></p>
              </div>
              <?php endif; ?>
              <?php if ($prescription['file_path']): ?>
              <a href="index.php?page=prescriptions&action=download&id=<?= (int)$prescription['id'] ?>"
                 class="btn btn-sm btn-outline-success">
                <i class="fas fa-file-pdf mr-1"></i> Download PDF
              </a>
              <?php endif; ?>
            </div>
          </div>
          <?php endif; ?>
        </div>

        <!-- Right: Actions -->
        <div class="col-lg-4">

          <?php if ($role === 'doctor' || $role === 'admin'): ?>
          <div class="card card-outline card-warning">
            <div class="card-header">
              <h3 class="card-title"><i class="fas fa-edit mr-2"></i>Update Status</h3>
            </div>
            <form method="POST" action="index.php?page=appointments&action=update_status">
              <?= \CSRF::field() ?>
              <input type="hidden" name="appointment_id" value="<?= (int)$appointment['id'] ?>">
              <div class="card-body">
                <div class="form-group">
                  <label>New Status</label>
                  <select name="status" class="form-control">
                    <?php foreach (['pending','confirmed','completed','cancelled'] as $s): ?>
                      <option value="<?= $s ?>" <?= $appointment['status'] === $s ? 'selected' : '' ?>>
                        <?= ucfirst($s) ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="form-group">
                  <label>Doctor Notes</label>
                  <textarea name="doctor_notes" class="form-control" rows="3"
                            placeholder="Optional notes…"><?= e($appointment['doctor_notes'] ?? '') ?></textarea>
                </div>
              </div>
              <div class="card-footer text-right">
                <button type="submit" class="btn btn-warning btn-block">
                  <i class="fas fa-save mr-1"></i> Save Status
                </button>
              </div>
            </form>
          </div>
          <?php endif; ?>

          <?php if ($role === 'doctor' && $appointment['status'] === 'completed' && !$prescription): ?>
          <div class="card card-outline card-success">
            <div class="card-body text-center">
              <p class="text-muted">No prescription yet for this appointment.</p>
              <a href="index.php?page=prescriptions&action=create&appointment_id=<?= (int)$appointment['id'] ?>"
                 class="btn btn-success btn-block">
                <i class="fas fa-prescription mr-1"></i> Add Prescription
              </a>
            </div>
          </div>
          <?php endif; ?>

          <?php if ($role === 'patient' && $appointment['status'] === 'pending'): ?>
          <div class="card card-outline card-danger">
            <div class="card-body text-center">
              <form method="POST" action="index.php?page=appointments&action=cancel"
                    onsubmit="return confirm('Are you sure you want to cancel this appointment?')">
                <?= \CSRF::field() ?>
                <input type="hidden" name="appointment_id" value="<?= (int)$appointment['id'] ?>">
                <button type="submit" class="btn btn-danger btn-block">
                  <i class="fas fa-times mr-1"></i> Cancel Appointment
                </button>
              </form>
            </div>
          </div>
          <?php endif; ?>

          <div class="card">
            <div class="card-body text-center">
              <a href="index.php?page=appointments" class="btn btn-outline-secondary btn-block">
                <i class="fas fa-arrow-left mr-1"></i> Back to List
              </a>
            </div>
          </div>

        </div>
      </div>

    </div>
  </section>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
