<?php
require_once __DIR__ . '/../models/EscalationLogModel.php';

class EscalationController {
    private EscalationLogModel $model;

    public function __construct() {
        $this->model = new EscalationLogModel();
    }

    // GET /escalation/pending  → Admin dashboard
    public function pendingList(): void {
        $this->requireStaffOrAdmin();
        $escalations = $this->model->getPendingEscalations();
        require __DIR__ . '/../views/escalation/list.php';
    }

    // GET /escalation/ticket?ticket_id=X  → lịch sử escalation của 1 ticket
    public function byTicket(): void {
        $ticketId    = (int)($_GET['ticket_id'] ?? 0);
        $escalations = $this->model->getByTicket($ticketId);
        require __DIR__ . '/../views/escalation/detail.php';
    }

    // POST /escalation/manual  → staff escalate thủ công
    public function manualEscalate(): void {
        $this->requireStaffOrAdmin();
        header('Content-Type: application/json');

        $ticketId   = (int)($_POST['ticket_id']   ?? 0);
        $escalateTo = (int)($_POST['escalate_to'] ?? 0);
        $reason     = trim($_POST['reason']        ?? '');

        if (!$ticketId || !$escalateTo) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Thiếu thông tin bắt buộc.']);
            return;
        }

        $result = $this->model->manualEscalate(
            $ticketId,
            $_SESSION['user_id'],
            $escalateTo,
            $reason
        );
        echo json_encode($result);
    }

    // POST /escalation/resolve  → đánh dấu đã xử lý
    public function resolve(): void {
        $this->requireStaffOrAdmin();
        header('Content-Type: application/json');
        $id = (int)($_POST['id'] ?? 0);
        $ok = $this->model->markResolved($id);
        echo json_encode(['success' => $ok]);
    }

    // POST /escalation/auto  → gọi bởi cron job
    public function autoEscalate(): void {
        // Chỉ cho phép gọi từ CLI hoặc có secret key
        $secret = $_GET['secret'] ?? '';
        if ($secret !== 'CRON_SECRET_KEY_2026') {
            http_response_code(403); exit;
        }
        $count = $this->model->autoEscalateOverdue();
        echo json_encode(['escalated' => $count]);
    }

    // POST /escalation/delete  → Admin only
    public function delete(): void {
        $this->requireAdmin();
        header('Content-Type: application/json');
        $id = (int)($_POST['id'] ?? 0);
        $ok = $this->model->delete($id);
        echo json_encode(['success' => $ok]);
    }

    private function requireStaffOrAdmin(): void {
        if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'staff'])) {
            http_response_code(403);
            echo json_encode(['error' => 'Forbidden']);
            exit;
        }
    }

    private function requireAdmin(): void {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
            http_response_code(403);
            echo json_encode(['error' => 'Forbidden']);
            exit;
        }
    }
}
