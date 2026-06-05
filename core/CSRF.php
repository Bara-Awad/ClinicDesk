<?php
/**
 * ClinicDesk - CSRF Protection (Cross Site Request Forgery)
 *
 * Every POST form must include a hidden CSRF token.
 * Every POST handler must validate it before processing data.
 *
 * Usage in view:
 *   <input type="hidden" name="csrf_token" value="<?= CSRF::generateToken() ?>">
 *
 * Usage in controller:
 *   if (!CSRF::validateToken($_POST['csrf_token'] ?? '')) {
 *       flashMessage('error', 'Invalid request. Please try again.');
 *       redirect('...');
 *   }
 */

class CSRF
{
    private const SESSION_KEY = 'csrf_token';

    /**
     * Generates (or re-uses) a cryptographically secure token for this session.
     * Stores it in $_SESSION and returns it for embedding in forms.
     *
     * @return string  64-character hex token.
     */
    public static function generateToken(): string
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        if (empty($_SESSION[self::SESSION_KEY])) {
            $_SESSION[self::SESSION_KEY] = bin2hex(random_bytes(32));
        }

        return $_SESSION[self::SESSION_KEY];
    }

    /**
     * Validates the submitted token against the session token.
     * Uses hash_equals() to prevent timing attacks.
     *
     * @param string $submittedToken  Value from $_POST['csrf_token'].
     * @return bool  True if valid, false otherwise.
     */
    public static function validateToken(string $submittedToken): bool
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $sessionToken = $_SESSION[self::SESSION_KEY] ?? '';

        if ($sessionToken === '' || $submittedToken === '') {
            return false;
        }

        return hash_equals($sessionToken, $submittedToken);
    }

    /**
     * Outputs a ready-to-use hidden input field.
     * Convenience wrapper — call inside any form.
     */
    public static function field(): string
    {
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(self::generateToken(), ENT_QUOTES, 'UTF-8') . '">';
    }
}
