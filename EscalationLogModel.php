<?php
require_once __DIR__ . '/../config/database.php';

class EscalationLogModel {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    // CREATE – escalate thủ công bởi staff
    public function manualEscalate(int $ticketId, int $escalatedBy, int $escalatedTo, string $reason): array {
        // Lấy level escalation hiện tại
        $stmt = $this->db->prepare("SELECT COALESCE(MAX(level), 0) FROM escalation_logs WHERE ticket_id = :tid");
        $stmt->execute([':tid' => $ticketId]);
        $currentLevel = (int)$stmt->fetchColumn();

        // Business rule: tối đa 3 lần escalate
        if ($currentLevel >= 3) {
            return ['success' => false, 'message' => 'Ticket đã được escalate tối đa 3 lần.'];
        }

        if (empty(trim($reason))) {
            return ['success' => false, 'message' => 'Lý do escalate không được để trống.'];
        }

        $ins = $this->db->prepare("
            INSERT INTO escalation_logs (ticket_id, escalated_by, escalated_to, reason, level)
            VALUES (:tid, :by, :to, :reason, :level)
        ");
        $ins->execute([
            ':tid'    => $ticketId,
            ':by'     => $escalatedBy,
            ':to'     => $escalatedTo,
            ':reason' => htmlspecialchars($reason),
            ':level'  => $currentLevel + 1,
        ]);

        return ['success' => true, 'message' => 'Escalate thành công (Level ' . ($currentLevel + 1) . ')'];
    }

    // CREATE – tự động escalate các ticket quá SLA (gọi bởi cron job)
    public function autoEscalateOverdue(): int {
        $stmt = $this->db->query("
            SELECT t.id AS ticket_id,
                   tc.sla_hours,
                   tc.department_id,
                   t.created_at,
                   COALESCE(MAX(el.level), 0) AS current_level
            FROM tickets t
            JOIN ticket_categories tc ON tc.id = t.category_id
            LEFT JOIN escalation_logs el ON el.ticket_id = t.id
            WHERE t.status NOT IN ('resolved', 'closed')
            GROUP BY t.id, tc.sla_hours, tc.department_id, t.created_at
            HAVING TIMESTAMPDIFF(HOUR, t.created_at, NOW()) > tc.sla_hours
               AND current_level < 3
        ");
        $overdue = $stmt->fetchAll();

        $count = 0;
        foreach ($overdue as $row) {
            $newLevel = $row['current_level'] + 1;

            // Lấy manager của department để escalate tới
            $mgr = $this->db->prepare("SELECT manager_id FROM departments WHERE id = :did");
            $mgr->execute([':did' => $row['department_id']]);
            $managerId = $mgr->fetchColumn();

            if ($managerId) {
                $ins = $this->db->prepare("
                    INSERT INTO escalation_logs (ticket_id, escalated_by, escalated_to, reason, level)
                    VALUES (:tid, 1, :to, :reason, :level)
                ");
                $ins->execute([
                    ':tid'    => $row['ticket_id'],
                    ':to'     => $managerId,
                    ':reason' => 'Auto-escalated: SLA exceeded (' . $row['sla_hours'] . 'h)',
                    ':level'  => $newLevel,
                ]);
                $count++;
            }
        }
        return $count;
    }

    // READ – lấy danh sách escalation của 1 ticket
    public function getByTicket(int $ticketId): array {
        $stmt = $this->db->prepare("
            SELECT el.*,
                   u1.full_name AS escalated_by_name,
                   u2.full_name AS escalated_to_name
            FROM escalation_logs el
            JOIN users u1 ON u1.id = el.escalated_by
            JOIN users u2 ON u2.id = el.escalated_to
            WHERE el.ticket_id = :tid
            ORDER BY el.escalated_at ASC
        ");
        $stmt->execute([':tid' => $ticketId]);
        return $stmt->fetchAll();
    }

    // READ – lấy tất cả escalation chưa resolved (cho admin dashboard)
    public function getPendingEscalations(): array {
        $stmt = $this->db->query("
            SELECT el.*, t.title AS ticket_title,
                   u1.full_name AS escalated_by_name,
                   u2.full_name AS escalated_to_name
            FROM escalation_logs el
            JOIN tickets t  ON t.id  = el.ticket_id
            JOIN users u1   ON u1.id = el.escalated_by
            JOIN users u2   ON u2.id = el.escalated_to
            WHERE el.resolved_at IS NULL
            ORDER BY el.escalated_at ASC
        ");
        return $stmt->fetchAll();
    }

    // UPDATE – đánh dấu escalation đã được giải quyết
    public function markResolved(int $escalationId): bool {
        $stmt = $this->db->prepare("
            UPDATE escalation_logs
            SET resolved_at = NOW()
            WHERE id = :id AND resolved_at IS NULL
        ");
        return $stmt->execute([':id' => $escalationId]);
    }

    // DELETE – xóa escalation log (Admin only)
    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM escalation_logs WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }
}
