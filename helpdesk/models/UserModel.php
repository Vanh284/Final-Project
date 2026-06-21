<?php
class UserModel extends Model
{
    protected string $table = 'users';

    public function findByEmail(string $email): ?array
    {
        $row = $this->db->query("SELECT * FROM users WHERE email = ? LIMIT 1", [$email])->fetch();
        return $row ?: null;
    }

    public function allWithDepartment(): array
    {
        return $this->db->query(
            "SELECT u.*, d.name AS department_name
             FROM users u
             LEFT JOIN departments d ON u.department_id = d.id
             ORDER BY u.created_at ASC"
        )->fetchAll();
    }

    public function staffByDepartment(int $deptId): array
    {
        return $this->db->query(
            "SELECT * FROM users WHERE role IN ('staff','admin') AND department_id = ? AND is_active = 1",
            [$deptId]
        )->fetchAll();
    }

    public function allStaff(): array
    {
        return $this->db->query(
            "SELECT u.*, d.name AS department_name FROM users u
             LEFT JOIN departments d ON u.department_id = d.id
             WHERE u.role IN ('staff','admin') AND u.is_active = 1
             ORDER BY u.full_name"
        )->fetchAll();
    }
}
