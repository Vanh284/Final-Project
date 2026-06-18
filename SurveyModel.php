<?php
class SurveyModel extends Model
{
    protected string $table = 'satisfaction_surveys';

    public function findByTicket(int $ticketId): ?array
    {
        $row = $this->db->query(
            "SELECT s.*, u.full_name FROM satisfaction_surveys s
             JOIN users u ON s.submitted_by = u.id
             WHERE s.ticket_id = ? LIMIT 1", [$ticketId]
        )->fetch();
        return $row ?: null;
    }

    public function averageRating(): float
    {
        $avg = $this->db->query("SELECT AVG(rating) FROM satisfaction_surveys")->fetchColumn();
        return round((float)$avg, 2);
    }

    public function ratingDistribution(): array
    {
        return $this->db->query(
            "SELECT rating, COUNT(*) AS count FROM satisfaction_surveys GROUP BY rating ORDER BY rating"
        )->fetchAll();
    }
}
