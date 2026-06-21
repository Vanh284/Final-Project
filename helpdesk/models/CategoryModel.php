<?php
class CategoryModel extends Model
{
    protected string $table = 'ticket_categories';

    public function allWithDepartment(): array
    {
        return $this->db->query(
            "SELECT tc.*, d.name AS department_name
             FROM ticket_categories tc
             JOIN departments d ON tc.department_id = d.id
             ORDER BY d.name, tc.name"
        )->fetchAll();
    }

    public function findWithDepartment(int $id): ?array
    {
        $row = $this->db->query(
            "SELECT tc.*, d.name AS department_name
             FROM ticket_categories tc
             JOIN departments d ON tc.department_id = d.id
             WHERE tc.id = ? LIMIT 1", [$id]
        )->fetch();
        return $row ?: null;
    }

    /** Auto-route: find category by matching keywords in a text */
    public function autoRoute(string $text): ?array
    {
        $categories = $this->allWithDepartment();
        $text = mb_strtolower($text);
        foreach ($categories as $cat) {
            if (!empty($cat['keywords'])) {
                foreach (explode(',', $cat['keywords']) as $kw) {
                    if (str_contains($text, trim(mb_strtolower($kw)))) {
                        return $cat;
                    }
                }
            }
        }
        return null;
    }
}
