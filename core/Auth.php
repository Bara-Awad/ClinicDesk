<?php
/**
 * ClinicDesk - Authentication Helper
 *
 * Manages session-based login/logout and role enforcement.
 * يتحكم في تسجيل الدخول والخروج والتحقق من صلاحيات كل مستخدم.
 */

require_once __DIR__ . '/../core/helpers.php';

class Auth
{
    /**
     * Stores the authenticated user in the session.
     * 
     * @param array $user  Row from the users table (id, name, role, email).
     */
    public static function login(array $user): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $_SESSION['user'] = [
            'id'    => (int) $user['id'],
            'name'  => $user['name'],
            'email' => $user['email'],
            'role'  => $user['role'],
        ];

        // Mitigate session-fixation: give the authenticated session a new ID
        session_regenerate_id(true);
    }

    /**
     * Destroys the session and redirects to the login page يحذف الجلسة ويعيد التوجيه لصفحة الدخول
     */
    public static function logout(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        session_unset();
        session_destroy();
        redirect('index.php?page=login');
    }

    /**
     * Returns true if a user is currently logged in.
     */
    public static function check(): bool
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        return isset($_SESSION['user']);
    }

    /**
     * Returns the current user array, or null if not logged in.
     *
     * @return array{id:int,name:string,email:string,role:string}|null
     */
    public static function currentUser(): ?array
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        return $_SESSION['user'] ?? null;
    }

    /**
     * Returns the role of the current user, or an empty string.
     */
    public static function role(): string
    {
        return self::currentUser()['role'] ?? '';
    }

    /**
     * Enforces authentication and role restrictions.
     *
     * Call this as the VERY FIRST line of every controller action.
     * - If not logged in  → redirect to login page.
     * - If wrong role     → redirect to 403 error page.
     *
     * @param string ...$roles  One or more allowed roles (e.g. "admin", "doctor").
     */
    public static function requireRole(string ...$roles): void
    {
        if (!self::check()) {
            redirect('index.php?page=login');
        }

        if (!in_array(self::role(), $roles, true)) {
            redirect('index.php?page=error&code=403');
        }
    }

    /**
     * Convenience: redirect to dashboard after login based on role.يعيد التوجيه للوحة التحكم بعد تسجيل الدخول
     */
    public static function redirectToDashboard(): void
    {
        redirect('index.php?page=dashboard');
    }
}
