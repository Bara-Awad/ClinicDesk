<?php
/**
 * ClinicDesk - UserModel
 * Handles all database operations for the users table
 */

require_once __DIR__ . '/BaseModel.php';

class UserModel extends BaseModel
{
    /**
     * Find a user by primary key.
     */
    public function findById(int $id): ?array
    {
        return $this->fetchOne(
            'SELECT * FROM users WHERE id = ? LIMIT 1',
            'i', [$id]
        );
    }

    /**
     * Find a user by email address (used during login).
     */
    public function findByEmail(string $email): ?array
    {
        return $this->fetchOne(
            'SELECT * FROM users WHERE email = ? LIMIT 1',
            's', [$email]
        );
    }

    /**
     * Create a new user. Password must already be hashed before calling.
     *
     * @param array $data  Keys: name, email, password, role, phone (optional).
     * @return int  The new user's ID.
     */
    public function create(array $data): int
    {
        $this->execute(
            'INSERT INTO users (name, email, password, role, phone) VALUES (?, ?, ?, ?, ?)',
            'sssss',
            [
                $data['name'],
                $data['email'],
                $data['password'],
                $data['role'] ?? 'patient',
                $data['phone'] ?? null,
            ]
        );
        return $this->db->lastInsertId();
    }

    /**
     * Update basic profile fields for a user.
     *
     * @param int   $id
     * @param array $data  Keys: name, phone, avatar (optional).
     * @return bool
     */
    public function update(int $id, array $data): bool
    {
        $result = $this->execute(
            'UPDATE users SET name = ?, phone = ?, avatar = ? WHERE id = ?',
            'sssi',
            [
                $data['name'],
                $data['phone'] ?? null,
                $data['avatar'] ?? null,
                $id,
            ]
        );
        return $result === true;
    }

    /**
     * Update only the password hash for a user.
     */
    public function updatePassword(int $id, string $newHash): bool
    {
        $result = $this->execute(
            'UPDATE users SET password = ? WHERE id = ?',
            'si', [$newHash, $id]
        );
        return $result === true;
    }

    /**
     * Paginated list of all users, optionally filtered by role.
     *
     * @param int    $offset  SQL OFFSET value from Paginator.
     * @param int    $limit   Rows per page.
     * @param string $role    Filter by role, or '' for all.
     * @param string $search  Search by name or email.
     * @return array
     */
    public function getAllPaginated(int $offset, int $limit, string $role = '', string $search = ''): array
    {
        $conditions = [];
        $params     = [];
        $types      = '';

        // إضافة فلتر الدور إذا تم تحديده
        if ($role !== '') {
            $conditions[] = 'role = ?';
            $params[]     = $role;
            $types       .= 's';
        }

        // إضافة فلتر البحث إذا تم إدخاله
        if ($search !== '') {
            $conditions[] = '(name LIKE ? OR email LIKE ?)';
            $like         = '%' . $search . '%';
            $params[]     = $like;
            $params[]     = $like;
            $types       .= 'ss';
        }

        $where = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';

        $params[] = $limit;
        $params[] = $offset;
        $types   .= 'ii';

        return $this->fetchAll(
            "SELECT * FROM users $where ORDER BY created_at DESC LIMIT ? OFFSET ?",
            $types, $params
        );
    }

    /**
     * Count total users, optionally filtered by role and search.
     */
    public function countAll(string $role = '', string $search = ''): int
    {
        $conditions = [];
        $params     = [];
        $types      = '';

        if ($role !== '') {
            $conditions[] = 'role = ?';
            $params[]     = $role;
            $types       .= 's';
        }

        if ($search !== '') {
            $conditions[] = '(name LIKE ? OR email LIKE ?)';
            $like         = '%' . $search . '%';
            $params[]     = $like;
            $params[]     = $like;
            $types       .= 'ss';
        }

        $where = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';

        return $this->fetchCount("SELECT COUNT(*) FROM users $where", $types, $params);
    }

    /**
     * Flip a user's is_active flag between 0 and 1.
     */
    public function toggleActive(int $id): bool
    {
        $result = $this->execute(
            'UPDATE users SET is_active = 1 - is_active WHERE id = ?',
            'i', [$id]
        );
        return $result === true;
    }

    /**
     * Count users grouped by role — used for admin dashboard stats.
     * Returns associative array: ['admin' => N, 'doctor' => N, 'patient' => N]
     */
    public function countByRole(): array
    {
        $rows = $this->fetchAll(
            'SELECT role, COUNT(*) as total FROM users GROUP BY role'
        );
        $map = ['admin' => 0, 'doctor' => 0, 'patient' => 0];
        foreach ($rows as $row) {
            $map[$row['role']] = (int) $row['total'];
        }
        return $map;
    }

    /**
     * Check if an email is already taken (excluding a specific user ID).
     */
    public function emailExists(string $email, int $excludeId = 0): bool
    {
        $count = $this->fetchCount(
            'SELECT COUNT(*) FROM users WHERE email = ? AND id != ?',
            'si', [$email, $excludeId]
        );
        return $count > 0;
    }
}
