<?php
/**
 * ClinicDesk - PrescriptionModel
 */

require_once __DIR__ . '/BaseModel.php';

class PrescriptionModel extends BaseModel
{
    public function findByAppointmentId(int $apptId): ?array
    {
        return $this->fetchOne(
            'SELECT * FROM prescriptions WHERE appointment_id = ? LIMIT 1',
            'i', [$apptId]
        );
    }

    public function findById(int $id): ?array
    {
        return $this->fetchOne(
            'SELECT * FROM prescriptions WHERE id = ? LIMIT 1',
            'i', [$id]
        );
    }

    /**
     * Create a new prescription.
     *
     * @param array $data  Keys: appointment_id, diagnosis, medications, notes, file_path.
     * @return int  New prescription ID.
     */
    public function create(array $data): int
    {
        $this->execute(
            'INSERT INTO prescriptions (appointment_id, diagnosis, medications, notes, file_path)
             VALUES (?, ?, ?, ?, ?)',
            'issss',
            [
                $data['appointment_id'],
                $data['diagnosis'],
                $data['medications'],
                $data['notes'] ?? null,
                $data['file_path'] ?? null,
            ]
        );
        return $this->db->lastInsertId();
    }

    /** يحدّث بيانات الوصفة */
    public function update(int $id, array $data): bool
    {
        $result = $this->execute(
            'UPDATE prescriptions SET diagnosis = ?, medications = ?, notes = ?, file_path = ? WHERE id = ?',
            'ssssi',
            [
                $data['diagnosis'],
                $data['medications'],
                $data['notes'] ?? null,
                $data['file_path'] ?? null,
                $id,
            ]
        );
        return $result === true;
    }

    /**
     * All prescriptions for a patient (via appointment ownership).
     * Includes appointment and doctor info for display.
     */
    public function getByPatient(int $patientId): array
    {
        return $this->fetchAll(
            "SELECT pr.*, a.appt_date, a.appt_time,
                    u.name AS doctor_name, s.name AS specialization_name
             FROM prescriptions pr
             JOIN appointments a ON a.id = pr.appointment_id
             JOIN doctors      d ON d.id = a.doctor_id
             JOIN users        u ON u.id = d.user_id
             JOIN specializations s ON s.id = d.specialization_id
             WHERE a.patient_id = ?
             ORDER BY a.appt_date DESC",
            'i', [$patientId]
        );
    }

    /**
     * Find a prescription by ID and verify the requesting patient owns it.
     * Returns null if ownership check fails.
     */
    public function findForPatient(int $prescriptionId, int $patientId): ?array
    {
        return $this->fetchOne(
            "SELECT pr.* FROM prescriptions pr
             JOIN appointments a ON a.id = pr.appointment_id
             WHERE pr.id = ? AND a.patient_id = ? LIMIT 1",
            'ii', [$prescriptionId, $patientId]
        );
    }

    /**
     * Find a prescription by appointment ID and verify the requesting doctor owns it
     */
    public function findForDoctor(int $apptId, int $doctorId): ?array
    {
        return $this->fetchOne(
            "SELECT pr.* FROM prescriptions pr
             JOIN appointments a ON a.id = pr.appointment_id
             WHERE pr.appointment_id = ? AND a.doctor_id = ? LIMIT 1",
            'ii', [$apptId, $doctorId]
        );
    }
}
