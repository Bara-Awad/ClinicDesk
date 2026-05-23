<?php
/**
 * ClinicDesk — Front Controller
 *
 * Every HTTP request passes through this file.
 * Routing: $_GET['page']   → controller
 *          $_GET['action'] → method on that controller
 *
 * Security: Auth::requireRole() is called inside each controller action.
 *           هذا الملف فقط يوجّه الطلبات ولا يتحقق من الصلاحيات
 */

// ── Bootstrap ────────────────────────────────────────────────────────────────
define('ROOT', __DIR__);

require_once ROOT . '/config/config.php';
require_once ROOT . '/config/database.php';
require_once ROOT . '/core/helpers.php';
require_once ROOT . '/core/CSRF.php';
require_once ROOT . '/core/Auth.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// ── Routing ──────────────────────────────────────────────────────────────────
$page   = preg_replace('/[^a-z0-9_]/', '', strtolower($_GET['page']   ?? 'login'));
$action = preg_replace('/[^a-z0-9_]/', '', strtolower($_GET['action'] ?? 'index'));

// Special case: error pages
if ($page === 'error') {
    $code = (int) ($_GET['code'] ?? 404);
    $view = ROOT . '/views/errors/' . $code . '.php';
    if (file_exists($view)) {
        require_once $view;
    } else {
        http_response_code(404);
        echo '<h1>404 — Page not found</h1>';
    }
    exit();
}

// ── Controller Map ────────────────────────────────────────────────────────────
// Maps page → [controller file, controller class]
$routes = [
    'login'          => ['controllers/AuthController.php',         'AuthController'],
    'logout'         => ['controllers/AuthController.php',         'AuthController'],
    'dashboard'      => ['controllers/DashboardController.php',    'DashboardController'],
    'users'          => ['controllers/UserController.php',         'UserController'],
    'doctors'        => ['controllers/DoctorController.php',       'DoctorController'],
    'appointments'   => ['controllers/AppointmentController.php',  'AppointmentController'],
    'prescriptions'  => ['controllers/PrescriptionController.php', 'PrescriptionController'],
    'reports'        => ['controllers/ReportController.php',       'ReportController'],
];

if (!isset($routes[$page])) {
    http_response_code(404);
    require_once ROOT . '/views/errors/404.php';
    exit();
}

[$controllerFile, $controllerClass] = $routes[$page];
require_once ROOT . '/' . $controllerFile;

$controller = new $controllerClass();

// ── Action Map ────────────────────────────────────────────────────────────────
// Action routing is handled below by the match() expression

// Determine method to call
$methodName = match (true) {

    // Auth
    $page === 'login'  && $action === 'index'   => 'showLogin',
    $page === 'login'  && $action === 'process' => 'processLogin',
    $page === 'logout'                           => 'processLogout',

    // Dashboard
    $page === 'dashboard'                        => 'index',

    // Users
    $page === 'users' && $action === 'index'           => 'index',
    $page === 'users' && $action === 'create'          => 'create',
    $page === 'users' && $action === 'store'           => 'store',
    $page === 'users' && $action === 'edit'            => 'edit',
    $page === 'users' && $action === 'update'          => 'update',
    $page === 'users' && $action === 'toggle'          => 'toggleActive',
    $page === 'users' && $action === 'change_password' => 'changePassword',

    // Doctors
    $page === 'doctors' && $action === 'index'                => 'index',
    $page === 'doctors' && $action === 'edit'                 => 'edit',
    $page === 'doctors' && $action === 'update'               => 'update',
    $page === 'doctors' && $action === 'specializations'      => 'specializations',
    $page === 'doctors' && $action === 'add_specialization'   => 'addSpecialization',
    $page === 'doctors' && $action === 'delete_specialization'=> 'deleteSpecialization',

    // Appointments
    $page === 'appointments' && $action === 'index'         => 'index',
    $page === 'appointments' && $action === 'book'          => 'book',
    $page === 'appointments' && $action === 'store'         => 'store',
    $page === 'appointments' && $action === 'show'          => 'show',
    $page === 'appointments' && $action === 'update_status' => 'updateStatus',
    $page === 'appointments' && $action === 'cancel'        => 'cancel',

    // Prescriptions
    $page === 'prescriptions' && $action === 'index'    => 'index',
    $page === 'prescriptions' && $action === 'create'   => 'create',
    $page === 'prescriptions' && $action === 'store'    => 'store',
    $page === 'prescriptions' && $action === 'download' => 'download',

    // Reports
    $page === 'reports'                                  => 'index',

    // Default
    default => 'index',
};

// Call the method
if (method_exists($controller, $methodName)) {
    $controller->$methodName();
} else {
    http_response_code(404);
    require_once ROOT . '/views/errors/404.php';
}
