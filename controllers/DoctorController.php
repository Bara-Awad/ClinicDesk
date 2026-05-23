<?php
/**
 * ClinicDesk - DoctorController
 * Admin: CRUD for doctor profiles.
 * Doctor: edit own profile.
 */

require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/CSRF.php';
require_once __DIR__ . '/../core/Paginator.php';
require_once __DIR__ . '/../core/helpers.php';
require_once __DIR__ . '/../models/DoctorModel.php';
require_once __DIR__ . '/../models/SpecializationModel.php';
require_once __DIR__ . '/../config/config.php';

class DoctorController
{
    private DoctorModel          $doctors;
    private SpecializationModel  $specs;

    public function __construct()
    {
        $this->doctors = new DoctorModel();
        $this->specs   = new SpecializationModel();
    }

    // -------------------------------------------------------------------------
    // Admin: List doctors
    // -------------------------------------------------------------------------
    public function index(): void
    {
        Auth::requireRole('admin');

        $pageNum   = currentPageNum();
        $total     = $this->doctors->countAll();
        $paginator = new Paginator($total, ITEMS_PER_PAGE, $pageNum);
        $doctorsList = $this->doctors->getAllPaginated($paginator->offset(), ITEMS_PER_PAGE);

        $pageTitle = 'Manage Doctors';
        require_once __DIR__ . '/../views/doctors/index.php';
    }

    // -------------------------------------------------------------------------
    // Admin: Edit doctor
    // -------------------------------------------------------------------------
    public function edit(): void
    {
        Auth::requireRole('admin', 'doctor');

        $role = Auth::role();
        $user = Auth::currentUser();

        if ($role === 'doctor') {
            $doctor = $this->doctors->findByUserId($user['id']);
        } else {
            $id     = (int) ($_GET['id'] ?? 0);
            $doctor = $this->doctors->findById($id);
        }

        if (!$doctor) {
            flashMessage('error', 'Doctor not found.');
            redirect('index.php?page=doctors');
        }

        $specializations = $this->specs->getAll();
        $pageTitle = 'Edit Doctor Profile';
        require_once __DIR__ . '/../views/doctors/edit.php';
    }

    // -------------------------------------------------------------------------
    // Admin: Update doctor
    // -------------------------------------------------------------------------
    public function update(): void
    {
        Auth::requireRole('admin', 'doctor');

        if (!CSRF::validateToken($_POST['csrf_token'] ?? '')) {
            flashMessage('error', 'Invalid request.');
            redirect('index.php?page=doctors');
        }

        $doctorId = (int) ($_POST['doctor_id'] ?? 0);
        $doctor   = $this->doctors->findById($doctorId);

        // Doctor role can only edit own profile
        if (Auth::role() === 'doctor') {
            $me = $this->doctors->findByUserId(Auth::currentUser()['id']);
            if (!$me || (int) $me['id'] !== $doctorId) {
                redirect('index.php?page=error&code=403');
            }
        }

        if (!$doctor) {
            flashMessage('error', 'Doctor not found.');
            redirect('index.php?page=doctors');
        }

        $specId = (int)   ($_POST['specialization_id']  ?? 0);
        $fee    = (float) ($_POST['consultation_fee']   ?? 0);
        $days   = isset($_POST['available_days'])
            ? implode(',', array_map('trim', $_POST['available_days']))
            : 'Sun,Mon,Tue,Wed,Thu';
        $bio    = trim($_POST['bio'] ?? '');

        // Handle photo upload
        if (!empty($_FILES['doctor_photo']['tmp_name'])) {
            $uploaded = $this->handlePhotoUpload($_FILES['doctor_photo'], $doctorId);
            if ($uploaded !== false) {
                // Delete old photo if it exists
                if ($doctor['avatar'] && file_exists($doctor['avatar'])) {
                    @unlink($doctor['avatar']);
                }
                $this->doctors->updatePhoto($doctorId, $uploaded);
            } else {
                flashMessage('error', 'Photo upload failed. JPEG/PNG only, max 1MB.');
                redirect('index.php?page=doctors&action=edit&id=' . $doctorId);
            }
        }

        $this->doctors->update($doctorId, [
            'specialization_id' => $specId,
            'bio'               => $bio,
            'consultation_fee'  => $fee,
            'available_days'    => $days,
        ]);

        flashMessage('success', 'Doctor profile updated.');
        redirect(Auth::role() === 'doctor' ? 'index.php?page=dashboard' : 'index.php?page=doctors');
    }

    // -------------------------------------------------------------------------
    // Admin: Specialization CRUD
    // -------------------------------------------------------------------------
    public function specializations(): void
    {
        Auth::requireRole('admin');

        $specsList = $this->specs->getAll();
        $pageTitle = 'Manage Specializations';
        require_once __DIR__ . '/../views/doctors/specializations.php';
    }

    public function addSpecialization(): void
    {
        Auth::requireRole('admin');

        if (!CSRF::validateToken($_POST['csrf_token'] ?? '')) {
            flashMessage('error', 'Invalid request.');
            redirect('index.php?page=doctors&action=specializations');
        }

        $name = trim($_POST['name'] ?? '');
        if (strlen($name) < 2) {
            flashMessage('error', 'Specialization name must be at least 2 characters.');
            redirect('index.php?page=doctors&action=specializations');
        }

        if ($this->specs->nameExists($name)) {
            flashMessage('error', 'Specialization already exists.');
            redirect('index.php?page=doctors&action=specializations');
        }

        $this->specs->create($name);
        flashMessage('success', 'Specialization added.');
        redirect('index.php?page=doctors&action=specializations');
    }

    public function deleteSpecialization(): void
    {
        Auth::requireRole('admin');

        if (!CSRF::validateToken($_POST['csrf_token'] ?? '')) {
            flashMessage('error', 'Invalid request.');
            redirect('index.php?page=doctors&action=specializations');
        }

        $id = (int) ($_POST['spec_id'] ?? 0);

        if (!$this->specs->isSafeToDelete($id)) {
            flashMessage('error', 'Cannot delete: doctors are assigned to this specialization.');
            redirect('index.php?page=doctors&action=specializations');
        }

        $this->specs->delete($id);
        flashMessage('success', 'Specialization deleted.');
        redirect('index.php?page=doctors&action=specializations');
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------
    private function handlePhotoUpload(array $file, int $doctorId): string|false
    {
        if ($file['error'] !== UPLOAD_ERR_OK)      return false;
        if ($file['size'] > MAX_DOCTOR_PHOTO_SIZE) return false;

        // التحقق أنها صورة حقيقية بـ getimagesize وليس فقط بالامتداد
        $imageInfo = @getimagesize($file['tmp_name']);
        if ($imageInfo === false) return false;
        if (!in_array($imageInfo['mime'], ['image/jpeg', 'image/png'])) return false;

        $ext      = $imageInfo['mime'] === 'image/jpeg' ? 'jpg' : 'png';
        $filename = 'doctor_' . $doctorId . '_' . time() . '.' . $ext;
        $destPath = UPLOAD_DOCTOR_PHOTOS . $filename;

        if (!move_uploaded_file($file['tmp_name'], $destPath)) return false;

        return $destPath;
    }
}
