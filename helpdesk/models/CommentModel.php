<?php
class CommentModel extends Model
{
    protected string $table = 'ticket_comments';

    public function byTicket(int $ticketId, bool $includeInternal = false): array
    {
        $sql = "SELECT c.*, u.full_name, u.role
                FROM ticket_comments c
                JOIN users u ON c.user_id = u.id
                WHERE c.ticket_id = ?";
        $params = [$ticketId];
        if (!$includeInternal) {
            $sql .= " AND c.is_internal = 0";
        }
        $sql .= " ORDER BY c.created_at ASC";
        return $this->db->query($sql, $params)->fetchAll();
    }
}
