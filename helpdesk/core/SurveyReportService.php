<?php
/**
 * Satisfaction Survey Report Service
 * ============================================================
 * Backend Focus #3: Tổng hợp báo cáo chất lượng dịch vụ
 * từ satisfaction_surveys.
 */
class SurveyReportService
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Overall summary stats.
     */
    public function overallStats(): array
    {
        $row = $this->db->query(
            "SELECT
                COUNT(*)                          AS total_surveys,
                ROUND(AVG(rating), 2)             AS avg_rating,
                SUM(rating = 5)                   AS five_star,
                SUM(rating = 4)                   AS four_star,
                SUM(rating = 3)                   AS three_star,
                SUM(rating = 2)                   AS two_star,
                SUM(rating = 1)                   AS one_star,
                SUM(rating >= 4)                  AS satisfied,
                SUM(rating <= 2)                  AS dissatisfied
             FROM satisfaction_surveys"
        )->fetch();

        // Satisfaction rate %
        $total = (int)($row['total_surveys'] ?? 0);
        $row['satisfaction_rate'] = $total > 0
            ? round(($row['satisfied'] / $total) * 100, 1)
            : 0;

        return $row;
    }

    /**
     * Average rating per department.
     */
    public function ratingByDepartment(): array
    {
        return $this->db->query(
            "SELECT
                d.name AS department_name,
                COUNT(ss.id)             AS total,
                ROUND(AVG(ss.rating), 2) AS avg_rating,
                SUM(ss.rating >= 4)      AS satisfied,
                SUM(ss.rating <= 2)      AS dissatisfied
             FROM satisfaction_surveys ss
             JOIN tickets t        ON ss.ticket_id = t.id
             JOIN ticket_categories tc ON t.category_id = tc.id
             JOIN departments d    ON tc.department_id = d.id
             GROUP BY d.id, d.name
             ORDER BY avg_rating DESC"
        )->fetchAll();
    }

    /**
     * Monthly trend: avg rating over last 6 months.
     */
    public function monthlyTrend(): array
    {
        return $this->db->query(
            "SELECT
                DATE_FORMAT(ss.submitted_at, '%Y-%m') AS month,
                COUNT(*)                              AS total,
                ROUND(AVG(ss.rating), 2)              AS avg_rating
             FROM satisfaction_surveys ss
             WHERE ss.submitted_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
             GROUP BY month
             ORDER BY month ASC"
        )->fetchAll();
    }

    /**
     * Recent low-rated surveys (rating <= 2) needing attention.
     */
    public function lowRatedSurveys(int $limit = 10): array
    {
        return $this->db->query(
            "SELECT ss.*, ss.rating,
                    t.ticket_code, t.title, t.status,
                    d.name AS department_name,
                    u.full_name AS submitter_name
             FROM satisfaction_surveys ss
             JOIN tickets t        ON ss.ticket_id = t.id
             JOIN ticket_categories tc ON t.category_id = tc.id
             JOIN departments d    ON tc.department_id = d.id
             JOIN users u          ON ss.submitted_by = u.id
             WHERE ss.rating <= 2
             ORDER BY ss.submitted_at DESC
             LIMIT ?", [$limit]
        )->fetchAll();
    }

    /**
     * Full survey list with pagination.
     */
    public function allSurveys(int $page = 1, int $perPage = 15): array
    {
        $total = (int)$this->db->query("SELECT COUNT(*) FROM satisfaction_surveys")->fetchColumn();
        $offset = ($page - 1) * $perPage;

        $data = $this->db->query(
            "SELECT ss.id, ss.rating, ss.comment, ss.submitted_at,
                    t.ticket_code, t.title,
                    d.name AS department_name,
                    u.full_name AS submitter_name
             FROM satisfaction_surveys ss
             JOIN tickets t        ON ss.ticket_id = t.id
             JOIN ticket_categories tc ON t.category_id = tc.id
             JOIN departments d    ON tc.department_id = d.id
             JOIN users u          ON ss.submitted_by = u.id
             ORDER BY ss.submitted_at DESC
             LIMIT ? OFFSET ?", [$perPage, $offset]
        )->fetchAll();

        return [
            'data'  => $data,
            'total' => $total,
            'pages' => (int)ceil($total / $perPage),
            'page'  => $page,
        ];
    }

    /**
     * Staff performance: avg rating for tickets they handled.
     */
    public function staffPerformance(): array
    {
        return $this->db->query(
            "SELECT
                u.full_name,
                d.name AS department_name,
                COUNT(ss.id)             AS total_surveys,
                ROUND(AVG(ss.rating), 2) AS avg_rating,
                SUM(ss.rating >= 4)      AS satisfied,
                SUM(ss.rating <= 2)      AS dissatisfied,
                ROUND(AVG(TIMESTAMPDIFF(MINUTE, t.created_at, t.resolved_at)), 0) AS avg_resolve_minutes
             FROM tickets t
             JOIN users u ON t.assigned_to = u.id
             JOIN ticket_categories tc ON t.category_id = tc.id
             JOIN departments d ON tc.department_id = d.id
             LEFT JOIN satisfaction_surveys ss ON ss.ticket_id = t.id
             WHERE t.status IN ('resolved','closed')
             GROUP BY u.id, u.full_name, d.name
             ORDER BY avg_rating DESC"
        )->fetchAll();
    }
}
