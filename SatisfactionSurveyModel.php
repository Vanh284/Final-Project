<?php
require_once __DIR__ . '/../config/database.php';

class SatisfactionSurveyModel {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    // CREATE – gửi khảo sát sau khi ticket đóng
    public function submit(int $ticketId, int $userId, int $rating, string $comment): array {
        // Validate rating
        if ($rating < 1 || $rating > 5) {
            return ['success' => false, 'message' => 'Rating phải từ 1 đến 5.'];
        }

        // Business rule: chỉ cho phép survey khi ticket đã closed
        $check = $this->db->prepare("SELECT status FROM tickets WHERE id = :tid");
        $check->execute([':tid' => $ticketId]);
        $status = $check->fetchColumn();

        if ($status !== 'closed') {
            return ['success' => false, 'message' => 'Chỉ có thể đánh giá khi ticket đã đóng.'];
        }

        // Business rule: mỗi ticket chỉ được submit 1 lần
        $dup = $this->db->prepare("SELECT id FROM satisfaction_surveys WHERE ticket_id = :tid");
        $dup->execute([':tid' => $ticketId]);
        if ($dup->fetchColumn()) {
            return ['success' => false, 'message' => 'Bạn đã gửi đánh giá cho ticket này rồi.'];
        }

        $stmt = $this->db->prepare("
            INSERT INTO satisfaction_surveys (ticket_id, user_id, rating, comment)
            VALUES (:tid, :uid, :rating, :comment)
        ");
        $stmt->execute([
            ':tid'     => $ticketId,
            ':uid'     => $userId,
            ':rating'  => $rating,
            ':comment' => htmlspecialchars($comment),
        ]);

        return ['success' => true, 'message' => 'Cảm ơn bạn đã đánh giá!'];
    }

    // READ – lấy survey của 1 ticket
    public function getByTicket(int $ticketId): ?array {
        $stmt = $this->db->prepare("
            SELECT ss.*, u.full_name
            FROM satisfaction_surveys ss
            JOIN users u ON u.id = ss.user_id
            WHERE ss.ticket_id = :tid
        ");
        $stmt->execute([':tid' => $ticketId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    // READ – lấy tất cả surveys (Admin)
    public function getAll(): array {
        $stmt = $this->db->query("
            SELECT ss.*, u.full_name, t.title AS ticket_title
            FROM satisfaction_surveys ss
            JOIN users u   ON u.id  = ss.user_id
            JOIN tickets t ON t.id  = ss.ticket_id
            ORDER BY ss.submitted_at DESC
        ");
        return $stmt->fetchAll();
    }

    // READ – báo cáo điểm trung bình theo bộ phận
    public function getAverageRatingByDepartment(): array {
        $stmt = $this->db->query("
            SELECT d.name AS department,
                   ROUND(AVG(ss.rating), 2) AS avg_rating,
                   COUNT(ss.id) AS total_surveys
            FROM satisfaction_surveys ss
            JOIN tickets t          ON t.id  = ss.ticket_id
            JOIN ticket_categories tc ON tc.id = t.category_id
            JOIN departments d      ON d.id  = tc.department_id
            GROUP BY d.id, d.name
            ORDER BY avg_rating DESC
        ");
        return $stmt->fetchAll();
    }

    // UPDATE – admin sửa comment (nếu cần)
    public function updateComment(int $id, string $comment): bool {
        $stmt = $this->db->prepare("UPDATE satisfaction_surveys SET comment = :c WHERE id = :id");
        return $stmt->execute([':c' => htmlspecialchars($comment), ':id' => $id]);
    }

    // DELETE – xóa survey (Admin only)
    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM satisfaction_surveys WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }
}
