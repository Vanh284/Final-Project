<?php
class StatusLogModel extends Model
{
    protected string $table = 'ticket_status_logs';

    public function byTicket(int $ticketId): array
    {
        return $this->db->query(
            "SELECT sl.*, u.full_name FROM ticket_status_logs sl
             JOIN users u ON sl.changed_by = u.id
             WHERE sl.ticket_id = ?
             ORDER BY sl.changed_at ASC", [$ticketId]
        )->fetchAll();
    }

    public function log(int $ticketId, int $userId, ?string $oldStatus, string $newStatus, string $note = ''): void
    {
        $this->create([
            'ticket_id'  => $ticketId,
            'changed_by' => $userId,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'note'       => $note,
        ]);
    }
}
