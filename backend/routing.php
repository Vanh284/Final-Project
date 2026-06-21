<?php require_once __DIR__ . '/../layout/header.php'; ?>

<div class="d-flex align-items-center gap-2 mb-4">
    <h4 class="fw-bold mb-0">
        <i class="bi bi-diagram-3 me-2 text-primary"></i>Ticket Routing Engine
    </h4>
    <span class="badge bg-primary-subtle text-primary border border-primary-subtle ms-2">Backend Focus #1</span>
</div>

<!-- Info box -->
<div class="alert alert-info border-0 shadow-sm mb-4">
    <i class="bi bi-info-circle me-2"></i>
    <strong>Cách hoạt động:</strong> Khi ticket được tạo, hệ thống phân tích tiêu đề + mô tả bằng <strong>keyword scoring</strong>
    (khớp title = 3đ, khớp mô tả = 1đ) để tự động chọn danh mục và nhân viên có ít ticket nhất trong bộ phận phù hợp.
</div>

<div class="row g-4">
<!-- Left: live demo -->
<div class="col-lg-5">
    <div class="card border-0 shadow-sm h-100">
        <div class="card-header bg-white fw-semibold pt-3 border-0">
            <i class="bi bi-play-circle me-2 text-success"></i>Demo phân loại trực tiếp
        </div>
        <div class="card-body">
            <form id="routeForm">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Tiêu đề ticket</label>
                    <input type="text" id="routeTitle" class="form-control"
                           placeholder="Vd: Wifi phòng lab A101 bị mất kết nối..."
                           value="Wifi phòng lab A101 bị mất kết nối liên tục">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Mô tả (tuỳ chọn)</label>
                    <textarea id="routeDesc" class="form-control" rows="3"
                              placeholder="Mô tả chi tiết thêm...">Trong buổi thực hành hôm nay không vào được internet, đã thử kết nối lại nhiều lần vẫn không được.</textarea>
                </div>
                <button type="submit" class="btn btn-primary w-100" id="analyseBtn">
                    <i class="bi bi-search me-2"></i>Phân tích & Route
                </button>
            </form>

            <!-- Result -->
            <div id="routeResult" class="mt-4 d-none">
                <hr>
                <h6 class="fw-semibold mb-3">Kết quả phân tích</h6>

                <!-- Confidence badge -->
                <div class="d-flex align-items-center gap-2 mb-3">
                    <span class="text-muted small">Độ tin cậy:</span>
                    <span id="confidenceBadge" class="badge fs-6"></span>
                    <span id="topScore" class="text-muted small"></span>
                </div>

                <!-- Winning category -->
                <div id="winnerBox" class="rounded-3 p-3 mb-3 bg-success-subtle border border-success-subtle d-none">
                    <div class="small text-muted mb-1">Danh mục được chọn</div>
                    <div class="fw-bold fs-6" id="winnerCat"></div>
                    <div class="small text-muted" id="winnerDept"></div>
                    <div class="small mt-1" id="winnerSla"></div>
                </div>
                <div id="noMatchBox" class="alert alert-warning py-2 d-none">
                    <i class="bi bi-question-circle me-2"></i>Không tìm thấy danh mục phù hợp. Vui lòng chọn thủ công.
                </div>

                <!-- Suggested staff -->
                <div id="staffBox" class="rounded-3 p-3 mb-3 bg-info-subtle border border-info-subtle d-none">
                    <div class="small text-muted mb-1">Nhân viên được gợi ý (ít ticket nhất)</div>
                    <div class="fw-semibold" id="staffName"></div>
                    <div class="small text-muted" id="staffLoad"></div>
                </div>

                <!-- Keyword matches -->
                <div id="matchedKeywords" class="mb-3"></div>

                <!-- Score table -->
                <div>
                    <div class="small fw-semibold text-muted mb-2">Điểm toàn bộ danh mục</div>
                    <div id="scoreTable"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Right: routing history -->
<div class="col-lg-7">
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white fw-semibold pt-3 border-0">
            <i class="bi bi-clock-history me-2 text-secondary"></i>Lịch sử phân loại gần đây (20 tickets)
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 small">
                    <thead class="table-light">
                        <tr>
                            <th>Mã</th><th>Tiêu đề</th><th>Danh mục</th>
                            <th>Bộ phận</th><th>Nhân viên</th><th>Ngày</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($history as $h): ?>
                    <tr>
                        <td><code><?= $h['ticket_code'] ?></code></td>
                        <td>
                            <a href="<?= APP_URL ?>/index.php?page=tickets&action=show&id=<?= $h['id'] ?>"
                               class="text-decoration-none">
                                <?= mb_strimwidth(htmlspecialchars($h['title']), 0, 45, '…') ?>
                            </a>
                        </td>
                        <td><?= htmlspecialchars($h['category_name']) ?></td>
                        <td><?= htmlspecialchars($h['department_name']) ?></td>
                        <td><?= $h['assignee_name'] ? htmlspecialchars($h['assignee_name']) : '<span class="text-muted">–</span>' ?></td>
                        <td class="text-muted"><?= date('d/m H:i', strtotime($h['created_at'])) ?></td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Keyword config info -->
    <div class="card border-0 shadow-sm mt-3">
        <div class="card-header bg-white fw-semibold pt-3 border-0">
            <i class="bi bi-key me-2 text-warning"></i>Từ khoá đang cấu hình
        </div>
        <div class="card-body p-0">
            <table class="table table-sm mb-0 small">
                <thead class="table-light">
                    <tr><th>Danh mục</th><th>Bộ phận</th><th>SLA</th><th>Từ khoá</th></tr>
                </thead>
                <tbody>
                <?php
                require_once __DIR__ . '/../../models/CategoryModel.php';
                $cats = (new CategoryModel())->allWithDepartment();
                foreach ($cats as $c):
                ?>
                <tr>
                    <td class="fw-semibold"><?= htmlspecialchars($c['name']) ?></td>
                    <td><?= htmlspecialchars($c['department_name']) ?></td>
                    <td><span class="badge bg-light text-dark border"><?= $c['sla_hours'] ?>h</span></td>
                    <td>
                        <?php if ($c['keywords']): ?>
                        <?php foreach (explode(',', $c['keywords']) as $kw): ?>
                        <span class="badge bg-secondary-subtle text-secondary border me-1"><?= htmlspecialchars(trim($kw)) ?></span>
                        <?php endforeach; ?>
                        <?php else: ?>
                        <span class="text-muted">–</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</div>

<script>
document.getElementById('routeForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = document.getElementById('analyseBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Đang phân tích...';

    const title = document.getElementById('routeTitle').value;
    const desc  = document.getElementById('routeDesc').value;

    try {
        const res = await postJson('<?= APP_URL ?>/index.php?page=backend&action=analyseRoute', {title, description: desc});
        if (!res.success) { showFlash('danger', res.message); return; }

        const r = res.result;
        document.getElementById('routeResult').classList.remove('d-none');

        // Confidence
        const confMap = {
            high:   ['bg-success', 'Cao ✓'],
            medium: ['bg-warning text-dark', 'Trung bình'],
            low:    ['bg-secondary', 'Thấp'],
            none:   ['bg-danger', 'Không khớp'],
        };
        const [cls, lbl] = confMap[r.confidence] ?? ['bg-secondary','?'];
        document.getElementById('confidenceBadge').className = `badge ${cls} fs-6`;
        document.getElementById('confidenceBadge').textContent = lbl;
        document.getElementById('topScore').textContent = `(${r.top_score} điểm)`;

        // Winner category
        if (r.category) {
            document.getElementById('winnerBox').classList.remove('d-none');
            document.getElementById('noMatchBox').classList.add('d-none');
            document.getElementById('winnerCat').textContent  = r.category.name;
            document.getElementById('winnerDept').textContent = '📂 ' + r.category.department_name;
            document.getElementById('winnerSla').innerHTML    = `⏱ SLA: <strong>${r.category.sla_hours} giờ</strong>`;
        } else {
            document.getElementById('winnerBox').classList.add('d-none');
            document.getElementById('noMatchBox').classList.remove('d-none');
        }

        // Suggested staff
        if (r.suggested_staff) {
            document.getElementById('staffBox').classList.remove('d-none');
            document.getElementById('staffName').textContent = '👤 ' + r.suggested_staff.full_name;
            document.getElementById('staffLoad').textContent = `Đang xử lý: ${r.suggested_staff.open_tickets} ticket`;
        } else {
            document.getElementById('staffBox').classList.add('d-none');
        }

        // Matched keywords
        const mkEl = document.getElementById('matchedKeywords');
        if (r.matched_keywords && Object.keys(r.matched_keywords).length) {
            let html = '<div class="small fw-semibold text-muted mb-1">Từ khoá khớp:</div><div class="d-flex flex-wrap gap-1">';
            for (const [catId, kws] of Object.entries(r.matched_keywords)) {
                kws.forEach(k => {
                    const src = k.source === 'title' ? '🔤 tiêu đề' : '📝 mô tả';
                    html += `<span class="badge bg-primary-subtle text-primary border">
                        ${k.kw} <span class="opacity-75">(${src} +${k.pts}đ)</span>
                    </span>`;
                });
            }
            html += '</div>';
            mkEl.innerHTML = html;
        } else {
            mkEl.innerHTML = '';
        }

        // Score table
        const scores = r.scores.filter(s => s.score > 0);
        if (scores.length) {
            const maxScore = scores[0].score;
            let html = '<div class="d-flex flex-column gap-1">';
            scores.forEach((s, i) => {
                const pct  = maxScore > 0 ? Math.round(s.score / maxScore * 100) : 0;
                const isWin = i === 0 ? 'fw-bold' : '';
                const bar   = i === 0 ? 'bg-success' : 'bg-secondary';
                html += `<div>
                    <div class="d-flex justify-content-between small ${isWin}">
                        <span>${s.cat.name}</span><span>${s.score}đ</span>
                    </div>
                    <div class="progress" style="height:6px">
                        <div class="progress-bar ${bar}" style="width:${pct}%"></div>
                    </div>
                </div>`;
            });
            html += '</div>';
            document.getElementById('scoreTable').innerHTML = html;
        } else {
            document.getElementById('scoreTable').innerHTML = '<p class="text-muted small">Không có danh mục nào khớp.</p>';
        }
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-search me-2"></i>Phân tích & Route';
    }
});
</script>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
