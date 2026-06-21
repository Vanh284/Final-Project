<?php require_once __DIR__ . '/../layout/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold mb-0"><i class="bi bi-people me-2 text-primary"></i>Quản lý Người dùng</h4>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#userModal" onclick="resetUserForm()">
        <i class="bi bi-plus-lg me-1"></i>Thêm người dùng
    </button>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="userTable">
                <thead class="table-light">
                    <tr>
                        <th style="width:45px" class="text-center">#</th>
                        <th>Họ tên</th><th>Email</th>
                        <th>Vai trò</th><th>Bộ phận</th><th>Trạng thái</th><th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($users as $i => $u): ?>
                <tr id="user-row-<?= $u['id'] ?>">
                    <td class="text-center text-muted small fw-semibold row-num"><?= $i + 1 ?></td>
                    <td class="fw-semibold"><?= htmlspecialchars($u['full_name']) ?></td>
                    <td><?= htmlspecialchars($u['email']) ?></td>
                    <td>
                        <?php
                        $roleBadge = ['admin'=>'bg-danger','staff'=>'bg-primary','user'=>'bg-secondary'];
                        $roleLabel = ['admin'=>'Admin','staff'=>'Nhân viên','user'=>'Người dùng'];
                        $r = $u['role'];
                        ?>
                        <span class="badge <?= $roleBadge[$r]??'bg-secondary' ?>"><?= $roleLabel[$r]??$r ?></span>
                    </td>
                    <td><?= htmlspecialchars($u['department_name'] ?? '–') ?></td>
                    <td>
                        <?php if ($u['is_active']): ?>
                        <span class="badge bg-success">Hoạt động</span>
                        <?php else: ?>
                        <span class="badge bg-secondary">Vô hiệu</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary py-0 btn-edit-user"
                                data-user='<?= htmlspecialchars(json_encode($u, JSON_UNESCAPED_UNICODE)) ?>'>
                            <i class="bi bi-pencil"></i>
                        </button>
                        <?php if ($u['id'] != $_SESSION['user']['id']): ?>
                        <button class="btn btn-sm btn-outline-danger py-0 ms-1 btn-del-user"
                                data-id="<?= $u['id'] ?>" data-name="<?= htmlspecialchars($u['full_name']) ?>">
                            <i class="bi bi-trash"></i>
                        </button>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="userModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="userModalTitle">Thêm người dùng</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="userErrors" class="alert alert-danger d-none"></div>
                <form id="userForm" novalidate>
                    <input type="hidden" name="id" id="userId">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Họ tên <span class="text-danger">*</span></label>
                        <input type="text" name="full_name" id="userFullName" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" id="userEmail" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Mật khẩu <span class="text-danger" id="pwdRequired">*</span></label>
                        <input type="password" name="password" id="userPwd" class="form-control"
                               placeholder="Để trống = giữ nguyên khi sửa">
                        <div class="form-text">Tối thiểu 6 ký tự</div>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Vai trò</label>
                            <select name="role" id="userRole" class="form-select">
                                <option value="user">Người dùng</option>
                                <option value="staff">Nhân viên</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Bộ phận</label>
                            <select name="department_id" id="userDept" class="form-select">
                                <option value="">-- Không thuộc BP --</option>
                                <?php foreach ($depts as $d): ?>
                                <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="mt-3 form-check">
                        <input type="checkbox" name="is_active" id="userActive" class="form-check-input" value="1" checked>
                        <label class="form-check-label" for="userActive">Tài khoản hoạt động</label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Huỷ</button>
                <button type="button" class="btn btn-primary" id="saveUserBtn">
                    <i class="bi bi-check2 me-1"></i>Lưu
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Hàm reset số thứ tự toàn bảng
function reNumberRows() {
    document.querySelectorAll('#userTable tbody tr').forEach((row, idx) => {
        const cell = row.querySelector('.row-num');
        if (cell) cell.textContent = idx + 1;
    });
}

function resetUserForm() {
    document.getElementById('userModalTitle').textContent = 'Thêm người dùng';
    document.getElementById('userForm').reset();
    document.getElementById('userId').value = '';
    document.getElementById('userErrors').classList.add('d-none');
    document.getElementById('pwdRequired').style.display = '';
}

document.querySelectorAll('.btn-edit-user').forEach(btn => {
    btn.addEventListener('click', function() {
        const u = JSON.parse(this.dataset.user);
        document.getElementById('userModalTitle').textContent = 'Chỉnh sửa người dùng';
        document.getElementById('userId').value      = u.id;
        document.getElementById('userFullName').value= u.full_name;
        document.getElementById('userEmail').value   = u.email;
        document.getElementById('userPwd').value     = '';
        document.getElementById('userRole').value    = u.role;
        document.getElementById('userDept').value    = u.department_id ?? '';
        document.getElementById('userActive').checked= u.is_active == 1;
        document.getElementById('userErrors').classList.add('d-none');
        document.getElementById('pwdRequired').style.display = 'none';
        new bootstrap.Modal(document.getElementById('userModal')).show();
    });
});

document.getElementById('saveUserBtn').addEventListener('click', function() {
    const id  = document.getElementById('userId').value;
    const url = id
        ? '<?= APP_URL ?>/index.php?page=users&action=update'
        : '<?= APP_URL ?>/index.php?page=users&action=store';

    const fd = new FormData(document.getElementById('userForm'));
    if (!document.getElementById('userActive').checked) fd.delete('is_active');

    fetch(url, {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: fd
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            showFlash('success', id ? 'Đã cập nhật người dùng' : 'Đã thêm người dùng mới');
            bootstrap.Modal.getInstance(document.getElementById('userModal')).hide();
            setTimeout(() => location.reload(), 800);
        } else {
            const errBox = document.getElementById('userErrors');
            const msgs = res.errors || [res.message];
            errBox.innerHTML = msgs.map(m => `<div>• ${m}</div>`).join('');
            errBox.classList.remove('d-none');
        }
    });
});

document.querySelectorAll('.btn-del-user').forEach(btn => {
    btn.addEventListener('click', function() {
        if (!confirm(`Xoá người dùng "${this.dataset.name}"?`)) return;
        const userId = this.dataset.id;
        fetch('<?= APP_URL ?>/index.php?page=users&action=destroy', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
            body: 'id=' + userId
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                if (res.soft) {
                    // Vô hiệu hoá: đổi badge, không xoá row
                    const row = document.getElementById('user-row-' + userId);
                    if (row) {
                        const cell = row.querySelector('td:nth-child(6)');
                        if (cell) cell.innerHTML = '<span class="badge bg-secondary">Vô hiệu</span>';
                    }
                    showFlash('warning', res.message, 6000);
                } else {
                    // Xoá hẳn: remove row + reset STT
                    const row = document.getElementById('user-row-' + userId);
                    if (row) row.remove();
                    reNumberRows();
                    showFlash('success', 'Đã xoá người dùng');
                }
            } else {
                showFlash('danger', res.message || 'Không thể xoá');
            }
        });
    });
});
</script>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
