<?php
/**
 * ClinicDesk - AppointmentController
 * يخدم الأدوار الثلاثة: الحجز، العرض، الفلترة، وتحديث الحالة
 */

require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/CSRF.php';
require_once __DIR__ . '/../core/Paginator.php';
require_once __DIR__ . '/../core/helpers.php';
require_once __DIR__ . '/../models/AppointmentModel.php';
require_once __DIR__ . '/../models/DoctorModel.php';
require_once __DIR__ . '/../models/PrescriptionModel.php';
require_once __DIR__ . '/../config/config.php';

class AppointmentController
{
    private AppointmentModel $appointments;
    private DoctorModel      $doctors;

    public function __construct()
    {
        $this->appointments = new AppointmentModel();
        $this->doctors      = new DoctorModel();
    }

    // -------------------------------------------------------------------------
    // Patient: Show booking form عرض نموذج الحجز
    // -------------------------------------------------------------------------
    public function book(): void
    {
        Auth::requireRole('patient');

        $doctorsList = $this->doctors->getAll();
        $slots       = APPOINTMENT_SLOTS;
        $pageTitle   = 'Book an Appointment';
        require_once __DIR__ . '/../views/appointments/book.php';
    }

    // -------------------------------------------------------------------------
    // Patient: Process booking form معالجة نموذج الحجز
    // -------------------------------------------------------------------------
    public function store(): void
    {
        Auth::requireRole('patient');

        if (!CSRF::validateToken($_POST['csrf_token'] ?? '')) {
            flashMessage('error', 'Invalid request.');
            redirect('index.php?page=appointments&action=book');
        }

        $patientId = Auth::currentUser()['id'];
        $doctorId  = (int) ($_POST['doctor_id']  ?? 0);
        $date      = trim($_POST['appt_date']     ?? '');
        $time      = trim($_POST['appt_time']     ?? '');
        $reason    = trim($_POST['reason']        ?? '');

        // Validations التحقق من صحة البيانات
        $errors = [];

        if (!$doctorId) $errors[] = 'Please select a doctor.';
        if (!$date)     $errors[] = 'Please select a date.';
        if (!$time)     $errors[] = 'Please select a time slot.';

        if ($date && strtotime($date) < strtotime('today')) {
            $errors[] = 'Appointment date cannot be in the past.';
        }

        // Day-of-week check التحقق أن اليوم المختار ضمن أيام عمل الطبيب
        if ($date && $doctorId) {
            $doctor        = $this->doctors->findById($doctorId);
            $availableDays = $doctor ? $this->doctors->getAvailableDays($doctorId) : [];
            $dayOfWeek     = date('D', strtotime($date)); 

            if (!in_array($dayOfWeek, $availableDays, true)) {
                $errors[] = 'The doctor is not available on ' . date('l', strtotime($date)) . '.';
            }
        }

        // Conflict check التحقق من التعارضات
        if (!$errors && $this->appointments->hasConflict($doctorId, $date, $time)) {
            $errors[] = 'This slot is already booked. Please choose another time.';
        }

        if ($errors) {
            flashMessage('error', implode(' ', $errors));
            redirect('index.php?page=appointments&action=book');
        }

        $booked = $this->appointments->book([
            'patient_id' => $patientId,
            'doctor_id'  => $doctorId,
            'appt_date'  => $date,
            'appt_time'  => $time,
            'reason'     => $reason,
        ]);

        if (!$booked) {
            flashMessage('error', 'Booking failed. The slot may already be taken.');
            redirect('index.php?page=appointments&action=book');
        }

        flashMessage('success', 'Appointment booked successfully!');
        redirect('index.php?page=appointments');
    }

    // -------------------------------------------------------------------------
    // List appointments (role-aware) عرض المواعيد (حسب الدور)
    // -------------------------------------------------------------------------
    public function index(): void
    {
        Auth::requireRole('admin', 'doctor', 'patient');

        $role    = Auth::role();
        $user    = Auth::currentUser();
        $filters = $this->getFilters();
        $pageNum = currentPageNum();

        if ($role === 'patient') {
            $scope      = ['patient_id' => $user['id']];
            $total      = $this->appointments->countFiltered($scope, $filters);
            $paginator  = new Paginator($total, ITEMS_PER_PAGE, $pageNum);
            $apptList   = $this->appointments->getByPatient($user['id'], $paginator->offset(), ITEMS_PER_PAGE, $filters);

        } elseif ($role === 'doctor') {
            $doctor     = (new DoctorModel())->findByUserId($user['id']);
            $doctorId   = $doctor ? (int) $doctor['id'] : 0;
            $scope      = ['doctor_id' => $doctorId];
            $total      = $this->appointments->countFiltered($scope, $filters);
            $paginator  = new Paginator($total, ITEMS_PER_PAGE, $pageNum);
            $apptList   = $this->appointments->getByDoctor($doctorId, $paginator->offset(), ITEMS_PER_PAGE, $filters);

        } else { // admin
            $total      = $this->appointments->countFiltered([], $filters);
            $paginator  = new Paginator($total, ITEMS_PER_PAGE, $pageNum);
            $apptList   = $this->appointments->getAll($paginator->offset(), ITEMS_PER_PAGE, $filters);
        }

        $doctorsList = $this->doctors->getAll(); // for filter dropdown
        $pageTitle   = 'Appointments';
        require_once __DIR__ . '/../views/appointments/index.php';
    }

    // -------------------------------------------------------------------------
    // View single appointment details عرض تفاصيل الموعد الواحد
    // -------------------------------------------------------------------------
    public function show(): void
    {
        Auth::requireRole('admin', 'doctor', 'patient');

        $id          = (int) ($_GET['id'] ?? 0);
        $appointment = $this->appointments->findById($id);

        if (!$appointment) {
            flashMessage('error', 'Appointment not found.');
            redirect('index.php?page=appointments');
        }

        // Ownership check التحقق من الملكية
        $this->verifyOwnership($appointment);

        $prescModel  = new PrescriptionModel();
        $prescription = $prescModel->findByAppointmentId($id);

        $pageTitle = 'Appointment Details';
        require_once __DIR__ . '/../views/appointments/show.php';
    }

    // -------------------------------------------------------------------------
    // Doctor/Admin: Update status and add notes تحديث الحالة وإضافة الملاحظات
    // -------------------------------------------------------------------------
    public function updateStatus(): void
    {
        Auth::requireRole('admin', 'doctor');

        if (!CSRF::validateToken($_POST['csrf_token'] ?? '')) {
            flashMessage('error', 'Invalid request.');
            redirect('index.php?page=appointments');
        }

        $id     = (int) ($_POST['appointment_id'] ?? 0);
        $status = $_POST['status'] ?? '';
        $notes  = trim($_POST['doctor_notes'] ?? '');

        $appointment = $this->appointments->findById($id);
        if (!$appointment) {
            flashMessage('error', 'Appointment not found.');
            redirect('index.php?page=appointments');
        }

        $this->verifyOwnership($appointment);

        $allowed = ['pending', 'confirmed', 'completed', 'cancelled'];
        if (!in_array($status, $allowed, true)) {
            flashMessage('error', 'Invalid status.');
            redirect('index.php?page=appointments&action=show&id=' . $id);
        }

        $this->appointments->updateStatus($id, $status, $notes);
        flashMessage('success', 'Appointment status updated.');
        redirect('index.php?page=appointments&action=show&id=' . $id);
    }

    // -------------------------------------------------------------------------
    // Patient: Cancel appointment إلغاء الموعد
    // -------------------------------------------------------------------------
    public function cancel(): void
    {
        Auth::requireRole('patient');

        if (!CSRF::validateToken($_POST['csrf_token'] ?? '')) {
            flashMessage('error', 'Invalid request.');
            redirect('index.php?page=appointments');
        }

        $id        = (int) ($_POST['appointment_id'] ?? 0);
        $patientId = Auth::currentUser()['id'];

        $cancelled = $this->appointments->cancel($id, $patientId);

        if ($cancelled) {
            flashMessage('success', 'Appointment cancelled.');
        } else {
            flashMessage('error', 'Could not cancel this appointment.');
        }

        redirect('index.php?page=appointments');
    }

    // -------------------------------------------------------------------------
    // Private helpers وظائف مساعدة خاصة
    // -------------------------------------------------------------------------

    private function getFilters(): array
    {
        return [
            'status'       => $_GET['status']       ?? '',
            'doctor_id'    => (int) ($_GET['doctor_id'] ?? 0) ?: '',
            'start_date'   => $_GET['start_date']   ?? '',
            'end_date'     => $_GET['end_date']     ?? '',
            'patient_name' => trim($_GET['patient_name'] ?? ''),
        ];
    }

    private function verifyOwnership(array $appointment): void
    {
        $user = Auth::currentUser();
        $role = Auth::role();

        if ($role === 'patient' && (int) $appointment['patient_id'] !== $user['id']) {
            redirect('index.php?page=error&code=403');
        }

        if ($role === 'doctor') {
            $doctor = (new DoctorModel())->findByUserId($user['id']);
            if (!$doctor || (int) $appointment['doctor_id'] !== (int) $doctor['id']) {
                redirect('index.php?page=error&code=403');
            }
        }
    }
}
