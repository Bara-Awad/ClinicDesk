<?php
require_once __DIR__ . '/../../core/Auth.php';
Auth::requireRole('patient');
require_once __DIR__ . '/../../core/helpers.php';
require_once __DIR__ . '/../../core/CSRF.php';
$pageTitle = 'Book Appointment';
require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/navbar.php';
require_once __DIR__ . '/../partials/sidebar.php';
?>

<div class="content-wrapper">
  <div class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6"><h1 class="m-0">Book an Appointment</h1></div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="index.php?page=appointments">Appointments</a></li>
            <li class="breadcrumb-item active">Book</li>
          </ol>
        </div>
      </div>
    </div>
  </div>

  <section class="content">
    <div class="container-fluid">
      <?php require_once __DIR__ . '/../partials/alerts.php'; ?>

      <div class="row justify-content-center">
        <div class="col-lg-7">
          <div class="card card-outline card-success">
            <div class="card-header">
              <h3 class="card-title"><i class="fas fa-calendar-plus mr-2"></i>New Appointment</h3>
            </div>
            <form method="POST" action="index.php?page=appointments&action=store">
              <?= \CSRF::field() ?>
              <div class="card-body">

                <!-- Doctor -->
                <div class="form-group">
                  <label>Select Doctor <span class="text-danger">*</span></label>
                  <select name="doctor_id" id="doctorSelect" class="form-control" required>
                    <option value="">— Choose a doctor —</option>
                    <?php foreach ($doctorsList as $d): ?>
                      <option value="<?= (int)$d['id'] ?>"
                              data-days="<?= sanitize($d['available_days']) ?>"
                              data-fee="<?= sanitize($d['consultation_fee']) ?>"
                              data-spec="<?= sanitize($d['specialization_name']) ?>"
                              <?= (int)($_POST['doctor_id'] ?? 0) === (int)$d['id'] ? 'selected' : '' ?>>
                        Dr. <?= sanitize($d['name']) ?> — <?= sanitize($d['specialization_name']) ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>

                <!-- Doctor info panel (shown after selection) -->
                <div id="doctorInfo" class="alert alert-info d-none mb-3">
                  <strong><i class="fas fa-info-circle mr-1"></i> Doctor Info</strong><br>
                  <span id="infoSpec"></span> &mdash;
                  Available: <strong id="infoDays"></strong> &mdash;
                  Fee: <strong>$<span id="infoFee"></span></strong>
                </div>

                <!-- Date -->
                <div class="form-group">
                  <label>Preferred Date <span class="text-danger">*</span></label>
                  <input type="date" name="appt_date" id="apptDate" class="form-control"
                         min="<?= date('Y-m-d') ?>"
                         value="<?= sanitize($_POST['appt_date'] ?? '') ?>" required>
                  <small id="dayWarning" class="text-danger d-none">
                    ⚠ The doctor is not available on this day.
                  </small>
                </div>

                <!-- Time Slot -->
                <div class="form-group">
                  <label>Time Slot <span class="text-danger">*</span></label>
                  <select name="appt_time" class="form-control" required>
                    <option value="">— Select time —</option>
                    <?php foreach ($slots as $slot): ?>
                      <option value="<?= $slot ?>" <?= ($_POST['appt_time'] ?? '') === $slot ? 'selected' : '' ?>>
                        <?= date('h:i A', strtotime($slot)) ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>

                <!-- Reason -->
                <div class="form-group">
                  <label>Reason for Visit</label>
                  <textarea name="reason" class="form-control" rows="3"
                            placeholder="Brief description of your concern…"><?= sanitize($_POST['reason'] ?? '') ?></textarea>
                </div>

              </div>
              <div class="card-footer d-flex justify-content-between">
                <a href="index.php?page=appointments" class="btn btn-secondary">
                  <i class="fas fa-arrow-left mr-1"></i> Cancel
                </a>
                <button type="submit" class="btn btn-success">
                  <i class="fas fa-calendar-check mr-1"></i> Book Appointment
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
const doctorSelect = document.getElementById("doctorSelect");
const doctorInfo   = document.getElementById("doctorInfo");
const infoSpec     = document.getElementById("infoSpec");
const infoDays     = document.getElementById("infoDays");
const infoFee      = document.getElementById("infoFee");
const apptDate     = document.getElementById("apptDate");
const dayWarning   = document.getElementById("dayWarning");

// Day abbreviation map for JS Date.getDay()
const dayAbbr = ["Sun","Mon","Tue","Wed","Thu","Fri","Sat"];

function getSelectedDays(){
  const opt = doctorSelect.options[doctorSelect.selectedIndex];
  return opt ? (opt.dataset.days || "").split(",").map(d => d.trim()) : [];
}

function updateDoctorInfo(){
  const opt = doctorSelect.options[doctorSelect.selectedIndex];
  if(!opt || !opt.value){
    doctorInfo.classList.add("d-none");
    return;
  }
  infoSpec.textContent = opt.dataset.spec;
  infoDays.textContent = opt.dataset.days;
  infoFee.textContent  = parseFloat(opt.dataset.fee).toFixed(2);
  doctorInfo.classList.remove("d-none");
  checkDay();
}

function checkDay(){
  if(!apptDate.value) return;
  // Use local date parsing to avoid timezone issues
  const parts = apptDate.value.split("-");
  const d = new Date(parseInt(parts[0]), parseInt(parts[1])-1, parseInt(parts[2]));
  const dayName = dayAbbr[d.getDay()];
  const availDays = getSelectedDays();
  if(availDays.length && !availDays.includes(dayName)){
    dayWarning.classList.remove("d-none");
  } else {
    dayWarning.classList.add("d-none");
  }
}

doctorSelect.addEventListener("change", updateDoctorInfo);
apptDate.addEventListener("change", checkDay);
updateDoctorInfo();
</script>';
require_once __DIR__ . '/../partials/footer.php';
?>
