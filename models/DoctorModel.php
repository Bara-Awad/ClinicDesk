<?php
/**
 * ClinicDesk - DoctorModel
 */

require_once __DIR__ . '/BaseModel.php';

class DoctorModel extends BaseModel
{
    private const BASE_SELECT = "
        SELECT d.*, u.name, u.email, u.phone, u.avatar, u.is_active,
               s.name AS specialization_name
        FROM doctors d
        JOIN users u ON u.id = d.user_id
        JOIN specializations s ON s.id = d.specialization_id
    ";

    /**
     * Find a doctor record by the associated user ID.
     */
    public function findByUserId(int $userId): ?array
    {
        return $this->fetchOne(
            self::BASE_SELECT . ' WHERE d.user_id = ? LIMIT 1',
            'i', [$userId]
        );
    }

    /**
     * Find a doctor record by the doctors.id primary key.
     */
    public function findById(int $doctorId): ?array
    {
        return $this->fetchOne(
            self::BASE_SELECT . ' WHERE d.id = ? LIMIT 1',
            'i', [$doctorId]
        );
    }

    /**
     * All doctors — used for dropdown lists (book appointment form).
     */
    public function getAll(): array
    {
        return $this->fetchAll(
            self::BASE_SELECT . " WHERE u.is_active = 1 ORDER BY u.name ASC"
        );
    }

    /**
     * Paginated list for admin doctor management page.
     */
    public function getAllPaginated(int $offset, int $limit): array
    {
        return $this->fetchAll(
            self::BASE_SELECT . ' ORDER BY u.name ASC LIMIT ? OFFSET ?',
            'ii', [$limit, $offset]
        );
    }

    /** يعدّ إجمالي الأطباء */
    public function countAll(): int
    {
        return $this->fetchCount('SELECT COUNT(*) FROM doctors');
    }

    /**
     * Create a new doctors record (the users record must exist first).
     *
     * @param array $data  Keys: user_id, specialization_id, bio, consultation_fee, available_days.
     * @return int  New doctor ID.
     */
    public function create(array $data): int
    {
        $this->execute(
            'INSERT INTO doctors (user_id, specialization_id, bio, consultation_fee, available_days)
             VALUES (?, ?, ?, ?, ?)',
            'iisds',
            [
                $data['user_id'],
                $data['specialization_id'],
                $data['bio'] ?? null,
                $data['consultation_fee'] ?? 0.00,
                $data['available_days'] ?? 'Sun,Mon,Tue,Wed,Thu',
            ]
        );
        return $this->db->lastInsertId();
    }

    /**
     * Update doctor profile fields.
     *
     * @param int   $doctorId  doctors.id (not user_id)
     * @param array $data      Keys: specialization_id, bio, consultation_fee, available_days, photo.
     */
    public function update(int $doctorId, array $data): bool
    {
        $result = $this->execute(
            'UPDATE doctors SET specialization_id = ?, bio = ?, consultation_fee = ?, available_days = ?
             WHERE id = ?',
            'isdsi',
            [
                $data['specialization_id'],
                $data['bio'] ?? null,
                $data['consultation_fee'] ?? 0.00,
                $data['available_days'] ?? 'Sun,Mon,Tue,Wed,Thu',
                $doctorId,
            ]
        );
        return $result === true;
    }

    /**
     * Update doctor profile photo path.
     */
    public function updatePhoto(int $doctorId, string $photoPath): bool
    {
        $result = $this->execute(
            'UPDATE users SET avatar = ?
             WHERE id = (SELECT user_id FROM doctors WHERE id = ?)',
            'si', [$photoPath, $doctorId]
        );
        return $result === true;
    }

    /**
     * Returns the doctor's available days as an array.
     * e.g. ['Sun', 'Mon', 'Tue']
     */
    public function getAvailableDays(int $doctorId): array
    {
        $row = $this->fetchOne(
            'SELECT available_days FROM doctors WHERE id = ?',
            'i', [$doctorId]
        );
        if (!$row) return [];
        return array_map('trim', explode(',', $row['available_days']));
    }
}
