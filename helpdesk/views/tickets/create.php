<?php require_once __DIR__ . '/../layout/header.php'; ?>

<div class="row justify-content-center">
<div class="col-lg-8">

<div class="d-flex align-items-center gap-2 mb-4">
    <a href="<?= APP_URL ?>/index.php?page=tickets" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left"></i>
    </a>
    <h4 class="fw-bold mb-0"><i class="bi bi-plus-circle me-2 text-primary"></i>Tạo Ticket mới</h4>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <div id="createErrors" class="alert alert-danger d-none"></div>

        <form id="createTicketForm" novalidate enctype="multipart/form-data">
            <div class="mb-3">
                <label class="form-label fw-semibold">Tiêu đề <span class="text-danger">*</span></label>
                <input type="text" name="title" id="title" class="form-control"
                       placeholder="Mô tả ngắn gọn vấn đề của bạn..." required minlength="5">
                <div class="invalid-feedback">Tiêu đề phải có ít nhất 5 ký tự.</div>
                <!-- Auto-suggest category -->
                <div id="categorySuggest" class="form-text text-info d-none">
                    <i class="bi bi-lightbulb me-1"></i>Gợi ý danh mục: <strong id="suggestName"></strong>
                    <a href="#" id="applySuggest" class="ms-1">[Áp dụng]</a>
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Danh mục <span class="text-danger">*</span></label>
                    <select name="category_id" id="categorySelect" class="form-select" required>
                        <option value="">-- Chọn danh mục --</option>
                        <?php
                        $currentDept = '';
                        foreach ($categories as $cat):
                            if ($cat['department_name'] !== $currentDept):
                                if ($currentDept) echo '</optgroup>';
                                echo '<optgroup label="' . htmlspecialchars($cat['department_name']) . '">';
                                $currentDept = $cat['department_name'];
                            endif;
                        ?>
                        <option value="<?= $cat['id'] ?>"
                                data-sla="<?= $cat['sla_hours'] ?>"
                                data-priority="<?= $cat['priority_default'] ?>">
                            <?= htmlspecialchars($cat['name']) ?>
                        </option>
                        <?php endforeach; ?>
                        <?php if ($currentDept) echo '</optgroup>'; ?>
                    </select>
                    <div class="invalid-feedback">Vui lòng chọn danh mục.</div>
                    <div id="slaInfo" class="form-text text-muted d-none">
                        <i class="bi bi-clock me-1"></i>SLA: <span id="slaHours"></span> giờ
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Mức độ ưu tiên</label>
                    <select name="priority" id="prioritySelect" class="form-select">
                        <option value="low">🟢 Thấp</option>
                        <option value="medium" selected>🟡 Trung bình</option>
                        <option value="high">🔴 Cao</option>
                        <option value="critical">⚠️ Khẩn cấp</option>
                    </select>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Mô tả chi tiết <span class="text-danger">*</span></label>
                <textarea name="description" id="description" class="form-control" rows="5"
                          placeholder="Mô tả chi tiết vấn đề, thời điểm xảy ra, các bước đã thử..."
                          required minlength="10"></textarea>
                <div class="invalid-feedback">Mô tả phải có ít nhất 10 ký tự.</div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Vị trí / Phòng</label>
                    <input type="text" name="location" class="form-control"
                           placeholder="Vd: Phòng B201, Tầng 3 nhà A...">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">File đính kèm</label>
                    <input type="file" name="attachment" class="form-control"
                           accept=".jpg,.jpeg,.png,.gif,.pdf,.doc,.docx,.zip">
                    <div class="form-text text-muted">Tối đa 5MB. Cho phép: ảnh, PDF, Word, ZIP</div>
                </div>
            </div>

            <div class="d-flex gap-2 justify-content-end">
                <a href="<?= APP_URL ?>/index.php?page=tickets" class="btn btn-outline-secondary">
                    <i class="bi bi-x-lg me-1"></i>Huỷ
                </a>
                <button type="submit" class="btn btn-primary px-4" id="submitBtn">
                    <i class="bi bi-send me-1"></i>Gửi Ticket
                </button>
            </div>
        </form>
    </div>
</div>
</div>
</div>

<script>
// Auto-suggest category from keywords
const categories = <?= json_encode(array_map(fn($c) => [
    'id'       => $c['id'],
    'name'     => $c['name'],
    'keywords' => $c['keywords'] ?? '',
    'sla'      => $c['sla_hours'],
    'priority' => $c['priority_default'],
], $categories), JSON_UNESCAPED_UNICODE) ?>;

let suggestedId = null;

document.getElementById('title').addEventListener('input', function() {
    const text = this.value.toLowerCase();
    let found = null;
    for (const cat of categories) {
        if (!cat.keywords) continue;
        for (const kw of cat.keywords.split(',')) {
            if (text.includes(kw.trim().toLowerCase())) {
                found = cat; break;
            }
        }
        if (found) break;
    }
    const box = document.getElementById('categorySuggest');
    if (found) {
        document.getElementById('suggestName').textContent = found.name;
        suggestedId = found.id;
        box.classList.remove('d-none');
    } else {
        box.classList.add('d-none');
        suggestedId = null;
    }
});

document.getElementById('applySuggest').addEventListener('click', function(e) {
    e.preventDefault();
    if (!suggestedId) return;
    const sel = document.getElementById('categorySelect');
    sel.value = suggestedId;
    sel.dispatchEvent(new Event('change'));
    document.getElementById('categorySuggest').classList.add('d-none');
});

// Show SLA info on category select
document.getElementById('categorySelect').addEventListener('change', function() {
    const opt = this.options[this.selectedIndex];
    const sla = opt.dataset.sla;
    if (sla) {
        document.getElementById('slaHours').textContent = sla;
        document.getElementById('slaInfo').classList.remove('d-none');
        // Set priority default
        const priority = opt.dataset.priority;
        if (priority) document.getElementById('prioritySelect').value = priority;
    } else {
        document.getElementById('slaInfo').classList.add('d-none');
    }
});

// Submit via AJAX
document.getElementById('createTicketForm').addEventListener('submit', function(e) {
    e.preventDefault();
    if (!this.checkValidity()) {
        this.classList.add('was-validated'); return;
    }
    const btn = document.getElementById('submitBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Đang gửi...';

    const fd = new FormData(this);
    fetch('<?= APP_URL ?>/index.php?page=tickets&action=store', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: fd
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            const assignMsg = res.assigned_name
                ? ` → phân công cho <strong>${res.assigned_name}</strong>`
                : '';
            showFlash('success', `Ticket <strong>${res.ticket_code}</strong> đã tạo thành công!${assignMsg}`);
            setTimeout(() => window.location.href = '<?= APP_URL ?>/index.php?page=tickets&action=show&id=' + res.ticket_id, 1500);
        } else {
            const errBox = document.getElementById('createErrors');
            const msgs = res.errors || [res.message];
            errBox.innerHTML = msgs.map(m => `<div>• ${m}</div>`).join('');
            errBox.classList.remove('d-none');
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-send me-1"></i>Gửi Ticket';
        }
    });
});
</script>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
