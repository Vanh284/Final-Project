<?php
class ReportController extends Controller
{
    private TicketModel $ticketModel;
    private SurveyModel $surveyModel;

    public function __construct()
    {
        require_once __DIR__ . '/../models/TicketModel.php';
        require_once __DIR__ . '/../models/SurveyModel.php';
        $this->ticketModel = new TicketModel();
        $this->surveyModel = new SurveyModel();
    }

    public function index(): void
    {
        $this->requireRole('admin', 'staff');
        $stats      = $this->ticketModel->stats();
        $byDept     = $this->ticketModel->statsByDepartment();
        $avgRating  = $this->surveyModel->averageRating();
        $ratingDist = $this->surveyModel->ratingDistribution();

        // Tickets by month (last 6 months)
        $byMonth = Database::getInstance()->query(
            "SELECT DATE_FORMAT(created_at,'%Y-%m') AS month, COUNT(*) AS total
             FROM tickets
             WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
             GROUP BY month ORDER BY month"
        )->fetchAll();

        $this->view('reports/index', [
            'pageTitle'  => 'Báo cáo & Thống kê',
            'stats'      => $stats,
            'byDept'     => $byDept,
            'avgRating'  => $avgRating,
            'ratingDist' => $ratingDist,
            'byMonth'    => $byMonth,
        ]);
    }
}
