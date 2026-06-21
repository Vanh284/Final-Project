<?php
/**
 * Ticket Routing Service
 * ============================================================
 * Backend Focus #1: Tự động phân loại ticket theo từ khoá
 * và chuyển đến đúng bộ phận + nhân viên phù hợp nhất.
 *
 * Scoring algorithm:
 *  - Mỗi keyword khớp trong title   = 3 điểm
 *  - Mỗi keyword khớp trong desc    = 1 điểm
 *  - Category có nhiều điểm nhất → winner
 *  - Nếu tie → ưu tiên category có SLA ngắn hơn (khẩn hơn)
 */
class TicketRoutingService
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Analyse text and return best-match category with score breakdown.
     *
     * @return array{
     *   category: array|null,
     *   scores: array,
     *   matched_keywords: array,
     *   confidence: string
     * }
     */
    public function route(string $title, string $description = ''): array
    {
        $categories = $this->db->query(
            "SELECT tc.*, d.name AS department_name, d.id AS department_id
             FROM ticket_categories tc
             JOIN departments d ON tc.department_id = d.id
             WHERE tc.is_active = 1"
        )->fetchAll();

        $titleLower = mb_strtolower($title);
        $descLower  = mb_strtolower($description);
        $scores     = [];
        $matched    = [];

        foreach ($categories as $cat) {
            $score   = 0;
            $catMatches = [];

            if (empty($cat['keywords'])) {
                $scores[$cat['id']] = ['cat' => $cat, 'score' => 0, 'keywords' => []];
                continue;
            }

            foreach (explode(',', $cat['keywords']) as $kw) {
                $kw = trim(mb_strtolower($kw));
                if (!$kw) continue;

                $inTitle = mb_substr_count($titleLower, $kw) > 0;
                $inDesc  = mb_substr_count($descLower,  $kw) > 0;

                if ($inTitle) { $score += 3; $catMatches[] = ['kw' => $kw, 'source' => 'title', 'pts' => 3]; }
                if ($inDesc)  { $score += 1; $catMatches[] = ['kw' => $kw, 'source' => 'desc',  'pts' => 1]; }
            }

            $scores[$cat['id']] = ['cat' => $cat, 'score' => $score, 'keywords' => $catMatches];
            if (!empty($catMatches)) $matched[$cat['id']] = $catMatches;
        }

        // Sort: score DESC, then sla_hours ASC (shorter SLA = higher urgency)
        uasort($scores, function ($a, $b) {
            if ($b['score'] !== $a['score']) return $b['score'] - $a['score'];
            return $a['cat']['sla_hours'] - $b['cat']['sla_hours'];
        });

        $best = reset($scores);
        $bestCat  = ($best && $best['score'] > 0) ? $best['cat'] : null;
        $topScore = $best ? $best['score'] : 0;

        // Confidence level
        $confidence = 'none';
        if ($topScore >= 6)     $confidence = 'high';
        elseif ($topScore >= 3) $confidence = 'medium';
        elseif ($topScore >= 1) $confidence = 'low';

        // Find best staff for the winning department
        $suggestedStaff = null;
        if ($bestCat) {
            $suggestedStaff = $this->bestStaffForDepartment((int)$bestCat['department_id']);
        }

        return [
            'category'         => $bestCat,
            'suggested_staff'  => $suggestedStaff,
            'scores'           => array_values($scores),
            'matched_keywords' => $matched,
            'confidence'       => $confidence,
            'top_score'        => $topScore,
        ];
    }

    /**
     * Find staff with fewest open tickets in a department (load balancing).
     */
    public function bestStaffForDepartment(int $deptId): ?array
    {
        return $this->db->query(
            "SELECT u.id, u.full_name, u.email,
                    COUNT(t.id) AS open_tickets
             FROM users u
             LEFT JOIN tickets t ON t.assigned_to = u.id
                 AND t.status NOT IN ('resolved','closed','cancelled')
             WHERE u.department_id = ?
               AND u.role IN ('staff','admin')
               AND u.is_active = 1
             GROUP BY u.id, u.full_name, u.email
             ORDER BY open_tickets ASC
             LIMIT 1", [$deptId]
        )->fetch() ?: null;
    }

    /**
     * Get routing history: last N auto-routed tickets with match info.
     */
    public function routingHistory(int $limit = 20): array
    {
        return $this->db->query(
            "SELECT t.id, t.ticket_code, t.title, t.created_at, t.priority,
                    tc.name AS category_name, tc.keywords,
                    d.name  AS department_name,
                    u.full_name AS assignee_name
             FROM tickets t
             JOIN ticket_categories tc ON t.category_id = tc.id
             JOIN departments d ON tc.department_id = d.id
             LEFT JOIN users u ON t.assigned_to = u.id
             ORDER BY t.created_at DESC
             LIMIT ?", [$limit]
        )->fetchAll();
    }
}
