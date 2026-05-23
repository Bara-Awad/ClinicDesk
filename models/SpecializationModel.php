<?php
/**
 * ClinicDesk - SpecializationModel
 */


require_once __DIR__ . '/BaseModel.php';

class SpecializationModel extends BaseModel
{
    /** يجلب جميع التخصصات مرتبة أبجدياً */
    public function getAll(): array
    {
        return $this->fetchAll('SELECT * FROM specializations ORDER BY name ASC');
    }

    /** يجلب تخصصاً بواسطة ID  */
    public function findById(int $id): ?array
    {
        return $this->fetchOne('SELECT * FROM specializations WHERE id = ? LIMIT 1', 'i', [$id]);
    }

    /** ينشئ تخصصاً جديداً ويعيد معرّفه */
    public function create(string $name): int
    {
        $this->execute('INSERT INTO specializations (name) VALUES (?)', 's', [$name]);
        return $this->db->lastInsertId();
    }

    /** يحذف تخصصاً بواسطة ID  */
    public function delete(int $id): bool
    {
        $result = $this->execute('DELETE FROM specializations WHERE id = ?', 'i', [$id]);
        return $result === true;
    }

    /**
     * Returns true if no doctors use this specialization — safe to delete.
     */
    public function isSafeToDelete(int $id): bool
    {
        $count = $this->fetchCount(
            'SELECT COUNT(*) FROM doctors WHERE specialization_id = ?',
            'i', [$id]
        );
        return $count === 0;
    }

    /** يتحقق إذا كان اسم التخصص موجوداً مسبقاً لمنع التكرار */
    public function nameExists(string $name): bool
    {
        return $this->fetchCount(
            'SELECT COUNT(*) FROM specializations WHERE name = ?',
            's', [$name]
        ) > 0;
    }
}
