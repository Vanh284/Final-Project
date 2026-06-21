<?php require_once __DIR__ . '/../layout/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold mb-0"><i class="bi bi-tags me-2 text-primary"></i>Quản lý Danh mục</h4>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#catModal" onclick="resetCatForm()">
        <i class="bi bi-plus-lg me-1"></i>Thêm danh mục
    </button>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Tên danh mục</th><th>Bộ phận</th><th>SLA (giờ)</th>
                        <th>Ưu tiên mặc định</th><th>Từ khoá auto-route</th><th>Trạng thái</th><th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($categories as $c): ?>
                <tr>
                    <td class="fw-semibold"><?= htmlspecialchars($c['name']) ?></td>
                    <td><?= htmlspecialchars($c['department_name']) ?></td>
                    <td><span class="badge bg-light text-dark border"><?= $c['sla_hours'] ?>h</span></td>
                    <td><?php $t = $c; include __DIR__ . '/../partials/priority_badge.php'; $t = null; ?>
                        <?php
                        $pm = ['low'=>'bg-secondary','medium'=>'bg-info text-dark','high'=>'bg-warning text-dark','critical'=>'bg-danger'];
                        $pl = ['low'=>'Thấp','medium'=>'Trung bình','high'=>'Cao','critical'=>'Khẩn cấp'];
                        $p  = $c['priority_default'];
                        echo "<span class='badge {$pm[$p]}'>{$pl[$p]}</span>";
                        ?>
                    </td>
                    <td class="small text-muted"><?= htmlspecialchars($c['keywords'] ?? '–') ?></td>
                    <td><?= $c['is_active'] ? '<span class="badge bg-success">Hoạt động</span>' : '<span class="badge bg-secondary">Ẩn</span>' ?></td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary py-0 btn-edit-cat"
                                data-cat='<?= htmlspecialchars(json_encode($c, JSON_UNESCAPED_UNICODE)) ?>'>
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger py-0 ms-1 btn-del-cat"
                                data-id="<?= $c['id'] ?>" data-name="<?= htmlspecialchars($c['name']) ?>">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="catModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="catModalTitle">Thêm danh mục</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="catForm">
                    <input type="hidden" name="id" id="catId">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Tên danh mục <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="catName" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Bộ phận <span class="text-danger">*</span></label>
                            <select name="department_id" id="catDept" class="form-select" required>
                                <option value="">-- Chọn bộ phận --</option>
                                <?php foreach ($departments as $d): ?>
                                <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">SLA (giờ)</label>
                            <input type="number" name="sla_hours" id="catSla" class="form-control" value="24" min="1">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Ưu tiên mặc định</label>
                            <select name="priority_default" id="catPriority" class="form-select">
                                <option value="low">Thấp</option>
                                <option value="medium" selected>Trung bình</option>
                                <option value="high">Cao</option>
                                <option value="critical">Khẩn cấp</option>
                            </select>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <div class="form-check mb-1">
                                <input type="checkbox" name="is_active" id="catActive" class="form-check-input" value="1" checked>
                                <label class="form-check-label" for="catActive">Đang hoạt động</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Từ khoá tự động phân loại</label>
                            <input type="text" name="keywords" id="catKeywords" class="form-control"
                                   placeholder="wifi, mạng, kết nối, internet (phân cách bằng dấu phẩy)">
                            <div class="form-text">Hệ thống sẽ tự gợi ý danh mục khi tiêu đề ticket chứa các từ khoá này.</div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Huỷ</button>
                <button type="button" class="btn btn-primary" id="saveCatBtn">
                    <i class="bi bi-check2 me-1"></i>Lưu
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function resetCatForm() {
    document.getElementById('catModalTitle').textContent = 'Thêm danh mục';
    document.getElementById('catForm').reset();
    document.getElementById('catId').value = '';
    document.getElementById('catActive').checked = true;
}

document.querySelectorAll('.btn-edit-cat').forEach(btn => {
    btn.addEventListener('click', function() {
        const c = JSON.parse(this.dataset.cat);
        document.getElementById('catModalTitle').textContent = 'Chỉnh sửa danh mục';
        document.getElementById('catId').value       = c.id;
        document.getElementById('catName').value     = c.name;
        document.getElementById('catDept').value     = c.department_id;
        document.getElementById('catSla').value      = c.sla_hours;
        document.getElementById('catPriority').value = c.priority_default;
        document.getElementById('catKeywords').value = c.keywords ?? '';
        document.getElementById('catActive').checked = c.is_active == 1;
        new bootstrap.Modal(document.getElementById('catModal')).show();
    });
});

document.getElementById('saveCatBtn').addEventListener('click', () => {
    const id  = document.getElementById('catId').value;
    const url = id
        ? '<?= APP_URL ?>/index.php?page=categories&action=update'
        : '<?= APP_URL ?>/index.php?page=categories&action=store';
    const fd = new FormData(document.getElementById('catForm'));
    if (!document.getElementById('catActive').checked) fd.delete('is_active');
    fetch(url, { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' }, body: fd })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            showFlash('success', 'Đã lưu danh mục');
            bootstrap.Modal.getInstance(document.getElementById('catModal')).hide();
            setTimeout(() => location.reload(), 800);
        } else showFlash('danger', res.message);
    });
});

document.querySelectorAll('.btn-del-cat').forEach(btn => {
    btn.addEventListener('click', function() {
        if (!confirm(`Xoá danh mục "${this.dataset.name}"?`)) return;
        fetch('<?= APP_URL ?>/index.php?page=categories&action=destroy', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
            body: `id=${this.dataset.id}`
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) { showFlash('success', 'Đã xoá'); setTimeout(() => location.reload(), 800); }
            else showFlash('danger', 'Không thể xoá danh mục đang được sử dụng');
        });
    });
});
</script>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
