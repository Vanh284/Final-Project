<?php
class TicketController extends Controller
{
    private TicketModel $ticketModel;
    private CategoryModel $categoryModel;
    private CommentModel $commentModel;
    private StatusLogModel $statusLogModel;
    private EscalationModel $escalationModel;
    private UserModel $userModel;

    public function __construct()
    {
        require_once __DIR__ . '/../models/TicketModel.php';
        require_once __DIR__ . '/../models/CategoryModel.php';
        require_once __DIR__ . '/../models/CommentModel.php';
        require_once __DIR__ . '/../models/StatusLogModel.php';
        require_once __DIR__ . '/../models/EscalationModel.php';
        require_once __DIR__ . '/../models/UserModel.php';
        $this->ticketModel    = new TicketModel();
        $this->categoryModel  = new CategoryModel();
        $this->commentModel   = new CommentModel();
        $this->statusLogModel = new StatusLogModel();
        $this->escalationModel= new EscalationModel();
        $this->userModel      = new UserModel();
    }

    /** LIST tickets */
    public function index(): void
    {
        $this->requireAuth();
        $user    = $this->currentUser();
        $page    = max(1, (int)($_GET['p'] ?? 1));
        $filters = [
            'status'      => $_GET['status'] ?? '',
            'priority'    => $_GET['priority'] ?? '',
            'category_id' => $_GET['category_id'] ?? '',
            'search'      => $_GET['search'] ?? '',
        ];
        // Non-admin users only see their own tickets or assigned tickets
        if ($user['role'] === 'user') {
            $filters['submitter_id'] = $user['id'];
        } elseif ($user['role'] === 'staff') {
            // staff sees tickets assigned to them or in their dept (no extra filter needed; all visible)
        }

        $result     = $this->ticketModel->listTickets($filters, $page, ITEMS_PER_PAGE);
        $categories = $this->categoryModel->allWithDepartment();

        $this->view('tickets/index', [
            'pageTitle'  => 'Danh sách Ticket',
            'tickets'    => $result['data'],
            'total'      => $result['total'],
            'pages'      => $result['pages'],
            'page'       => $page,
            'filters'    => $filters,
            'categories' => $categories,
        ]);
    }

    /** SHOW single ticket (AJAX or normal) */
    public function show(): void
    {
        $this->requireAuth();
        $id     = (int)($_GET['id'] ?? 0);
        $ticket = $this->ticketModel->findFull($id);
        if (!$ticket) { $this->json(['success' => false, 'message' => 'Không tìm thấy ticket'], 404); }

        $user = $this->currentUser();
        // Authorization
        if ($user['role'] === 'user' && $ticket['submitter_id'] != $user['id']) {
            $this->json(['success' => false, 'message' => 'Không có quyền'], 403);
        }

        $includeInternal = in_array($user['role'], ['admin','staff']);
        $comments    = $this->commentModel->byTicket($id, $includeInternal);
        $statusLogs  = $this->statusLogModel->byTicket($id);
        $escalations = $this->escalationModel->byTicket($id);
        $survey      = null;
        if (in_array($ticket['status'], ['resolved','closed'])) {
            require_once __DIR__ . '/../models/SurveyModel.php';
            $survey = (new SurveyModel())->findByTicket($id);
        }
        $staffList = $this->userModel->allStaff();

        if ($this->isAjax()) {
            $this->json([
                'success'    => true,
                'ticket'     => $ticket,
                'comments'   => $comments,
                'statusLogs' => $statusLogs,
                'escalations'=> $escalations,
                'survey'     => $survey,
                'staffList'  => $staffList,
            ]);
        }

        $this->view('tickets/show', [
            'pageTitle'   => 'Chi tiết Ticket #' . $ticket['ticket_code'],
            'ticket'      => $ticket,
            'comments'    => $comments,
            'statusLogs'  => $statusLogs,
            'escalations' => $escalations,
            'survey'      => $survey,
            'staffList'   => $staffList,
        ]);
    }

    /** CREATE form */
    public function create(): void
    {
        $this->requireAuth();
        $categories = $this->categoryModel->allWithDepartment();
        $this->view('tickets/create', [
            'pageTitle'  => 'Tạo Ticket mới',
            'categories' => $categories,
        ]);
    }

    /** STORE new ticket */
    public function store(): void
    {
        $this->requireAuth();
        $user = $this->currentUser();

        $title       = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $categoryId  = (int)($_POST['category_id'] ?? 0);
        $priority    = $_POST['priority'] ?? 'medium';
        $location    = trim($_POST['location'] ?? '');

        // Validation
        $errors = [];
        if (strlen($title) < 5)       $errors[] = 'Tiêu đề phải có ít nhất 5 ký tự.';
        if (strlen($description) < 10) $errors[] = 'Mô tả phải có ít nhất 10 ký tự.';
        if (!$categoryId)              $errors[] = 'Vui lòng chọn danh mục.';
        if (!in_array($priority, ['low','medium','high','critical'])) $priority = 'medium';

        if ($errors) {
            $this->json(['success' => false, 'errors' => $errors], 422);
        }

        $category = $this->categoryModel->find($categoryId);
        if (!$category) { $this->json(['success' => false, 'message' => 'Danh mục không hợp lệ'], 400); }

        // Auto-route: find best staff in the category's department
        $staff = $this->userModel->staffByDepartment($category['department_id']);
        $assignedTo = $staff ? $staff[0]['id'] : null;

        $dueAt = date('Y-m-d H:i:s', strtotime("+{$category['sla_hours']} hours"));

        // Handle file upload
        $attachment = null;
        if (!empty($_FILES['attachment']['name'])) {
            $ext = strtolower(pathinfo($_FILES['attachment']['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, UPLOAD_ALLOWED)) {
                $this->json(['success' => false, 'errors' => ['Định dạng file không được phép.']], 422);
            }
            if ($_FILES['attachment']['size'] > UPLOAD_MAX_SIZE) {
                $this->json(['success' => false, 'errors' => ['File quá lớn (tối đa 5MB).']], 422);
            }
            $filename = uniqid('att_', true) . '.' . $ext;
            if (!is_dir(UPLOAD_DIR)) mkdir(UPLOAD_DIR, 0755, true);
            move_uploaded_file($_FILES['attachment']['tmp_name'], UPLOAD_DIR . $filename);
            $attachment = $filename;
        }

        $code = $this->ticketModel->generateCode();
        $db   = Database::getInstance();
        $db->beginTransaction();
        try {
            $ticketId = $this->ticketModel->create([
                'ticket_code'  => $code,
                'title'        => $title,
                'description'  => $description,
                'category_id'  => $categoryId,
                'submitter_id' => $user['id'],
                'assigned_to'  => $assignedTo,
                'priority'     => $priority,
                'status'       => 'open',
                'location'     => $location,
                'attachment'   => $attachment,
                'due_at'       => $dueAt,
            ]);

            $this->statusLogModel->log($ticketId, $user['id'], null, 'open', 'Ticket được tạo');

            if ($assignedTo) {
                require_once __DIR__ . '/../models/AssignmentModel.php';
                (new AssignmentModel())->create([
                    'ticket_id'   => $ticketId,
                    'staff_id'    => $assignedTo,
                    'assigned_by' => $user['id'],
                    'note'        => 'Phân công tự động theo danh mục',
                ]);
            }

            $db->commit();
            $this->json(['success' => true, 'ticket_id' => $ticketId, 'ticket_code' => $code]);
        } catch (Exception $e) {
            $db->rollBack();
            $this->json(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()], 500);
        }
    }

    /** UPDATE ticket (status, priority, assignment) */
    public function update(): void
    {
        $this->requireAuth();
        $this->requireRole('admin', 'staff');
        $user = $this->currentUser();
        $id   = (int)($_POST['id'] ?? 0);
        $ticket = $this->ticketModel->find($id);
        if (!$ticket) { $this->json(['success' => false, 'message' => 'Không tìm thấy ticket'], 404); }

        $data = [];
        $oldStatus = $ticket['status'];

        if (isset($_POST['status'])) {
            $newStatus = $_POST['status'];
            $allowed = ['open','in_progress','pending','resolved','closed','cancelled'];
            if (!in_array($newStatus, $allowed)) {
                $this->json(['success' => false, 'message' => 'Trạng thái không hợp lệ'], 422);
            }
            $data['status'] = $newStatus;
            if ($newStatus === 'resolved') $data['resolved_at'] = date('Y-m-d H:i:s');
            if ($newStatus === 'closed')   $data['closed_at']   = date('Y-m-d H:i:s');
        }
        if (isset($_POST['priority'])) $data['priority']    = $_POST['priority'];
        if (isset($_POST['assigned_to'])) $data['assigned_to'] = (int)$_POST['assigned_to'] ?: null;

        if (empty($data)) { $this->json(['success' => false, 'message' => 'Không có dữ liệu cập nhật'], 400); }

        $db = Database::getInstance();
        $db->beginTransaction();
        try {
            $this->ticketModel->update($id, $data);

            if (!empty($data['status']) && $data['status'] !== $oldStatus) {
                $this->statusLogModel->log($id, $user['id'], $oldStatus, $data['status'], trim($_POST['note'] ?? ''));
            }

            $db->commit();
            $this->json(['success' => true, 'message' => 'Cập nhật thành công']);
        } catch (Exception $e) {
            $db->rollBack();
            $this->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /** DELETE ticket (admin only) */
    public function destroy(): void
    {
        $this->requireRole('admin');
        $id = (int)($_POST['id'] ?? 0);
        if (!$this->ticketModel->find($id)) {
            $this->json(['success' => false, 'message' => 'Không tìm thấy ticket'], 404);
        }
        $this->ticketModel->delete($id);
        $this->json(['success' => true, 'message' => 'Đã xoá ticket']);
    }

    /** Escalate overdue tickets (can be called by cron or manually) */
    public function escalate(): void
    {
        $this->requireRole('admin');
        $overdue = $this->ticketModel->overdueNotEscalated();
        $count   = 0;
        foreach ($overdue as $t) {
            $this->ticketModel->update($t['id'], ['escalated' => 1]);
            $this->escalationModel->create([
                'ticket_id'    => $t['id'],
                'escalated_by' => null,
                'escalated_to' => null,
                'reason'       => 'Tự động escalate: ticket quá hạn SLA',
            ]);
            $this->statusLogModel->log($t['id'], $_SESSION['user']['id'], $t['status'], $t['status'], 'Tự động escalate do quá hạn SLA');
            $count++;
        }
        $this->json(['success' => true, 'escalated' => $count]);
    }
}
