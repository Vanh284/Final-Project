<?php
class AssignmentModel extends Model
{
    protected string $table = 'ticket_assignments';

    public function byTicket(int $ticketId): array
    {
        return $this->db->query(
            "SELECT ta.*, u.full_name AS staff_name, ab.full_name AS assigned_by_name
             FROM ticket_assignments ta
             JOIN users u  ON ta.staff_id    = u.id
             JOIN users ab ON ta.assigned_by = ab.id
             WHERE ta.ticket_id = ?
             ORDER BY ta.assigned_at DESC", [$ticketId]
        )->fetchAll();
    }
}
