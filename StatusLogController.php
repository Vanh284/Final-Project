<?php
require_once __DIR__ . '/../models/TicketStatusLogModel.php';

class StatusLogController {
    private TicketStatusLogModel $model;

    public function __construct() {
        $this->model = new TicketStatusLogModel();
    }

    // GET /status-log/history?ticket_id=X  → trả JSON (AJAX)
    public function history(): void {
        $ticketId = (int)($_GET['ticket_id'] ?? 0);
        if (!$ticketId) {
            http_response_code(400);
            echo json_encode(['error' => 'ticket_id is required']);
            return;
        }
        header('Content-Type: application/json');
        echo json_encode($this->model->getHistoryByTicket($ticketId));
    }

    // POST /status-log/update-note  (Admin)
    public function updateNote(): void {
        $this->requireAdmin();
        $id   = (int)($_POST['id']   ?? 0);
        $note = trim($_POST['note']  ?? '');
        $ok   = $this->model->updateNote($id, $note);
        header('Content-Type: application/json');
        echo json_encode(['success' => $ok]);
    }

    // POST /status-log/delete  (Admin)
    public function delete(): void {
        $this->requireAdmin();
        $id = (int)($_POST['id'] ?? 0);
        $ok = $this->model->delete($id);
        header('Content-Type: application/json');
        echo json_encode(['success' => $ok]);
    }

    // Hiển thị trang lịch sử trạng thái (view)
    public function showHistory(): void {
        $ticketId = (int)($_GET['ticket_id'] ?? 0);
        $logs     = $this->model->getHistoryByTicket($ticketId);
        require __DIR__ . '/../views/status_logs/history.php';
    }

    private function requireAdmin(): void {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
            http_response_code(403);
            echo json_encode(['error' => 'Forbidden']);
            exit;
        }
    }
}
