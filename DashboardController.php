<?php
class DashboardController extends Controller
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
        $this->requireAuth();
        $user  = $this->currentUser();
        $stats = $this->ticketModel->stats();
        $byDept = $this->ticketModel->statsByDepartment();
        $avgRating = $this->surveyModel->averageRating();
        $ratingDist = $this->surveyModel->ratingDistribution();

        // Recent tickets
        $filters = [];
        if ($user['role'] === 'user') {
            $filters['submitter_id'] = $user['id'];
        } elseif ($user['role'] === 'staff') {
            $filters['assigned_to'] = $user['id'];
        }
        $recent = $this->ticketModel->listTickets($filters, 1, 5);

        $this->view('dashboard/index', [
            'pageTitle'  => 'Dashboard',
            'stats'      => $stats,
            'byDept'     => $byDept,
            'avgRating'  => $avgRating,
            'ratingDist' => $ratingDist,
            'recent'     => $recent['data'],
        ]);
    }
}
