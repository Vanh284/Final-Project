<?php
class DepartmentModel extends Model
{
    protected string $table = 'departments';

    public function allWithManager(): array
    {
        return $this->db->query(
            "SELECT d.*, u.full_name AS manager_name
             FROM departments d
             LEFT JOIN users u ON d.manager_id = u.id
             ORDER BY d.name"
        )->fetchAll();
    }
}
