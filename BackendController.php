<?php
/**
 * BackendController
 * ============================================================
 * Handles the 3 Backend Focus features of Topic 13:
 *  1. Ticket Routing Engine  (?page=backend&action=routing)
 *  2. SLA & Escalation       (?page=backend&action=sla)
 *  3. Survey Report          (?page=backend&action=survey)
 */
class BackendController extends Controller
{
    public function __construct()
    {
        require_once __DIR__ . '/../core/TicketRoutingService.php';
        require_once __DIR__ . '/../core/SlaEscalationService.php';
        require_once __DIR__ . '/../core/SurveyReportService.php';
    }

    // ----------------------------------------------------------------
    // #1 TICKET ROUTING
    // ----------------------------------------------------------------

    /** Routing dashboard: show UI + history */
    public function routing(): void
    {
        $this->requireRole('admin', 'staff');
        $svc     = new TicketRoutingService();
        $history = $svc->routingHistory(20);

        $this->view('backend/routing', [
            'pageTitle' => 'Ticket Routing Engine',
            'history'   => $history,
        ]);
    }

    /** AJAX: analyse a title+description and return routing result */
    public function analyseRoute(): void
    {
        $this->requireAuth();
        $title = trim($_POST['title'] ?? '');
        $desc  = trim($_POST['description'] ?? '');

        if (strlen($title) < 3) {
            $this->json(['success' => false, 'message' => 'Tiêu đề quá ngắn'], 422);
        }

        $svc    = new TicketRoutingService();
        $result = $svc->route($title, $desc);

        $this->json(['success' => true, 'result' => $result]);
    }

    // ----------------------------------------------------------------
    // #2 SLA & ESCALATION
    // ----------------------------------------------------------------

    /** SLA dashboard */
    public function sla(): void
    {
        $this->requireRole('admin', 'staff');
        $svc        = new SlaEscalationService();
        $overdue    = $svc->getOverdueTickets();
        $escalated  = $svc->getEscalatedTickets();
        $slaStats   = $svc->slaStats();

        $this->view('backend/sla', [
            'pageTitle' => 'SLA & Escalation',
            'overdue'   => $overdue,
            'escalated' => $escalated,
            'slaStats'  => $slaStats,
        ]);
    }

    /** AJAX: run escalation for all overdue tickets */
    public function runEscalation(): void
    {
        $this->requireRole('admin');
        $svc    = new SlaEscalationService();
        $result = $svc->runEscalation((int)$_SESSION['user']['id']);
        $this->json(['success' => true, 'data' => $result]);
    }

    /** AJAX: get current overdue list (for live refresh) */
    public function overdueList(): void
    {
        $this->requireRole('admin', 'staff');
        $svc     = new SlaEscalationService();
        $overdue = $svc->getOverdueTickets();
        $this->json(['success' => true, 'overdue' => $overdue, 'count' => count($overdue)]);
    }

    // ----------------------------------------------------------------
    // #3 SATISFACTION SURVEY REPORT
    // ----------------------------------------------------------------

    /** Survey report dashboard */
    public function survey(): void
    {
        $this->requireRole('admin', 'staff');
        $svc     = new SurveyReportService();
        $page    = max(1, (int)($_GET['p'] ?? 1));

        $overall   = $svc->overallStats();
        $byDept    = $svc->ratingByDepartment();
        $trend     = $svc->monthlyTrend();
        $lowRated  = $svc->lowRatedSurveys(5);
        $staffPerf = $svc->staffPerformance();
        $allSurveys= $svc->allSurveys($page, 10);

        $this->view('backend/survey', [
            'pageTitle'   => 'Báo cáo Satisfaction Survey',
            'overall'     => $overall,
            'byDept'      => $byDept,
            'trend'       => $trend,
            'lowRated'    => $lowRated,
            'staffPerf'   => $staffPerf,
            'surveys'     => $allSurveys['data'],
            'pages'       => $allSurveys['pages'],
            'page'        => $allSurveys['page'],
            'totalSurveys'=> $allSurveys['total'],
        ]);
    }
}
