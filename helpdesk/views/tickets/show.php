<?php require_once __DIR__ . '/../layout/header.php'; ?>

<?php
$isAdminOrStaff = in_array($_SESSION['user']['role'], ['admin','staff']);
$isOwner        = $ticket['submitter_id'] == $_SESSION['user']['id'];
$canEdit        = $isAdminOrStaff;
$resolved       = in_array($ticket['status'], ['resolved','closed']);
?>

<div class="d-flex align-items-center gap-2 mb-3">
    <a href="<?= APP_URL ?>/index.php?page=tickets" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left"></i>
    </a>
    <h5 class="fw-bold mb-0 me-auto">
        <code class="text-primary"><?= $ticket['ticket_code'] ?></code>
        <?= htmlspecialchars($ticket['title']) ?>
    </h5>
    <?php if ($ticket['escalated']): ?>
    <span class="badge bg-danger"><i class="bi bi-exclamation-triangle me-1"></i>Escalated</span>
    <?php endif; ?>
    <?php include __DIR__ . '/../partials/priority_badge.php'; ?>
    <?php include __DIR__ . '/../partials/status_badge.php'; ?>
</div>

<div class="row g-3">
<!-- Left: ticket details + comments -->
<div class="col-lg-8">

    <!-- Details card -->
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <div class="row g-3 text-sm">
                <div class="col-sm-6">
                    <div class="text-muted small">Danh mục</div>
                    <div class="fw-semibold"><?= htmlspecialchars($ticket['category_name']) ?></div>
                </div>
                <div class="col-sm-6">
                    <div class="text-muted small">Bộ phận xử lý</div>
                    <div class="fw-semibold"><?= htmlspecialchars($ticket['department_name']) ?></div>
                </div>
                <div class="col-sm-6">
                    <div class="text-muted small">Người gửi</div>
                    <div><?= htmlspecialchars($ticket['submitter_name']) ?></div>
                </div>
                <div class="col-sm-6">
                    <div class="text-muted small">Người xử lý</div>
                    <div><?= $ticket['assignee_name'] ? htmlspecialchars($ticket['assignee_name']) : '<span class="text-muted">Chưa phân công</span>' ?></div>
                </div>
                <div class="col-sm-6">
                    <div class="text-muted small">Ngày tạo</div>
                    <div><?= date('d/m/Y H:i', strtotime($ticket['created_at'])) ?></div>
                </div>
                <div class="col-sm-6">
                    <div class="text-muted small">Hạn xử lý (SLA)</div>
                    <div class="<?= $ticket['due_at'] && strtotime($ticket['due_at']) < time() && !$resolved ? 'text-danger fw-semibold' : '' ?>">
                        <?= $ticket['due_at'] ? date('d/m/Y H:i', strtotime($ticket['due_at'])) : '–' ?>
                    </div>
                </div>
                <?php if ($ticket['location']): ?>
                <div class="col-12">
                    <div class="text-muted small">Vị trí</div>
                    <div><i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($ticket['location']) ?></div>
                </div>
                <?php endif; ?>
                <div class="col-12">
                    <div class="text-muted small mb-1">Mô tả</div>
                    <div class="bg-light rounded p-3"><?= nl2br(htmlspecialchars($ticket['description'])) ?></div>
                </div>
                <?php if ($ticket['attachment']): ?>
                <div class="col-12">
                    <div class="text-muted small mb-1">File đính kèm</div>
                    <a href="<?= APP_URL ?>/public/uploads/<?= htmlspecialchars($ticket['attachment']) ?>"
                       class="btn btn-sm btn-outline-secondary" target="_blank">
                        <i class="bi bi-paperclip me-1"></i><?= htmlspecialchars($ticket['attachment']) ?>
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Comments -->
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-white fw-semibold pt-3 border-0">
            <i class="bi bi-chat-text me-2 text-primary"></i>Trao đổi
        </div>
        <div class="card-body" id="commentsList">
            <?php if (empty($comments)): ?>
            <p class="text-muted text-center py-3">Chưa có trao đổi nào.</p>
            <?php else: ?>
            <?php foreach ($comments as $c): ?>
            <div class="d-flex gap-2 mb-3 comment-item" id="comment-<?= $c['id'] ?>">
                <div class="bg-primary bg-gradient text-white rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                     style="width:36px;height:36px;font-size:14px">
                    <?= mb_substr($c['full_name'], 0, 1) ?>
                </div>
                <div class="flex-grow-1">
                    <div class="d-flex justify-content-between">
                        <span class="fw-semibold small"><?= htmlspecialchars($c['full_name']) ?>
                            <?php if ($c['is_internal']): ?><span class="badge bg-secondary ms-1 small">Nội bộ</span><?php endif; ?>
                        </span>
                        <span class="text-muted small"><?= date('d/m/Y H:i', strtotime($c['created_at'])) ?></span>
                    </div>
                    <div class="bg-light rounded p-2 mt-1 comment-content"><?= nl2br(htmlspecialchars($c['content'])) ?></div>
                    <?php if ($_SESSION['user']['id'] == $c['user_id'] || $_SESSION['user']['role'] === 'admin'): ?>
                    <div class="mt-1">
                        <button class="btn btn-link btn-sm p-0 text-muted btn-edit-comment"
                                data-id="<?= $c['id'] ?>" data-content="<?= htmlspecialchars($c['content']) ?>">Sửa</button>
                        <button class="btn btn-link btn-sm p-0 text-danger ms-2 btn-del-comment"
                                data-id="<?= $c['id'] ?>">Xoá</button>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <?php if (!in_array($ticket['status'], ['closed','cancelled'])): ?>
        <div class="card-footer bg-white border-0 pt-0">
            <form id="commentForm">
                <input type="hidden" name="ticket_id" value="<?= $ticket['id'] ?>">
                <?php if ($isAdminOrStaff): ?>
                <div class="form-check form-switch mb-2">
                    <input class="form-check-input" type="checkbox" name="is_internal" id="isInternal" value="1">
                    <label class="form-check-label small text-muted" for="isInternal">Ghi chú nội bộ (ẩn với người dùng)</label>
                </div>
                <?php endif; ?>
                <div class="d-flex gap-2">
                    <textarea name="content" class="form-control" rows="2"
                              placeholder="Nhập nội dung trao đổi..."></textarea>
                    <button type="submit" class="btn btn-primary px-3">
                        <i class="bi bi-send-fill"></i>
                    </button>
                </div>
            </form>
        </div>
        <?php endif; ?>
    </div>

    <!-- Satisfaction survey -->
    <?php if ($resolved && $isOwner && !$survey): ?>
    <div class="card border-0 shadow-sm border-start border-4 border-success">
        <div class="card-body">
            <h6 class="fw-semibold mb-3"><i class="bi bi-star me-2 text-warning"></i>Đánh giá chất lượng xử lý</h6>
            <form id="surveyForm">
                <input type="hidden" name="ticket_id" value="<?= $ticket['id'] ?>">
                <div class="d-flex gap-2 mb-2" id="starRating">
                    <?php for ($s = 1; $s <= 5; $s++): ?>
                    <label class="fs-2 cursor-pointer star-label" data-val="<?= $s ?>">
                        <input type="radio" name="rating" value="<?= $s ?>" class="d-none" <?= $s==5?'checked':'' ?>>
                        <i class="bi bi-star<?= $s==5?'-fill':'' ?> text-warning"></i>
                    </label>
                    <?php endfor; ?>
                </div>
                <textarea name="comment" class="form-control mb-2" rows="2"
                          placeholder="Nhận xét thêm (tuỳ chọn)..."></textarea>
                <button type="submit" class="btn btn-warning btn-sm">
                    <i class="bi bi-send me-1"></i>Gửi đánh giá
                </button>
            </form>
        </div>
    </div>
    <?php elseif ($survey): ?>
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <h6 class="fw-semibold mb-2"><i class="bi bi-star-fill text-warning me-2"></i>Đánh giá của bạn</h6>
            <div class="text-warning fs-4 mb-1">
                <?= str_repeat('★', $survey['rating']) ?><?= str_repeat('☆', 5-$survey['rating']) ?>
            </div>
            <?php if ($survey['comment']): ?><p class="text-muted mb-0"><?= htmlspecialchars($survey['comment']) ?></p><?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Right: sidebar actions -->
<div class="col-lg-4">
    <!-- Update status (staff/admin only) -->
    <?php if ($canEdit): ?>
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-white fw-semibold pt-3 border-0">
            <i class="bi bi-pencil-square me-2 text-primary"></i>Cập nhật ticket
        </div>
        <div class="card-body">
            <form id="updateForm">
                <input type="hidden" name="id" value="<?= $ticket['id'] ?>">
                <div class="mb-2">
                    <label class="form-label small fw-semibold">Trạng thái</label>
                    <select name="status" class="form-select form-select-sm">
                        <?php foreach (['open'=>'Mở','in_progress'=>'Đang xử lý','pending'=>'Chờ phản hồi','resolved'=>'Đã giải quyết','closed'=>'Đã đóng','cancelled'=>'Đã huỷ'] as $v => $l): ?>
                        <option value="<?= $v ?>" <?= $ticket['status']===$v?'selected':'' ?>><?= $l ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-2">
                    <label class="form-label small fw-semibold">Ưu tiên</label>
                    <select name="priority" class="form-select form-select-sm">
                        <?php foreach (['low'=>'Thấp','medium'=>'Trung bình','high'=>'Cao','critical'=>'Khẩn cấp'] as $v => $l): ?>
                        <option value="<?= $v ?>" <?= $ticket['priority']===$v?'selected':'' ?>><?= $l ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-2">
                    <label class="form-label small fw-semibold">Phân công cho</label>
                    <select name="assigned_to" class="form-select form-select-sm">
                        <option value="">-- Chưa phân công --</option>
                        <?php foreach ($staffList as $s): ?>
                        <option value="<?= $s['id'] ?>" <?= $ticket['assigned_to']==$s['id']?'selected':'' ?>>
                            <?= htmlspecialchars($s['full_name']) ?> (<?= htmlspecialchars($s['department_name']??'') ?>)
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-2">
                    <label class="form-label small fw-semibold">Ghi chú</label>
                    <textarea name="note" class="form-control form-control-sm" rows="2"
                              placeholder="Lý do thay đổi trạng thái..."></textarea>
                </div>
                <button type="submit" class="btn btn-primary btn-sm w-100">
                    <i class="bi bi-check2 me-1"></i>Lưu thay đổi
                </button>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <!-- Status history -->
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-white fw-semibold pt-3 border-0">
            <i class="bi bi-clock-history me-2 text-secondary"></i>Lịch sử trạng thái
        </div>
        <div class="card-body py-2 px-3">
            <?php foreach ($statusLogs as $log): ?>
            <div class="d-flex gap-2 py-2 border-bottom">
                <div class="flex-shrink-0 mt-1">
                    <span class="badge bg-light text-dark border"><?= $log['new_status'] ?></span>
                </div>
                <div class="flex-grow-1 small">
                    <div class="text-muted"><?= date('d/m/Y H:i', strtotime($log['changed_at'])) ?> – <?= htmlspecialchars($log['full_name']) ?></div>
                    <?php if ($log['note']): ?><div class="text-dark"><?= htmlspecialchars($log['note']) ?></div><?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Escalation logs -->
    <?php if (!empty($escalations)): ?>
    <div class="card border-0 shadow-sm border-start border-4 border-danger mb-3">
        <div class="card-header bg-white fw-semibold pt-3 border-0">
            <i class="bi bi-exclamation-triangle me-2 text-danger"></i>Escalation
        </div>
        <div class="card-body py-2">
            <?php foreach ($escalations as $e): ?>
            <div class="small mb-2">
                <div class="text-muted"><?= date('d/m/Y H:i', strtotime($e['escalated_at'])) ?></div>
                <div><?= htmlspecialchars($e['reason']) ?></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>
</div>

<script>
// --- Update ticket form ---
document.getElementById('updateForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const fd = new FormData(this);
    fetch('<?= APP_URL ?>/index.php?page=tickets&action=update', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: fd
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) { showFlash('success', 'Đã cập nhật ticket'); setTimeout(() => location.reload(), 1000); }
        else showFlash('danger', res.message);
    });
});

// --- Comment form ---
document.getElementById('commentForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const fd = new FormData(this);
    fetch('<?= APP_URL ?>/index.php?page=comments&action=store', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: fd
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            const c = res.comment;
            const isInternal = c.is_internal ? '<span class="badge bg-secondary ms-1 small">Nội bộ</span>' : '';
            const html = `
            <div class="d-flex gap-2 mb-3 comment-item" id="comment-${c.id}">
                <div class="bg-primary bg-gradient text-white rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                     style="width:36px;height:36px;font-size:14px">${c.full_name.charAt(0)}</div>
                <div class="flex-grow-1">
                    <div class="d-flex justify-content-between">
                        <span class="fw-semibold small">${c.full_name}${isInternal}</span>
                        <span class="text-muted small">${c.created_at}</span>
                    </div>
                    <div class="bg-light rounded p-2 mt-1 comment-content">${c.content}</div>
                    <div class="mt-1">
                        <button class="btn btn-link btn-sm p-0 text-muted btn-edit-comment"
                                data-id="${c.id}" data-content="${c.content}">Sửa</button>
                        <button class="btn btn-link btn-sm p-0 text-danger ms-2 btn-del-comment"
                                data-id="${c.id}">Xoá</button>
                    </div>
                </div>
            </div>`;
            document.getElementById('commentsList').insertAdjacentHTML('beforeend', html);
            this.reset();
            bindCommentActions();
        } else showFlash('danger', res.message);
    });
});

// --- Delete comment ---
function bindCommentActions() {
    document.querySelectorAll('.btn-del-comment').forEach(btn => {
        btn.onclick = function() {
            if (!confirm('Xoá bình luận này?')) return;
            const id = this.dataset.id;
            fetch('<?= APP_URL ?>/index.php?page=comments&action=destroy', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
                body: `id=${id}`
            })
            .then(r => r.json())
            .then(res => { if (res.success) document.getElementById('comment-' + id)?.remove(); });
        };
    });
    document.querySelectorAll('.btn-edit-comment').forEach(btn => {
        btn.onclick = function() {
            const id      = this.dataset.id;
            const content = this.dataset.content;
            const newContent = prompt('Chỉnh sửa bình luận:', content);
            if (!newContent || newContent === content) return;
            fetch('<?= APP_URL ?>/index.php?page=comments&action=update', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
                body: `id=${id}&content=${encodeURIComponent(newContent)}`
            })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    const el = document.querySelector(`#comment-${id} .comment-content`);
                    if (el) el.textContent = newContent;
                    this.dataset.content = newContent;
                }
            });
        };
    });
}
bindCommentActions();

// --- Star rating ---
document.querySelectorAll('.star-label').forEach(label => {
    label.addEventListener('click', function() {
        const val = parseInt(this.dataset.val);
        document.querySelectorAll('.star-label').forEach((l, i) => {
            const icon = l.querySelector('i');
            icon.className = (i < val) ? 'bi bi-star-fill text-warning' : 'bi bi-star text-warning';
        });
        document.querySelector(`input[name="rating"][value="${val}"]`).checked = true;
    });
});

// --- Survey form ---
document.getElementById('surveyForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const fd = new FormData(this);
    fetch('<?= APP_URL ?>/index.php?page=surveys&action=store', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: fd
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) { showFlash('success', 'Cảm ơn bạn đã đánh giá!'); setTimeout(() => location.reload(), 1000); }
        else showFlash('danger', res.message);
    });
});
</script>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
