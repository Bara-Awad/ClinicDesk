<?php
/**
 * ClinicDesk - AuthController
 */

require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/CSRF.php';
require_once __DIR__ . '/../core/helpers.php';
require_once __DIR__ . '/../models/UserModel.php';

class AuthController
{
    private UserModel $users;

    public function __construct()
    {
        $this->users = new UserModel();
    }

    // -------------------------------------------------------------------------
    // GET /index.php?page=login
    // -------------------------------------------------------------------------
    public function showLogin(): void
    {
        // Already logged in — go to dashboard
        if (Auth::check()) {
            Auth::redirectToDashboard();
        }

        $pageTitle = 'Login — ' . APP_NAME;
        require_once __DIR__ . '/../views/auth/login.php';
    }

    // -------------------------------------------------------------------------
    // POST /index.php?page=login&action=process
    // -------------------------------------------------------------------------
    public function processLogin(): void
    {
        // 1. Validate CSRF token
        if (!CSRF::validateToken($_POST['csrf_token'] ?? '')) {
            flashMessage('error', 'Invalid request. Please try again.');
            redirect('index.php?page=login');
        }

        // 2. Sanitize email
        $email    = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'] ?? '';

        // 3. Look up user البحث عن المستخدم في قاعدة البيانات
        $user = $this->users->findByEmail($email);

        if (!$user) {
            flashMessage('error', 'Invalid credentials.');
            redirect('index.php?page=login');
        }

        // 4. Check account is active
        if ((int) $user['is_active'] !== 1) {
            flashMessage('error', 'Account suspended. Contact admin.');
            redirect('index.php?page=login');
        }

        // 5. Verify password
        if (!password_verify($password, $user['password'])) {
            flashMessage('error', 'Invalid credentials.');
            redirect('index.php?page=login');
        }

        // 6. Create session
        Auth::login($user);
        redirect('index.php?page=dashboard');
    }

    // -------------------------------------------------------------------------
    // POST /index.php?page=logout
    // -------------------------------------------------------------------------
    public function processLogout(): void
    {
        if (!CSRF::validateToken($_POST['csrf_token'] ?? '')) {
            redirect('index.php?page=login');
        }
        Auth::logout();
    }
}
