<?php
class EscalationModel extends Model
{
    protected string $table = 'escalation_logs';

    public function byTicket(int $ticketId): array
    {
        return $this->db->query(
            "SELECT el.*, u.full_name AS escalated_by_name
             FROM escalation_logs el
             LEFT JOIN users u ON el.escalated_by = u.id
             WHERE el.ticket_id = ?
             ORDER BY el.escalated_at ASC", [$ticketId]
        )->fetchAll();
    }
}
