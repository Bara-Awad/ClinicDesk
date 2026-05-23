<?php
/**
 * ClinicDesk - PrescriptionController
 * Doctor: add prescriptions with optional PDF upload.
 * Patient: view and securely download prescriptions.
 */

require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/CSRF.php';
require_once __DIR__ . '/../core/helpers.php';
require_once __DIR__ . '/../models/PrescriptionModel.php';
require_once __DIR__ . '/../models/AppointmentModel.php';
require_once __DIR__ . '/../models/DoctorModel.php';
require_once __DIR__ . '/../config/config.php';

class PrescriptionController
{
    private PrescriptionModel $prescriptions;
    private AppointmentModel  $appointments;

    public function __construct()
    {
        $this->prescriptions = new PrescriptionModel();
        $this->appointments  = new AppointmentModel();
    }

    // -------------------------------------------------------------------------
    // Doctor: Show "Add Prescription" form
    // -------------------------------------------------------------------------
    public function create(): void
    {
        Auth::requireRole('doctor');

        $apptId      = (int) ($_GET['appointment_id'] ?? 0);
        $appointment = $this->appointments->findById($apptId);

        $this->verifyDoctorOwnership($appointment);
        $this->requireCompletedNoRx($appointment);

        $pageTitle = 'Add Prescription';
        require_once __DIR__ . '/../views/prescriptions/create.php';
    }

    // -------------------------------------------------------------------------
    // Doctor: Process prescription form
    // -------------------------------------------------------------------------
    public function store(): void
    {
        Auth::requireRole('doctor');

        if (!CSRF::validateToken($_POST['csrf_token'] ?? '')) {
            flashMessage('error', 'Invalid request.');
            redirect('index.php?page=appointments');
        }

        $apptId      = (int) ($_POST['appointment_id'] ?? 0);
        $appointment = $this->appointments->findById($apptId);

        $this->verifyDoctorOwnership($appointment);
        $this->requireCompletedNoRx($appointment);

        $diagnosis   = trim($_POST['diagnosis']   ?? '');
        $medications = trim($_POST['medications'] ?? '');
        $notes       = trim($_POST['notes']       ?? '');

        if (!$diagnosis || !$medications) {
            flashMessage('error', 'Diagnosis and medications are required.');
            redirect('index.php?page=prescriptions&action=create&appointment_id=' . $apptId);
        }

        // Handle optional PDF upload
        $filePath = null;
        if (!empty($_FILES['prescription_file']['tmp_name'])) {
            $uploaded = $this->handlePrescriptionUpload($_FILES['prescription_file'], $apptId);
            if ($uploaded === false) {
                flashMessage('error', 'File upload failed. PDF only, max 3MB.');
                redirect('index.php?page=prescriptions&action=create&appointment_id=' . $apptId);
            }
            $filePath = $uploaded;
        }

        $this->prescriptions->create([
            'appointment_id' => $apptId,
            'diagnosis'      => $diagnosis,
            'medications'    => $medications,
            'notes'          => $notes,
            'file_path'      => $filePath,
        ]);

        flashMessage('success', 'Prescription added successfully.');
        redirect('index.php?page=appointments&action=show&id=' . $apptId);
    }

    // -------------------------------------------------------------------------
    // Patient: List prescriptions
    // -------------------------------------------------------------------------
    public function index(): void
    {
        Auth::requireRole('patient');

        $patientId    = Auth::currentUser()['id'];
        $prescList    = $this->prescriptions->getByPatient($patientId);

        $pageTitle = 'My Prescriptions';
        require_once __DIR__ . '/../views/prescriptions/index.php';
    }

    // -------------------------------------------------------------------------
    // All roles: Securely download prescription PDF
    // -------------------------------------------------------------------------
    public function download(): void
    {
        Auth::requireRole('admin', 'doctor', 'patient');

        $prescId     = (int) ($_GET['id'] ?? 0);
        $prescription = $this->prescriptions->findById($prescId);

        if (!$prescription) {
            flashMessage('error', 'Prescription not found.');
            redirect('index.php?page=appointments');
        }

        $appointment = $this->appointments->findById((int) $prescription['appointment_id']);
        if (!$appointment) {
            redirect('index.php?page=error&code=403');
        }

        // Ownership check by role
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

        $filePath = $prescription['file_path'];

        if (!$filePath || !file_exists($filePath)) {
            flashMessage('error', 'Prescription file not found on server.');
            redirect('index.php?page=prescriptions');
        }

        // Stream file securely — never expose the real path
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="prescription.pdf"');
        header('Content-Length: ' . filesize($filePath));
        readfile($filePath);
        exit();
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    /** يتحقق أن الطبيب الحالي يملك هذا الموعد */
    private function verifyDoctorOwnership(?array $appointment): void
    {
        if (!$appointment) {
            flashMessage('error', 'Appointment not found.');
            redirect('index.php?page=appointments');
        }

        $user   = Auth::currentUser();
        $doctor = (new DoctorModel())->findByUserId($user['id']);

        if (!$doctor || (int) $appointment['doctor_id'] !== (int) $doctor['id']) {
            redirect('index.php?page=error&code=403');
        }
    }

    /** يتحقق أن الموعد مكتمل وليس له وصفة بعد */
    private function requireCompletedNoRx(array $appointment): void
    {
        if ($appointment['status'] !== 'completed') {
            flashMessage('error', 'Prescriptions can only be added to completed appointments.');
            redirect('index.php?page=appointments&action=show&id=' . $appointment['id']);
        }

        $existing = $this->prescriptions->findByAppointmentId((int) $appointment['id']);
        if ($existing) {
            flashMessage('info', 'A prescription already exists for this appointment.');
            redirect('index.php?page=appointments&action=show&id=' . $appointment['id']);
        }
    }

    private function handlePrescriptionUpload(array $file, int $apptId): string|false
    {
        if ($file['error'] !== UPLOAD_ERR_OK)           return false;
        if ($file['size'] > MAX_PRESCRIPTION_SIZE)       return false;

        // Validate MIME with finfo (not $_FILES['type']) for security
        $finfo    = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);

        if ($mimeType !== 'application/pdf') return false;

        $filename = 'prescription_' . $apptId . '_' . time() . '.pdf';
        $destPath = UPLOAD_PRESCRIPTIONS . $filename;

        if (!move_uploaded_file($file['tmp_name'], $destPath)) return false;

        return $destPath;
    }
}
