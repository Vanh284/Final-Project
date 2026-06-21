<?php require_once __DIR__ . '/../layout/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold mb-0"><i class="bi bi-building me-2 text-primary"></i>Quản lý Bộ phận</h4>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#deptModal" onclick="resetDeptForm()">
        <i class="bi bi-plus-lg me-1"></i>Thêm bộ phận
    </button>
</div>

<div class="row g-3">
<?php foreach ($departments as $d): ?>
<div class="col-md-4">
    <div class="card border-0 shadow-sm h-100">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h6 class="fw-bold mb-1"><?= htmlspecialchars($d['name']) ?></h6>
                    <?php if ($d['description']): ?>
                    <p class="text-muted small mb-2"><?= htmlspecialchars($d['description']) ?></p>
                    <?php endif; ?>
                    <div class="small text-muted">
                        <i class="bi bi-person-badge me-1"></i>Trưởng BP:
                        <?= $d['manager_name'] ? htmlspecialchars($d['manager_name']) : '<em>Chưa phân công</em>' ?>
                    </div>
                </div>
                <span class="badge <?= $d['is_active'] ? 'bg-success' : 'bg-secondary' ?>">
                    <?= $d['is_active'] ? 'Hoạt động' : 'Tạm ngưng' ?>
                </span>
            </div>
        </div>
        <div class="card-footer bg-white border-0 d-flex gap-2">
            <button class="btn btn-sm btn-outline-primary flex-fill btn-edit-dept"
                    data-dept='<?= htmlspecialchars(json_encode($d, JSON_UNESCAPED_UNICODE)) ?>'>
                <i class="bi bi-pencil me-1"></i>Sửa
            </button>
            <button class="btn btn-sm btn-outline-danger btn-del-dept"
                    data-id="<?= $d['id'] ?>" data-name="<?= htmlspecialchars($d['name']) ?>">
                <i class="bi bi-trash"></i>
            </button>
        </div>
    </div>
</div>
<?php endforeach; ?>
</div>

<!-- Modal -->
<div class="modal fade" id="deptModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deptModalTitle">Thêm bộ phận</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="deptForm">
                    <input type="hidden" name="id" id="deptId">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Tên bộ phận <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="deptName" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Mô tả</label>
                        <textarea name="description" id="deptDesc" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Trưởng bộ phận</label>
                        <select name="manager_id" id="deptManager" class="form-select">
                            <option value="">-- Chưa phân công --</option>
                            <?php foreach ($staff as $s): ?>
                            <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['full_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" name="is_active" id="deptActive" class="form-check-input" value="1" checked>
                        <label class="form-check-label" for="deptActive">Đang hoạt động</label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Huỷ</button>
                <button type="button" class="btn btn-primary" id="saveDeptBtn">
                    <i class="bi bi-check2 me-1"></i>Lưu
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function resetDeptForm() {
    document.getElementById('deptModalTitle').textContent = 'Thêm bộ phận';
    document.getElementById('deptForm').reset();
    document.getElementById('deptId').value = '';
}

document.querySelectorAll('.btn-edit-dept').forEach(btn => {
    btn.addEventListener('click', function() {
        const d = JSON.parse(this.dataset.dept);
        document.getElementById('deptModalTitle').textContent = 'Chỉnh sửa bộ phận';
        document.getElementById('deptId').value      = d.id;
        document.getElementById('deptName').value    = d.name;
        document.getElementById('deptDesc').value    = d.description ?? '';
        document.getElementById('deptManager').value = d.manager_id ?? '';
        document.getElementById('deptActive').checked= d.is_active == 1;
        new bootstrap.Modal(document.getElementById('deptModal')).show();
    });
});

document.getElementById('saveDeptBtn').addEventListener('click', () => {
    const id  = document.getElementById('deptId').value;
    const url = id
        ? '<?= APP_URL ?>/index.php?page=departments&action=update'
        : '<?= APP_URL ?>/index.php?page=departments&action=store';
    const fd = new FormData(document.getElementById('deptForm'));
    if (!document.getElementById('deptActive').checked) fd.delete('is_active');
    fetch(url, { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' }, body: fd })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            showFlash('success', 'Đã lưu bộ phận');
            bootstrap.Modal.getInstance(document.getElementById('deptModal')).hide();
            setTimeout(() => location.reload(), 800);
        } else showFlash('danger', res.message);
    });
});

document.querySelectorAll('.btn-del-dept').forEach(btn => {
    btn.addEventListener('click', function() {
        if (!confirm(`Xoá bộ phận "${this.dataset.name}"?`)) return;
        fetch('<?= APP_URL ?>/index.php?page=departments&action=destroy', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
            body: `id=${this.dataset.id}`
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) { showFlash('success', 'Đã xoá'); setTimeout(() => location.reload(), 800); }
            else showFlash('danger', res.message ?? 'Không thể xoá');
        });
    });
});
</script>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
