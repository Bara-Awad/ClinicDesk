<?php
/**
 * ClinicDesk - ReportController
 * Admin-only: filtered appointment reports + CSV export.
 */

require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/CSRF.php';
require_once __DIR__ . '/../core/helpers.php';
require_once __DIR__ . '/../models/AppointmentModel.php';
require_once __DIR__ . '/../models/DoctorModel.php';

class ReportController
{
    public function index(): void
    {
        Auth::requireRole('admin');

        $apptModel   = new AppointmentModel();
        $doctorModel = new DoctorModel();
        $doctorsList = $doctorModel->getAll();

        $results    = [];
        $summary    = [];
        $filtered   = false;
        $errors     = [];

        // Form submitted
        if (isset($_GET['start_date'])) {
            $filtered  = true;
            $startDate = trim($_GET['start_date'] ?? '');
            $endDate   = trim($_GET['end_date']   ?? '');
            $doctorId  = (int) ($_GET['doctor_id'] ?? 0);
            $status    = trim($_GET['status']      ?? '');

            // Validate date range
            if (!$startDate || !$endDate) {
                $errors[] = 'Both start date and end date are required.';
            } elseif (strtotime($startDate) > strtotime($endDate)) {
                $errors[] = 'Start date must be on or before end date.';
            }

            if (!$errors) {
                $filters = [
                    'start_date' => $startDate,
                    'end_date'   => $endDate,
                    'doctor_id'  => $doctorId ?: '',
                    'status'     => $status,
                ];

                // Fetch all results (no pagination for reports)
                $results = $apptModel->getAll(0, 10000, $filters);

                // Build summary
                $summary = ['total' => count($results), 'by_status' => []];
                foreach ($results as $r) {
                    $s = $r['status'];
                    $summary['by_status'][$s] = ($summary['by_status'][$s] ?? 0) + 1;
                }

                // CSV export
                if (($_GET['export'] ?? '') === 'csv') {
                    $this->exportCsv($results);
                }
            }
        }

        $pageTitle = 'Appointment Reports';
        require_once __DIR__ . '/../views/reports/index.php';
    }

    // -------------------------------------------------------------------------
    // Stream CSV to browser
    // -------------------------------------------------------------------------
    private function exportCsv(array $results): never
    {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="appointments_report_' . date('Ymd') . '.csv"');

        $out = fopen('php://output', 'w');

        // Header row
        fputcsv($out, [
            'ID', 'Patient Name', 'Doctor Name', 'Specialization',
            'Date', 'Time', 'Status', 'Reason', 'Created At',
        ]);

        foreach ($results as $r) {
            fputcsv($out, [
                $r['id'],
                $r['patient_name'],
                $r['doctor_name'],
                $r['specialization_name'],
                $r['appt_date'],
                $r['appt_time'],
                $r['status'],
                $r['reason'] ?? '',
                $r['created_at'],
            ]);
        }

        fclose($out);
        exit();
    }
}
