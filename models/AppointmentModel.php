<?php
/**
 * ClinicDesk - AppointmentModel
 */

require_once __DIR__ . '/BaseModel.php';

class AppointmentModel extends BaseModel
{
    private const BASE_SELECT = "
        SELECT a.*,
               p.name  AS patient_name,  p.email AS patient_email,
               u.name  AS doctor_name,   u.email AS doctor_email,
               s.name  AS specialization_name,
               d.id    AS doctor_record_id,
               d.consultation_fee,
               pr.id   AS prescription_id
        FROM appointments a
        JOIN users    p ON p.id = a.patient_id
        JOIN doctors  d ON d.id = a.doctor_id
        JOIN users    u ON u.id = d.user_id
        JOIN specializations s ON s.id = d.specialization_id
        LEFT JOIN prescriptions pr ON pr.appointment_id = a.id
    ";

    /**
     * Insert a new appointment.
     * Returns false if the UNIQUE constraint fires (double-booking).
     */
    public function book(array $data): bool
    {
        $result = $this->execute(
            'INSERT INTO appointments (patient_id, doctor_id, appt_date, appt_time, reason)
             VALUES (?, ?, ?, ?, ?)',
            'iisss',
            [
                $data['patient_id'],
                $data['doctor_id'],
                $data['appt_date'],
                $data['appt_time'],
                $data['reason'] ?? null,
            ]
        );
        return $result !== false;
    }

    /**
     * Check whether a doctor already has an appointment at the given date/time.
     */
    public function hasConflict(int $doctorId, string $date, string $time): bool
    {
        $count = $this->fetchCount(
            "SELECT COUNT(*) FROM appointments
             WHERE doctor_id = ? AND appt_date = ? AND appt_time = ?
               AND status != 'cancelled'",
            'iss', [$doctorId, $date, $time]
        );
        return $count > 0;
    }

    /**
     * Find a single appointment by ID with all JOINed info.
     */
    public function findById(int $id): ?array
    {
        return $this->fetchOne(
            self::BASE_SELECT . ' WHERE a.id = ? LIMIT 1',
            'i', [$id]
        );
    }

    /**
     * Paginated appointments for a specific patient, with optional filters يجلب مواعيد مريض معين مع ترقيم الصفحات والفلترة
     */
    public function getByPatient(int $patientId, int $offset, int $limit, array $filters = []): array
    {
        [$where, $types, $params] = $this->buildFilters(['patient_id' => $patientId], $filters);
        $params[] = $limit;
        $params[] = $offset;
        $types   .= 'ii';

        return $this->fetchAll(
            self::BASE_SELECT . " $where ORDER BY a.appt_date DESC, a.appt_time DESC LIMIT ? OFFSET ?",
            $types, $params
        );
    }

    /**
     * Paginated appointments for a specific doctor, with optional filters.
     */
    public function getByDoctor(int $doctorId, int $offset, int $limit, array $filters = []): array
    {
        [$where, $types, $params] = $this->buildFilters(['doctor_id' => $doctorId], $filters);
        $params[] = $limit;
        $params[] = $offset;
        $types   .= 'ii';

        return $this->fetchAll(
            self::BASE_SELECT . " $where ORDER BY a.appt_date ASC, a.appt_time ASC LIMIT ? OFFSET ?",
            $types, $params
        );
    }

    /**
     * Paginated appointments for admin (all), with optional filters.
     */
    public function getAll(int $offset, int $limit, array $filters = []): array
    {
        [$where, $types, $params] = $this->buildFilters([], $filters);
        $params[] = $limit;
        $params[] = $offset;
        $types   .= 'ii';

        return $this->fetchAll(
            self::BASE_SELECT . " $where ORDER BY a.appt_date DESC, a.appt_time DESC LIMIT ? OFFSET ?",
            $types, $params
        );
    }

    /**
     * Count appointments matching scope + filters (for Paginator).
     *
     * @param array $scope    e.g. ['patient_id' => 5] or ['doctor_id' => 3] or []
     * @param array $filters  User-supplied filters فلاتر المستخدم الاختيارية
     */
    public function countFiltered(array $scope, array $filters = []): int
    {
        [$where, $types, $params] = $this->buildFilters($scope, $filters);
        return $this->fetchCount(
            "SELECT COUNT(*) FROM appointments a
             JOIN doctors d ON d.id = a.doctor_id
             JOIN users   u ON u.id = d.user_id
             JOIN users   p ON p.id = a.patient_id
             $where",
            $types, $params
        );
    }

    /**
     * Update appointment status (and optional doctor notes).
     */
    public function updateStatus(int $id, string $status, string $notes = ''): bool
    {
        $result = $this->execute(
            'UPDATE appointments SET status = ?, doctor_notes = ? WHERE id = ?',
            'ssi', [$status, $notes ?: null, $id]
        );
        return $result === true;
    }

    /**
     * يلغي موعداً من قِبَل المريض يشترط أن يكون الموعد بحالة - pending
     */
    public function cancel(int $id, int $patientId): bool
    {
        $result = $this->execute(
            "UPDATE appointments SET status = 'cancelled' WHERE id = ? AND patient_id = ? AND status = 'pending'",
            'ii', [$id, $patientId]
        );
        return $result === true && $this->db->affectedRows() > 0;
    }

    /**
     * Today's appointments for a doctor.
     */
    public function getTodayByDoctor(int $doctorId): array
    {
        return $this->fetchAll(
            self::BASE_SELECT . "
             WHERE a.doctor_id = ? AND a.appt_date = CURDATE()
               AND a.status NOT IN ('cancelled')
             ORDER BY a.appt_time ASC",
            'i', [$doctorId]
        );
    }

    /**
     * Upcoming appointments (today or future) for a patient.
     */
    public function getUpcomingByPatient(int $patientId, int $limit = 5): array
    {
        return $this->fetchAll(
            self::BASE_SELECT . "
             WHERE a.patient_id = ? AND a.appt_date >= CURDATE()
               AND a.status IN ('pending','confirmed')
             ORDER BY a.appt_date ASC, a.appt_time ASC LIMIT ?",
            'ii', [$patientId, $limit]
        );
    }

    /**
     * Count appointments today.
     */
    public function countToday(): int
    {
        return $this->fetchCount(
            "SELECT COUNT(*) FROM appointments WHERE appt_date = CURDATE()"
        );
    }

    /**
     * Count appointments this week grouped by status.
     */
    public function countThisWeekByStatus(): array
    {
        $rows = $this->fetchAll(
            "SELECT status, COUNT(*) as total FROM appointments
             WHERE WEEK(appt_date) = WEEK(NOW()) AND YEAR(appt_date) = YEAR(NOW())
             GROUP BY status"
        );
        $map = ['pending' => 0, 'confirmed' => 0, 'completed' => 0, 'cancelled' => 0];
        foreach ($rows as $row) {
            $map[$row['status']] = (int) $row['total'];
        }
        return $map;
    }

    /**
     * 5 most recent appointments for admin dashboard يجلب آخر 5 مواعيد للوحة تحكم المدير 
     */
    public function getRecentForAdmin(int $limit = 5): array
    {
        return $this->fetchAll(
            self::BASE_SELECT . ' ORDER BY a.created_at DESC LIMIT ?',
            'i', [$limit]
        );
    }

    /**
     * Count this month's appointments for a doctor.
     */
    public function countThisMonthByDoctor(int $doctorId): int
    {
        return $this->fetchCount(
            "SELECT COUNT(*) FROM appointments
             WHERE doctor_id = ?
               AND MONTH(appt_date) = MONTH(NOW())
               AND YEAR(appt_date)  = YEAR(NOW())",
            'i', [$doctorId]
        );
    }

    /**
     * Appointments per day for the last 14 days — used for chart.
     */
    public function getDailyCountsLast14Days(): array
    {
        return $this->fetchAll(
            "SELECT appt_date, COUNT(*) as total
             FROM appointments
             WHERE appt_date >= DATE_SUB(CURDATE(), INTERVAL 14 DAY)
             GROUP BY appt_date ORDER BY appt_date"
        );
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    /**
     * Builds a dynamic WHERE clause from scope + user filters.
     *
     * @return array{0: string, 1: string, 2: array}  [WHERE clause, types string, params array]
     */
    private function buildFilters(array $scope, array $filters): array
    {
        $conditions = [];
        $params     = [];
        $types      = '';

        // Fixed scope (ownership)
        if (isset($scope['patient_id'])) {
            $conditions[] = 'a.patient_id = ?';
            $params[]     = $scope['patient_id'];
            $types       .= 'i';
        }
        if (isset($scope['doctor_id'])) {
            $conditions[] = 'a.doctor_id = ?';
            $params[]     = $scope['doctor_id'];
            $types       .= 'i';
        }

        // User filters
        if (!empty($filters['status'])) {
            $conditions[] = 'a.status = ?';
            $params[]     = $filters['status'];
            $types       .= 's';
        }
        if (!empty($filters['doctor_id'])) {
            $conditions[] = 'a.doctor_id = ?';
            $params[]     = (int) $filters['doctor_id'];
            $types       .= 'i';
        }
        if (!empty($filters['start_date'])) {
            $conditions[] = 'a.appt_date >= ?';
            $params[]     = $filters['start_date'];
            $types       .= 's';
        }
        if (!empty($filters['end_date'])) {
            $conditions[] = 'a.appt_date <= ?';
            $params[]     = $filters['end_date'];
            $types       .= 's';
        }
        if (!empty($filters['patient_name'])) {
            $conditions[] = 'p.name LIKE ?';
            $params[]     = '%' . $filters['patient_name'] . '%';
            $types       .= 's';
        }

        $where = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';
        return [$where, $types, $params];
    }
}
