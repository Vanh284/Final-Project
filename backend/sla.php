<?php require_once __DIR__ . '/../layout/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div class="d-flex align-items-center gap-2">
        <h4 class="fw-bold mb-0">
            <i class="bi bi-alarm me-2 text-danger"></i>SLA & Escalation
        </h4>
        <span class="badge bg-danger-subtle text-danger border border-danger-subtle ms-2">Backend Focus #2</span>
    </div>
    <?php if ($_SESSION['user']['role'] === 'admin'): ?>
    <button class="btn btn-danger" id="runEscalationBtn">
        <i class="bi bi-lightning-charge me-1"></i>Chạy Escalation ngay
    </button>
    <?php endif; ?>
</div>

<!-- Info box -->
<div class="alert alert-warning border-0 shadow-sm mb-4">
    <i class="bi bi-info-circle me-2"></i>
    <strong>Cách hoạt động:</strong> Mỗi danh mục ticket có <strong>SLA (giờ)</strong> tối đa để xử lý.
    Khi ticket quá hạn mà chưa resolved/closed, hệ thống tự động <strong>escalate</strong> lên
    trưởng bộ phận hoặc Admin, ghi nhận vào <code>escalation_logs</code>.
</div>

<!-- SLA Stats per department -->
<div class="row g-3 mb-4">
<?php foreach ($slaStats as $s):
    $total      = (int)$s['total'];
    $onTime     = (int)$s['resolved_on_time'];
    $rate       = $total > 0 ? round(($onTime / max($s['resolved'], 1)) * 100) : 0;
    $avgHours   = $s['avg_resolve_minutes'] ? round($s['avg_resolve_minutes'] / 60, 1) : null;
?>
<div class="col-md-4">
    <div class="card border-0 shadow-sm h-100">
        <div class="card-body">
            <h6 class="fw-bold mb-3"><?= htmlspecialchars($s['department_name']) ?></h6>
            <div class="row g-2 text-center mb-3">
                <div class="col-4">
                    <div class="fs-4 fw-bold text-primary"><?= $total ?></div>
                    <div class="small text-muted">Tổng</div>
                </div>
                <div class="col-4">
                    <div class="fs-4 fw-bold text-warning"><?= $s['currently_overdue'] ?></div>
                    <div class="small text-muted">Quá hạn</div>
                </div>
                <div class="col-4">
                    <div class="fs-4 fw-bold text-danger"><?= $s['escalated'] ?></div>
                    <div class="small text-muted">Escalated</div>
                </div>
            </div>
            <div class="mb-1 d-flex justify-content-between small">
                <span>Xử lý đúng hạn</span>
                <strong><?= $rate ?>%</strong>
            </div>
            <div class="progress mb-2" style="height:8px">
                <div class="progress-bar <?= $rate >= 80 ? 'bg-success' : ($rate >= 50 ? 'bg-warning' : 'bg-danger') ?>"
                     style="width:<?= $rate ?>%"></div>
            </div>
            <?php if ($avgHours): ?>
            <div class="small text-muted text-center">
                Thời gian xử lý TB: <strong><?= $avgHours ?> giờ</strong>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php endforeach; ?>
</div>

<div class="row g-4">
<!-- Overdue tickets -->
<div class="col-lg-6">
    <div class="card border-0 shadow-sm border-start border-4 border-warning">
        <div class="card-header bg-white fw-semibold pt-3 border-0 d-flex justify-content-between">
            <span><i class="bi bi-hourglass-split me-2 text-warning"></i>Ticket quá hạn SLA chưa escalate</span>
            <span class="badge bg-warning text-dark" id="overdueCount"><?= count($overdue) ?></span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive" style="max-height:400px;overflow-y:auto">
                <table class="table table-hover align-middle mb-0 small">
                    <thead class="table-light sticky-top">
                        <tr><th>Mã</th><th>Tiêu đề</th><th>Bộ phận</th><th>Trễ</th><th>Ưu tiên</th></tr>
                    </thead>
                    <tbody id="overdueTable">
                    <?php if (empty($overdue)): ?>
                    <tr><td colspan="5" class="text-center text-success py-4">
                        <i class="bi bi-check-circle me-2"></i>Không có ticket quá hạn!
                    </td></tr>
                    <?php else: foreach ($overdue as $t): ?>
                    <tr>
                        <td>
                            <a href="<?= APP_URL ?>/index.php?page=tickets&action=show&id=<?= $t['id'] ?>"
                               class="text-decoration-none">
                                <code><?= $t['ticket_code'] ?></code>
                            </a>
                        </td>
                        <td><?= mb_strimwidth(htmlspecialchars($t['title']), 0, 35, '…') ?></td>
                        <td><?= htmlspecialchars($t['department_name']) ?></td>
                        <td>
                            <?php $mins = (int)$t['overdue_minutes']; ?>
                            <span class="badge bg-danger">
                                <?= $mins >= 60 ? round($mins/60,1).'h' : $mins.'m' ?>
                            </span>
                        </td>
                        <td><?php include __DIR__ . '/../partials/priority_badge.php'; ?></td>
                    </tr>
                    <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Escalated tickets -->
<div class="col-lg-6">
    <div class="card border-0 shadow-sm border-start border-4 border-danger">
        <div class="card-header bg-white fw-semibold pt-3 border-0 d-flex justify-content-between">
            <span><i class="bi bi-exclamation-triangle me-2 text-danger"></i>Đã escalate</span>
            <span class="badge bg-danger"><?= count($escalated) ?></span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive" style="max-height:400px;overflow-y:auto">
                <table class="table table-hover align-middle mb-0 small">
                    <thead class="table-light sticky-top">
                        <tr><th>Mã</th><th>Tiêu đề</th><th>Chuyển lên</th><th>Thời điểm</th></tr>
                    </thead>
                    <tbody>
                    <?php if (empty($escalated)): ?>
                    <tr><td colspan="4" class="text-center text-muted py-4">Chưa có escalation nào</td></tr>
                    <?php else: foreach ($escalated as $e): ?>
                    <tr>
                        <td>
                            <a href="<?= APP_URL ?>/index.php?page=tickets&action=show&id=<?= $e['id'] ?>"
                               class="text-decoration-none">
                                <code><?= $e['ticket_code'] ?></code>
                            </a>
                        </td>
                        <td><?= mb_strimwidth(htmlspecialchars($e['title']), 0, 30, '…') ?></td>
                        <td class="text-danger small">
                            <?= $e['escalated_to_name'] ? htmlspecialchars($e['escalated_to_name']) : 'Admin' ?>
                        </td>
                        <td class="text-muted">
                            <?= $e['escalated_at'] ? date('d/m H:i', strtotime($e['escalated_at'])) : '–' ?>
                        </td>
                    </tr>
                    <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</div>

<!-- Escalation result modal -->
<div class="modal fade" id="escalationModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="bi bi-lightning-charge me-2"></i>Kết quả Escalation</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="escalationResult"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"
                        onclick="location.reload()">Đóng & Làm mới</button>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('runEscalationBtn')?.addEventListener('click', async function() {
    if (!confirm('Chạy escalation cho tất cả ticket quá hạn SLA?\nHành động này sẽ đánh dấu escalated và ghi log.')) return;
    this.disabled = true;
    this.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Đang xử lý...';

    try {
        const res = await postJson('<?= APP_URL ?>/index.php?page=backend&action=runEscalation', {});
        const d   = res.data;
        let html  = `<div class="text-center mb-3">
            <div class="display-4 fw-bold text-danger">${d.escalated_count}</div>
            <div class="text-muted">ticket đã được escalate</div>
        </div>`;

        if (d.escalated.length) {
            html += '<ul class="list-group list-group-flush">';
            d.escalated.forEach(t => {
                html += `<li class="list-group-item d-flex justify-content-between py-2">
                    <div>
                        <code>${t.ticket_code}</code>
                        <span class="ms-2 small">${t.title.substring(0, 40)}</span>
                    </div>
                    <span class="badge bg-danger small">${t.department_name}</span>
                </li>`;
            });
            html += '</ul>';
        } else {
            html = '<div class="text-center text-success py-3"><i class="bi bi-check-circle fs-2 d-block mb-2"></i>Không có ticket nào quá hạn.</div>';
        }

        document.getElementById('escalationResult').innerHTML = html;
        new bootstrap.Modal(document.getElementById('escalationModal')).show();
    } finally {
        this.disabled = false;
        this.innerHTML = '<i class="bi bi-lightning-charge me-1"></i>Chạy Escalation ngay';
    }
});

// Auto-refresh overdue count every 60s
setInterval(async () => {
    try {
        const res = await postJson('<?= APP_URL ?>/index.php?page=backend&action=overdueList', {});
        document.getElementById('overdueCount').textContent = res.count;
    } catch {}
}, 60000);
</script>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
