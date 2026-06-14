<?php
require_once __DIR__ . '/../models/SatisfactionSurveyModel.php';

class SurveyController {
    private SatisfactionSurveyModel $model;

    public function __construct() {
        $this->model = new SatisfactionSurveyModel();
    }

    // GET /survey/form?ticket_id=X  → hiển thị form đánh giá
    public function showForm(): void {
        $this->requireLogin();
        $ticketId = (int)($_GET['ticket_id'] ?? 0);
        $existing = $this->model->getByTicket($ticketId);
        $error    = $_GET['error'] ?? '';
        require __DIR__ . '/../views/survey/form.php';
    }

    // POST /survey/submit  → xử lý submit form
    public function submit(): void {
        $this->requireLogin();

        $ticketId = (int)($_POST['ticket_id'] ?? 0);
        $rating   = (int)($_POST['rating']    ?? 0);
        $comment  = trim($_POST['comment']    ?? '');

        $result = $this->model->submit($ticketId, $_SESSION['user_id'], $rating, $comment);

        if ($result['success']) {
            $_SESSION['flash'] = $result['message'];
            $base = $_SESSION['base_url'] ?? '';
            header('Location: ' . $base . '/?page=ticket_detail&id=' . $ticketId . '&survey=done');
        } else {
            $_SESSION['flash_error'] = $result['message'];
            $base = $_SESSION['base_url'] ?? '';
            header('Location: ' . $base . '/?page=survey_form&ticket_id=' . $ticketId);
        }
        exit;
    }

    // GET /survey/report  → Admin: báo cáo tổng hợp
    public function report(): void {
        $this->requireAdmin();
        $data = $this->model->getAverageRatingByDepartment();
        require __DIR__ . '/../views/survey/report.php';
    }

    // GET /survey/all  → Admin: xem tất cả surveys
    public function listAll(): void {
        $this->requireAdmin();
        $surveys = $this->model->getAll();
        require __DIR__ . '/../views/survey/list.php';
    }

    // POST /survey/delete  → Admin only
    public function delete(): void {
        $this->requireAdmin();
        header('Content-Type: application/json');
        $id = (int)($_POST['id'] ?? 0);
        $ok = $this->model->delete($id);
        echo json_encode(['success' => $ok]);
    }

    private function requireLogin(): void {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /helpdesk/public/?page=login');
            exit;
        }
    }

    private function requireAdmin(): void {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
            http_response_code(403);
            echo '<p>Forbidden – Admin only.</p>';
            exit;
        }
    }
}
