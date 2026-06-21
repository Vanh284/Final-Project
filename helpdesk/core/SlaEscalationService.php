<?php
/**
 * SLA & Escalation Service
 * ============================================================
 * Backend Focus #2:
 *  - Kiểm tra ticket nào quá hạn SLA
 *  - Tự động escalate lên admin/manager
 *  - Ghi escalation_logs + ticket_status_logs
 *  - Trả về report chi tiết
 */
class SlaEscalationService
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Run escalation check. Returns summary of what was escalated.
     * Call this via cron (?page=tickets&action=runEscalation) or from admin UI.
     */
    public function runEscalation(int $triggeredBy): array
    {
        $overdue = $this->getOverdueTickets();
        $escalated = [];
        $skipped   = [];

        $this->db->beginTransaction();
        try {
            foreach ($overdue as $ticket) {
                // Find admin/manager to escalate to
                $escalateTo = $this->findEscalationTarget((int)$ticket['department_id']);

                // Mark ticket as escalated
                $this->db->query(
                    "UPDATE tickets SET escalated = 1, updated_at = NOW() WHERE id = ?",
                    [$ticket['id']]
                );

                // Log escalation
                $this->db->query(
                    "INSERT INTO escalation_logs (ticket_id, escalated_by, escalated_to, reason, escalated_at)
                     VALUES (?, ?, ?, ?, NOW())",
                    [
                        $ticket['id'],
                        $triggeredBy ?: null,
                        $escalateTo ? $escalateTo['id'] : null,
                        sprintf(
                            'Tự động escalate: ticket quá hạn SLA %d giờ (hạn: %s, trạng thái: %s)',
                            $ticket['sla_hours'],
                            $ticket['due_at'],
                            $ticket['status']
                        ),
                    ]
                );

                // Status log note (status không đổi, chỉ ghi nhận)
                $this->db->query(
                    "INSERT INTO ticket_status_logs (ticket_id, changed_by, old_status, new_status, note, changed_at)
                     VALUES (?, ?, ?, ?, ?, NOW())",
                    [
                        $ticket['id'],
                        $triggeredBy ?: 1,
                        $ticket['status'],
                        $ticket['status'],
                        'Hệ thống tự động escalate do quá hạn SLA' .
                            ($escalateTo ? " → chuyển lên {$escalateTo['full_name']}" : ''),
                    ]
                );

                $escalated[] = array_merge($ticket, ['escalated_to' => $escalateTo]);
            }
            $this->db->commit();
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }

        return [
            'escalated_count' => count($escalated),
            'escalated'       => $escalated,
            'checked_at'      => date('Y-m-d H:i:s'),
        ];
    }

    /**
     * Get all tickets that are overdue and not yet escalated.
     */
    public function getOverdueTickets(): array
    {
        return $this->db->query(
            "SELECT t.*,
                    tc.name AS category_name, tc.sla_hours,
                    d.name  AS department_name, d.id AS department_id,
                    sub.full_name AS submitter_name, sub.email AS submitter_email,
                    asn.full_name AS assignee_name,
                    TIMESTAMPDIFF(MINUTE, t.due_at, NOW()) AS overdue_minutes
             FROM tickets t
             JOIN ticket_categories tc ON t.category_id = tc.id
             JOIN departments d        ON tc.department_id = d.id
             JOIN users sub            ON t.submitter_id = sub.id
             LEFT JOIN users asn       ON t.assigned_to = asn.id
             WHERE t.due_at IS NOT NULL
               AND t.due_at < NOW()
               AND t.status NOT IN ('resolved','closed','cancelled')
               AND t.escalated = 0
             ORDER BY t.due_at ASC"
        )->fetchAll();
    }

    /**
     * Get tickets already escalated (with escalation detail).
     */
    public function getEscalatedTickets(): array
    {
        return $this->db->query(
            "SELECT t.id, t.ticket_code, t.title, t.status, t.priority,
                    t.due_at, t.created_at,
                    tc.name AS category_name, tc.sla_hours,
                    d.name  AS department_name,
                    sub.full_name AS submitter_name,
                    asn.full_name AS assignee_name,
                    el.reason, el.escalated_at,
                    ea.full_name AS escalated_to_name
             FROM tickets t
             JOIN ticket_categories tc ON t.category_id = tc.id
             JOIN departments d        ON tc.department_id = d.id
             JOIN users sub            ON t.submitter_id = sub.id
             LEFT JOIN users asn       ON t.assigned_to = asn.id
             LEFT JOIN escalation_logs el ON el.ticket_id = t.id
             LEFT JOIN users ea            ON ea.id = el.escalated_to
             WHERE t.escalated = 1
             ORDER BY el.escalated_at DESC"
        )->fetchAll();
    }

    /**
     * SLA compliance stats per department.
     */
    public function slaStats(): array
    {
        return $this->db->query(
            "SELECT
                d.name AS department_name,
                COUNT(t.id)                                                        AS total,
                SUM(t.status IN ('resolved','closed'))                             AS resolved,
                SUM(t.status IN ('resolved','closed')
                    AND (t.resolved_at IS NULL OR t.resolved_at <= t.due_at))      AS resolved_on_time,
                SUM(t.escalated = 1)                                               AS escalated,
                SUM(t.due_at IS NOT NULL
                    AND t.due_at < NOW()
                    AND t.status NOT IN ('resolved','closed','cancelled'))          AS currently_overdue,
                ROUND(AVG(
                    CASE WHEN t.resolved_at IS NOT NULL
                    THEN TIMESTAMPDIFF(MINUTE, t.created_at, t.resolved_at)
                    END
                ), 0) AS avg_resolve_minutes
             FROM tickets t
             JOIN ticket_categories tc ON t.category_id = tc.id
             JOIN departments d ON tc.department_id = d.id
             GROUP BY d.id, d.name
             ORDER BY d.name"
        )->fetchAll();
    }

    /**
     * Find admin or manager of a department to escalate to.
     */
    private function findEscalationTarget(int $deptId): ?array
    {
        // First: department manager
        $manager = $this->db->query(
            "SELECT u.id, u.full_name, u.email
             FROM departments d
             JOIN users u ON d.manager_id = u.id
             WHERE d.id = ? AND u.is_active = 1 LIMIT 1", [$deptId]
        )->fetch();
        if ($manager) return $manager;

        // Fallback: any admin
        return $this->db->query(
            "SELECT id, full_name, email FROM users WHERE role = 'admin' AND is_active = 1 LIMIT 1"
        )->fetch() ?: null;
    }
}
