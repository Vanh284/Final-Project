<?php require_once __DIR__ . '/../layout/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold mb-0"><i class="bi bi-ticket-perforated me-2 text-primary"></i>Danh sách Ticket</h4>
    <a href="<?= APP_URL ?>/index.php?page=tickets&action=create" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i>Tạo Ticket mới
    </a>
</div>

<!-- Filters -->
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <input type="hidden" name="page" value="tickets">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control form-control-sm"
                       placeholder="🔍 Tìm theo tiêu đề hoặc mã..."
                       value="<?= htmlspecialchars($filters['search']) ?>">
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select form-select-sm">
                    <option value="">Tất cả trạng thái</option>
                    <?php foreach (['open'=>'Mở','in_progress'=>'Đang xử lý','pending'=>'Chờ phản hồi','resolved'=>'Đã giải quyết','closed'=>'Đã đóng','cancelled'=>'Đã huỷ'] as $v => $l): ?>
                    <option value="<?= $v ?>" <?= $filters['status']===$v?'selected':'' ?>><?= $l ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <select name="priority" class="form-select form-select-sm">
                    <option value="">Tất cả ưu tiên</option>
                    <?php foreach (['low'=>'Thấp','medium'=>'Trung bình','high'=>'Cao','critical'=>'Khẩn cấp'] as $v => $l): ?>
                    <option value="<?= $v ?>" <?= $filters['priority']===$v?'selected':'' ?>><?= $l ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <select name="category_id" class="form-select form-select-sm">
                    <option value="">Tất cả danh mục</option>
                    <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>" <?= $filters['category_id']==$cat['id']?'selected':'' ?>>
                        <?= htmlspecialchars($cat['name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-primary flex-fill">Lọc</button>
                <a href="<?= APP_URL ?>/index.php?page=tickets" class="btn btn-sm btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<!-- Table -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:120px">Mã ticket</th>
                        <th>Tiêu đề</th>
                        <th>Danh mục</th>
                        <th>Bộ phận</th>
                        <th>Ưu tiên</th>
                        <th>Trạng thái</th>
                        <th>Người tạo</th>
                        <th>Hạn xử lý</th>
                        <th style="width:80px">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($tickets as $t): ?>
                <tr>
                    <td>
                        <code class="text-primary"><?= $t['ticket_code'] ?></code>
                        <?php if ($t['escalated']): ?><i class="bi bi-exclamation-triangle-fill text-danger ms-1" title="Escalated"></i><?php endif; ?>
                    </td>
                    <td>
                        <a href="<?= APP_URL ?>/index.php?page=tickets&action=show&id=<?= $t['id'] ?>"
                           class="text-decoration-none fw-semibold text-dark">
                            <?= htmlspecialchars($t['title']) ?>
                        </a>
                        <?php if ($t['location']): ?><br><small class="text-muted"><i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($t['location']) ?></small><?php endif; ?>
                    </td>
                    <td class="small"><?= htmlspecialchars($t['category_name']) ?></td>
                    <td class="small"><?= htmlspecialchars($t['department_name']) ?></td>
                    <td><?php include __DIR__ . '/../partials/priority_badge.php'; ?></td>
                    <td><?php include __DIR__ . '/../partials/status_badge.php'; ?></td>
                    <td class="small"><?= htmlspecialchars($t['submitter_name']) ?></td>
                    <td class="small">
                        <?php if ($t['due_at']): ?>
                        <span class="<?= strtotime($t['due_at']) < time() && !in_array($t['status'], ['resolved','closed','cancelled']) ? 'text-danger fw-semibold' : 'text-muted' ?>">
                            <?= date('d/m H:i', strtotime($t['due_at'])) ?>
                        </span>
                        <?php else: ?>–<?php endif; ?>
                    </td>
                    <td>
                        <a href="<?= APP_URL ?>/index.php?page=tickets&action=show&id=<?= $t['id'] ?>"
                           class="btn btn-sm btn-outline-primary py-0">
                            <i class="bi bi-eye"></i>
                        </a>
                        <?php if (($_SESSION['user']['role']??'') === 'admin'): ?>
                        <button class="btn btn-sm btn-outline-danger py-0 ms-1 btn-delete-ticket"
                                data-id="<?= $t['id'] ?>" data-code="<?= $t['ticket_code'] ?>">
                            <i class="bi bi-trash"></i>
                        </button>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($tickets)): ?>
                <tr><td colspan="9" class="text-center text-muted py-5">
                    <i class="bi bi-inbox fs-2 d-block mb-2"></i>Không tìm thấy ticket nào
                </td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php if ($total > 0): ?>
    <div class="card-footer bg-white d-flex justify-content-between align-items-center">
        <small class="text-muted">Hiển thị <?= count($tickets) ?> / <?= $total ?> kết quả</small>
        <?php include __DIR__ . '/../partials/pagination.php'; ?>
    </div>
    <?php endif; ?>
</div>

<script>
// Delete ticket
document.querySelectorAll('.btn-delete-ticket').forEach(btn => {
    btn.addEventListener('click', function() {
        const id   = this.dataset.id;
        const code = this.dataset.code;
        if (!confirm(`Xoá ticket ${code}? Hành động này không thể hoàn tác!`)) return;
        fetch('<?= APP_URL ?>/index.php?page=tickets&action=destroy', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
            body: `id=${id}`
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                showFlash('success', `Đã xoá ticket ${code}`);
                setTimeout(() => location.reload(), 1000);
            } else {
                showFlash('danger', res.message);
            }
        });
    });
});
</script>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
