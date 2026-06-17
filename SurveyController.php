<?php
class SurveyController extends Controller
{
    private SurveyModel $surveyModel;
    private TicketModel $ticketModel;

    public function __construct()
    {
        require_once __DIR__ . '/../models/SurveyModel.php';
        require_once __DIR__ . '/../models/TicketModel.php';
        $this->surveyModel = new SurveyModel();
        $this->ticketModel = new TicketModel();
    }

    /** Submit satisfaction survey */
    public function store(): void
    {
        $this->requireAuth();
        $user     = $this->currentUser();
        $ticketId = (int)($_POST['ticket_id'] ?? 0);
        $rating   = (int)($_POST['rating'] ?? 0);
        $comment  = trim($_POST['comment'] ?? '');

        if ($rating < 1 || $rating > 5) {
            $this->json(['success' => false, 'message' => 'Đánh giá phải từ 1–5 sao'], 422);
        }

        $ticket = $this->ticketModel->find($ticketId);
        if (!$ticket) { $this->json(['success' => false, 'message' => 'Ticket không tồn tại'], 404); }
        if ($ticket['submitter_id'] != $user['id']) {
            $this->json(['success' => false, 'message' => 'Không có quyền'], 403);
        }
        if (!in_array($ticket['status'], ['resolved','closed'])) {
            $this->json(['success' => false, 'message' => 'Chỉ có thể đánh giá ticket đã giải quyết'], 400);
        }
        if ($this->surveyModel->findByTicket($ticketId)) {
            $this->json(['success' => false, 'message' => 'Bạn đã đánh giá ticket này rồi'], 400);
        }

        $id = $this->surveyModel->create([
            'ticket_id'    => $ticketId,
            'submitted_by' => $user['id'],
            'rating'       => $rating,
            'comment'      => $comment,
        ]);
        $this->json(['success' => true, 'id' => $id]);
    }
}
