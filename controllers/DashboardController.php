<?php
/**
 * ClinicDesk - DashboardController
 */

require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/helpers.php';
require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../models/DoctorModel.php';
require_once __DIR__ . '/../models/AppointmentModel.php';
require_once __DIR__ . '/../models/PrescriptionModel.php';

class DashboardController
{
    public function index(): void
    {
        Auth::requireRole('admin', 'doctor', 'patient');

        $role = Auth::role();
        $user = Auth::currentUser();

        match ($role) {
            'admin'   => $this->adminDashboard($user),
            'doctor'  => $this->doctorDashboard($user),
            'patient' => $this->patientDashboard($user),
        };
    }

    // -------------------------------------------------------------------------
    // Admin Dashboard
    // -------------------------------------------------------------------------
    private function adminDashboard(array $user): void
    {
        $userModel = new UserModel();
        $apptModel = new AppointmentModel();

        $stats = [
            'users_by_role'     => $userModel->countByRole(),
            'appointments_today'=> $apptModel->countToday(),
            'week_by_status'    => $apptModel->countThisWeekByStatus(),
            'recent'            => $apptModel->getRecentForAdmin(5),
            'chart_data'        => $apptModel->getDailyCountsLast14Days(),
        ];

        $pageTitle = 'Admin Dashboard';
        require_once __DIR__ . '/../views/dashboard/admin.php';
    }

    // -------------------------------------------------------------------------
    // Doctor Dashboard
    // -------------------------------------------------------------------------
    private function doctorDashboard(array $user): void
    {
        $doctorModel = new DoctorModel();
        $apptModel   = new AppointmentModel();

        $doctor = $doctorModel->findByUserId($user['id']);
        if (!$doctor) {
            flashMessage('error', 'Doctor profile not found.');
            redirect('index.php?page=login');
        }

        $doctorId = (int) $doctor['id'];

        $stats = [
            'today'        => $apptModel->getTodayByDoctor($doctorId),
            'month_total'  => $apptModel->countThisMonthByDoctor($doctorId),
            'week_status'  => $apptModel->countThisWeekByStatus(),
            'upcoming'     => $apptModel->getByDoctor($doctorId, 0, 5),
        ];

        $pageTitle = 'Doctor Dashboard';
        require_once __DIR__ . '/../views/dashboard/doctor.php';
    }

    // -------------------------------------------------------------------------
    // Patient Dashboard
    // -------------------------------------------------------------------------
    private function patientDashboard(array $user): void
    {
        $apptModel  = new AppointmentModel();
        $prescModel = new PrescriptionModel();

        $patientId = $user['id'];

        $stats = [
            'upcoming'     => $apptModel->getUpcomingByPatient($patientId, 5),
            'active_count' => $apptModel->countFiltered(['patient_id' => $patientId], ['status' => 'pending'])
                             + $apptModel->countFiltered(['patient_id' => $patientId], ['status' => 'confirmed']),
            'completed'    => $apptModel->countFiltered(['patient_id' => $patientId], ['status' => 'completed']),
            'prescriptions'=> $prescModel->getByPatient($patientId),
        ];

        $pageTitle = 'My Dashboard';
        require_once __DIR__ . '/../views/dashboard/patient.php';
    }
}
