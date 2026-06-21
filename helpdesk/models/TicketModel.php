<?php
class TicketModel extends Model
{
    protected string $table = 'tickets';

    /** Full ticket with related names */
    public function findFull(int $id): ?array
    {
        $row = $this->db->query(
            "SELECT t.*,
                    tc.name   AS category_name,
                    tc.sla_hours,
                    d.name    AS department_name,
                    d.id      AS department_id,
                    sub.full_name AS submitter_name,
                    sub.email     AS submitter_email,
                    asn.full_name AS assignee_name
             FROM tickets t
             JOIN ticket_categories tc ON t.category_id = tc.id
             JOIN departments d        ON tc.department_id = d.id
             JOIN users sub            ON t.submitter_id = sub.id
             LEFT JOIN users asn       ON t.assigned_to = asn.id
             WHERE t.id = ? LIMIT 1", [$id]
        )->fetch();
        return $row ?: null;
    }

    /** List tickets with filters */
    public function listTickets(array $filters = [], int $page = 1, int $perPage = 10): array
    {
        $where  = [];
        $params = [];

        if (!empty($filters['status']))      { $where[] = 't.status = ?';         $params[] = $filters['status']; }
        if (!empty($filters['priority']))    { $where[] = 't.priority = ?';        $params[] = $filters['priority']; }
        if (!empty($filters['category_id'])){ $where[] = 't.category_id = ?';     $params[] = $filters['category_id']; }
        if (!empty($filters['submitter_id'])){ $where[] = 't.submitter_id = ?';   $params[] = $filters['submitter_id']; }
        if (!empty($filters['assigned_to'])){ $where[] = 't.assigned_to = ?';     $params[] = $filters['assigned_to']; }
        if (!empty($filters['department_id'])){ $where[] = 'd.id = ?';            $params[] = $filters['department_id']; }
        if (!empty($filters['search'])) {
            $where[]  = '(t.title LIKE ? OR t.ticket_code LIKE ?)';
            $s = '%' . $filters['search'] . '%';
            $params[] = $s; $params[] = $s;
        }

        $whereStr = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $total = (int) $this->db->query(
            "SELECT COUNT(*) FROM tickets t
             JOIN ticket_categories tc ON t.category_id = tc.id
             JOIN departments d ON tc.department_id = d.id {$whereStr}", $params
        )->fetchColumn();

        $offset = ($page - 1) * $perPage;
        $data   = $this->db->query(
            "SELECT t.*,
                    tc.name AS category_name,
                    d.name  AS department_name,
                    sub.full_name AS submitter_name,
                    asn.full_name AS assignee_name
             FROM tickets t
             JOIN ticket_categories tc ON t.category_id = tc.id
             JOIN departments d        ON tc.department_id = d.id
             JOIN users sub            ON t.submitter_id = sub.id
             LEFT JOIN users asn       ON t.assigned_to = asn.id
             {$whereStr}
             ORDER BY t.created_at DESC
             LIMIT {$perPage} OFFSET {$offset}", $params
        )->fetchAll();

        return ['data' => $data, 'total' => $total, 'pages' => ceil($total / $perPage), 'page' => $page];
    }

    /** Generate next ticket code */
    public function generateCode(): string
    {
        $year  = date('Y');
        $count = (int) $this->db->query(
            "SELECT COUNT(*) FROM tickets WHERE YEAR(created_at) = ?", [$year]
        )->fetchColumn();
        return 'TK-' . $year . '-' . str_pad($count + 1, 4, '0', STR_PAD_LEFT);
    }

    /** Dashboard stats */
    public function stats(): array
    {
        return $this->db->query(
            "SELECT
                COUNT(*) AS total,
                SUM(status = 'open')        AS open,
                SUM(status = 'in_progress') AS in_progress,
                SUM(status = 'resolved')    AS resolved,
                SUM(status = 'closed')      AS closed,
                SUM(status = 'pending')     AS pending,
                SUM(escalated = 1)          AS escalated,
                SUM(due_at IS NOT NULL AND due_at < NOW() AND status NOT IN ('resolved','closed','cancelled')) AS overdue
             FROM tickets"
        )->fetch();
    }

    /** Stats per department */
    public function statsByDepartment(): array
    {
        return $this->db->query(
            "SELECT d.name AS department_name,
                    COUNT(*) AS total,
                    CAST(SUM(t.status IN ('open','in_progress','pending')) AS UNSIGNED) AS open_count,
                    CAST(SUM(t.status IN ('resolved','closed')) AS UNSIGNED) AS resolved_count
             FROM tickets t
             JOIN ticket_categories tc ON t.category_id = tc.id
             JOIN departments d ON tc.department_id = d.id
             GROUP BY d.id, d.name ORDER BY total DESC"
        )->fetchAll();
    }

    /** Tickets overdue and not escalated yet */
    public function overdueNotEscalated(): array
    {
        return $this->db->query(
            "SELECT t.*, tc.name AS category_name, sub.full_name AS submitter_name
             FROM tickets t
             JOIN ticket_categories tc ON t.category_id = tc.id
             JOIN users sub ON t.submitter_id = sub.id
             WHERE t.due_at < NOW()
               AND t.status NOT IN ('resolved','closed','cancelled')
               AND t.escalated = 0"
        )->fetchAll();
    }
}
