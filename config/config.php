<?php
/**
 * ClinicDesk - Application Configuration
 * تعريف الثوابت المستخدمة في جميع أنحاء التطبيق
 */

define('APP_NAME',        'ClinicDesk');
define('APP_VERSION',     '1.0.0');
define('BASE_URL',        'http://localhost/clinicdesk');  

// Pagination
define('ITEMS_PER_PAGE',  10);

// Upload limits (bytes)
define('MAX_AVATAR_SIZE',       1 * 1024 * 1024);   // 1 MB
define('MAX_DOCTOR_PHOTO_SIZE', 1 * 1024 * 1024);   // 1 MB
define('MAX_PRESCRIPTION_SIZE', 3 * 1024 * 1024);   // 3 MB

// Upload paths (relative to project root)
define('UPLOAD_AVATARS',       'public/uploads/avatars/');
define('UPLOAD_DOCTOR_PHOTOS', 'public/uploads/doctor_photos/');
define('UPLOAD_PRESCRIPTIONS', 'public/uploads/prescriptions/');

// Allowed appointment time slots
define('APPOINTMENT_SLOTS', [
    '09:00', '09:30', '10:00', '10:30', '11:00', '11:30',
    '12:00', '12:30', '13:00', '13:30', '14:00', '14:30',
    '15:00', '15:30', '16:00',
]);

// Turn off error display in production; log errors instead
ini_set('display_errors', 0);
ini_set('log_errors',     1);
error_reporting(E_ALL);
