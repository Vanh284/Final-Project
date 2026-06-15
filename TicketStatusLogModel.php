<?php
require_once __DIR__ . '/../config/database.php';

class TicketStatusLogModel {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    // CREATE – ghi log khi ticket đổi trạng thái
    public function logStatusChange(int $ticketId, string $oldStatus, string $newStatus, int $changedBy, string $note = ''): bool {
        // Business rule: không log nếu trạng thái không thay đổi
        if ($oldStatus === $newStatus) {
            return false;
        }

        $stmt = $this->db->prepare("
            INSERT INTO ticket_status_logs (ticket_id, old_status, new_status, changed_by, note)
            VALUES (:tid, :old, :new, :by, :note)
        ");
        return $stmt->execute([
            ':tid'  => $ticketId,
            ':old'  => $oldStatus,
            ':new'  => $newStatus,
            ':by'   => $changedBy,
            ':note' => htmlspecialchars($note),
        ]);
    }

    // READ – lấy toàn bộ lịch sử trạng thái của 1 ticket
    public function getHistoryByTicket(int $ticketId): array {
        $stmt = $this->db->prepare("
            SELECT tsl.*, u.full_name AS changed_by_name
            FROM ticket_status_logs tsl
            JOIN users u ON u.id = tsl.changed_by
            WHERE tsl.ticket_id = :tid
            ORDER BY tsl.changed_at ASC
        ");
        $stmt->execute([':tid' => $ticketId]);
        return $stmt->fetchAll();
    }

    // READ – lấy 1 log theo id
    public function getById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM ticket_status_logs WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    // UPDATE – cập nhật ghi chú của 1 log entry
    public function updateNote(int $id, string $note): bool {
        $stmt = $this->db->prepare("
            UPDATE ticket_status_logs SET note = :note WHERE id = :id
        ");
        return $stmt->execute([':note' => htmlspecialchars($note), ':id' => $id]);
    }

    // DELETE – xóa log (Admin only)
    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM ticket_status_logs WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    // READ – đếm số lần đổi trạng thái của 1 ticket
    public function countByTicket(int $ticketId): int {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM ticket_status_logs WHERE ticket_id = :tid");
        $stmt->execute([':tid' => $ticketId]);
        return (int)$stmt->fetchColumn();
    }
}
