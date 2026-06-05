<?php
/**
 * ClinicDesk - UserController
 * Admin-only: full CRUD for user accounts.
 */

require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/CSRF.php';
require_once __DIR__ . '/../core/Paginator.php';
require_once __DIR__ . '/../core/helpers.php';
require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../models/DoctorModel.php';
require_once __DIR__ . '/../models/SpecializationModel.php';
require_once __DIR__ . '/../config/config.php';

class UserController
{
    private UserModel $users;
    private DoctorModel $doctors;
    private SpecializationModel $specs;

    public function __construct()
    {
        $this->users   = new UserModel();
        $this->doctors = new DoctorModel();
        $this->specs   = new SpecializationModel();
    }

    // -------------------------------------------------------------------------
    // List users
    // -------------------------------------------------------------------------
    public function index(): void
    {
        Auth::requireRole('admin');

        $role    = $_GET['role']   ?? '';
        $search  = trim($_GET['search'] ?? '');
        $pageNum = currentPageNum();

        $total     = $this->users->countAll($role, $search);
        $paginator = new Paginator($total, ITEMS_PER_PAGE, $pageNum);
        $usersList = $this->users->getAllPaginated($paginator->offset(), ITEMS_PER_PAGE, $role, $search);

        $pageTitle = 'Manage Users';
        require_once __DIR__ . '/../views/users/index.php';
    }

    // -------------------------------------------------------------------------
    // Create user — show form
    // -------------------------------------------------------------------------
    public function create(): void
    {
        Auth::requireRole('admin');

        $specializations = $this->specs->getAll();
        $pageTitle       = 'Create User';
        require_once __DIR__ . '/../views/users/create.php';
    }

    // -------------------------------------------------------------------------
    // Create user — process POST
    // -------------------------------------------------------------------------
    public function store(): void
    {
        Auth::requireRole('admin');

        if (!CSRF::validateToken($_POST['csrf_token'] ?? '')) {
            flashMessage('error', 'Invalid request.');
            redirect('index.php?page=users&action=create');
        }

        $name  = trim($_POST['name']  ?? '');
        $email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
        $pass  = $_POST['password']   ?? '';
        $role  = $_POST['role']       ?? 'patient';
        $phone = trim($_POST['phone'] ?? '');

        // Validation
        $errors = [];
        if (strlen($name) < 2)               $errors[] = 'Name must be at least 2 characters.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email address.';
        if (strlen($pass) < 8)               $errors[] = 'Password must be at least 8 characters.';
        if (!in_array($role, ['admin','doctor','patient'])) $errors[] = 'Invalid role.';
        if ($this->users->emailExists($email)) $errors[] = 'Email already in use.';

        if ($errors) {
            flashMessage('error', implode(' ', $errors));
            redirect('index.php?page=users&action=create');
        }

        // Create user record with hashed password
        $hash   = password_hash($pass, PASSWORD_BCRYPT);
        $userId = $this->users->create([
            'name'     => $name,
            'email'    => $email,
            'password' => $hash,
            'role'     => $role,
            'phone'    => $phone,
        ]);

        // If doctor, also create doctors record
        if ($role === 'doctor') {
            $specId  = (int) ($_POST['specialization_id']   ?? 0);
            $fee     = (float) ($_POST['consultation_fee']  ?? 0);
            $days    = isset($_POST['available_days']) ? implode(',', $_POST['available_days']) : 'Sun,Mon,Tue,Wed,Thu';
            $bio     = trim($_POST['bio'] ?? '');

            if ($specId > 0) {
                $this->doctors->create([
                    'user_id'           => $userId,
                    'specialization_id' => $specId,
                    'bio'               => $bio,
                    'consultation_fee'  => $fee,
                    'available_days'    => $days,
                ]);
            }
        }

        flashMessage('success', 'User created successfully.');
        redirect('index.php?page=users');
    }

    // -------------------------------------------------------------------------
    // Edit user — show form
    // -------------------------------------------------------------------------
    public function edit(): void
    {
        Auth::requireRole('admin');

        $id         = (int) ($_GET['id'] ?? 0);
        $targetUser = $this->users->findById($id);
        if (!$targetUser) {
            flashMessage('error', 'User not found.');
            redirect('index.php?page=users');
        }

        $specializations = $this->specs->getAll();
        $doctorRecord    = $targetUser['role'] === 'doctor'
            ? $this->doctors->findByUserId($id)
            : null;

        $pageTitle = 'Edit User: ' . sanitize($targetUser['name']);
        require_once __DIR__ . '/../views/users/edit.php';
    }

    // -------------------------------------------------------------------------
    // Edit user — process POST
    // -------------------------------------------------------------------------
    public function update(): void
    {
        Auth::requireRole('admin');

        if (!CSRF::validateToken($_POST['csrf_token'] ?? '')) {
            flashMessage('error', 'Invalid request.');
            redirect('index.php?page=users');
        }

        $id         = (int) ($_POST['user_id'] ?? 0);
        $targetUser = $this->users->findById($id);
        if (!$targetUser) {
            flashMessage('error', 'User not found.');
            redirect('index.php?page=users');
        }

        $name  = trim($_POST['name']  ?? '');
        $phone = trim($_POST['phone'] ?? '');

        // Handle avatar upload if a new file is provided, otherwise keep existing avatar 
        $avatar = $targetUser['avatar'];
        if (!empty($_FILES['avatar']['tmp_name'])) {
            $uploaded = $this->handleAvatarUpload($_FILES['avatar'], $id);
            if ($uploaded === false) {
                flashMessage('error', 'Avatar upload failed. JPEG/PNG only, max 1MB.');
                redirect('index.php?page=users&action=edit&id=' . $id);
            }
            $avatar = $uploaded;
        }

        $this->users->update($id, ['name' => $name, 'phone' => $phone, 'avatar' => $avatar]);

        // Update doctor fields if applicable and if user is still a doctor (role cannot be changed here)
        if ($targetUser['role'] === 'doctor') {
            $doctor = $this->doctors->findByUserId($id);
            if ($doctor) {
                $specId = (int) ($_POST['specialization_id']  ?? $doctor['specialization_id']);
                $fee    = (float) ($_POST['consultation_fee'] ?? $doctor['consultation_fee']);
                $days   = isset($_POST['available_days'])
                    ? implode(',', $_POST['available_days'])
                    : $doctor['available_days'];
                $bio    = trim($_POST['bio'] ?? $doctor['bio']);

                $this->doctors->update($doctor['id'], [
                    'specialization_id' => $specId,
                    'bio'               => $bio,
                    'consultation_fee'  => $fee,
                    'available_days'    => $days,
                ]);
            }
        }

        flashMessage('success', 'User updated successfully.');
        redirect('index.php?page=users');
    }

    // -------------------------------------------------------------------------
    // Toggle active/inactive
    // -------------------------------------------------------------------------
    public function toggleActive(): void
    {
        Auth::requireRole('admin');

        if (!CSRF::validateToken($_POST['csrf_token'] ?? '')) {
            flashMessage('error', 'Invalid request.');
            redirect('index.php?page=users');
        }

        $id      = (int) ($_POST['user_id'] ?? 0);
        $current = Auth::currentUser();

        // Admin cannot deactivate their own account
        if ($id === $current['id']) {
            flashMessage('error', 'You cannot deactivate your own account.');
            redirect('index.php?page=users');
        }

        $this->users->toggleActive($id);
        flashMessage('success', 'Account status updated.');
        redirect('index.php?page=users');
    }

    // -------------------------------------------------------------------------
    // Change password (admin sets new password for a user)
    // -------------------------------------------------------------------------
    public function changePassword(): void
    {
        Auth::requireRole('admin');

        if (!CSRF::validateToken($_POST['csrf_token'] ?? '')) {
            flashMessage('error', 'Invalid request.');
            redirect('index.php?page=users');
        }

        $id   = (int) ($_POST['user_id'] ?? 0);
        $pass = $_POST['new_password'] ?? '';

        if (strlen($pass) < 8) {
            flashMessage('error', 'Password must be at least 8 characters.');
            redirect('index.php?page=users&action=edit&id=' . $id);
        }

        $this->users->updatePassword($id, password_hash($pass, PASSWORD_BCRYPT));
        flashMessage('success', 'Password updated.');
        redirect('index.php?page=users&action=edit&id=' . $id);
    }

    // -------------------------------------------------------------------------
    // Private: handle avatar upload
    // Returns path string on success, false on failure
    // -------------------------------------------------------------------------
    private function handleAvatarUpload(array $file, int $userId): string|false
    {
        if ($file['error'] !== UPLOAD_ERR_OK) return false;
        if ($file['size'] > MAX_AVATAR_SIZE)  return false;

        // Validate it's a real image
        $imageInfo = @getimagesize($file['tmp_name']);
        if ($imageInfo === false) return false;
        if (!in_array($imageInfo['mime'], ['image/jpeg', 'image/png'])) return false;

        $ext      = $imageInfo['mime'] === 'image/jpeg' ? 'jpg' : 'png';
        $filename = 'avatar_' . $userId . '_' . time() . '.' . $ext;
        $destPath = UPLOAD_AVATARS . $filename;

        if (!move_uploaded_file($file['tmp_name'], $destPath)) return false;

        return $destPath;
    }
}
