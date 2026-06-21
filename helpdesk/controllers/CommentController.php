<?php
class CommentController extends Controller
{
    private CommentModel $commentModel;
    private TicketModel  $ticketModel;

    public function __construct()
    {
        require_once __DIR__ . '/../models/CommentModel.php';
        require_once __DIR__ . '/../models/TicketModel.php';
        $this->commentModel = new CommentModel();
        $this->ticketModel  = new TicketModel();
    }

    /** Add comment (AJAX POST) */
    public function store(): void
    {
        $this->requireAuth();
        $user     = $this->currentUser();
        $ticketId = (int)($_POST['ticket_id'] ?? 0);
        $content  = trim($_POST['content'] ?? '');
        $isInternal = ($user['role'] !== 'user') && !empty($_POST['is_internal']) ? 1 : 0;

        if (!$content) { $this->json(['success' => false, 'message' => 'Nội dung không được để trống'], 422); }

        $ticket = $this->ticketModel->find($ticketId);
        if (!$ticket) { $this->json(['success' => false, 'message' => 'Ticket không tồn tại'], 404); }

        // Users can only comment on their own tickets
        if ($user['role'] === 'user' && $ticket['submitter_id'] != $user['id']) {
            $this->json(['success' => false, 'message' => 'Không có quyền'], 403);
        }

        $id = $this->commentModel->create([
            'ticket_id'   => $ticketId,
            'user_id'     => $user['id'],
            'content'     => $content,
            'is_internal' => $isInternal,
        ]);

        $this->json([
            'success' => true,
            'comment' => [
                'id'          => $id,
                'content'     => htmlspecialchars($content, ENT_QUOTES, 'UTF-8'),
                'full_name'   => $user['full_name'],
                'role'        => $user['role'],
                'is_internal' => $isInternal,
                'created_at'  => date('d/m/Y H:i'),
            ],
        ]);
    }

    /** Update comment */
    public function update(): void
    {
        $this->requireAuth();
        $user    = $this->currentUser();
        $id      = (int)($_POST['id'] ?? 0);
        $content = trim($_POST['content'] ?? '');

        if (!$content) { $this->json(['success' => false, 'message' => 'Nội dung không được để trống'], 422); }

        $comment = $this->commentModel->find($id);
        if (!$comment) { $this->json(['success' => false, 'message' => 'Không tìm thấy bình luận'], 404); }

        // Only comment owner or admin can edit
        if ($comment['user_id'] != $user['id'] && $user['role'] !== 'admin') {
            $this->json(['success' => false, 'message' => 'Không có quyền'], 403);
        }

        $this->commentModel->update($id, ['content' => $content]);
        $this->json(['success' => true, 'message' => 'Đã cập nhật']);
    }

    /** Delete comment */
    public function destroy(): void
    {
        $this->requireAuth();
        $user = $this->currentUser();
        $id   = (int)($_POST['id'] ?? 0);

        $comment = $this->commentModel->find($id);
        if (!$comment) { $this->json(['success' => false, 'message' => 'Không tìm thấy'], 404); }

        if ($comment['user_id'] != $user['id'] && $user['role'] !== 'admin') {
            $this->json(['success' => false, 'message' => 'Không có quyền'], 403);
        }

        $this->commentModel->delete($id);
        $this->json(['success' => true]);
    }
}
