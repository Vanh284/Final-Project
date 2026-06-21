<?php require_once __DIR__ . '/../layout/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0"><i class="bi bi-speedometer2 me-2 text-primary"></i>Dashboard</h4>
    <a href="<?= APP_URL ?>/index.php?page=tickets&action=create" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i>Tạo Ticket
    </a>
</div>

<!-- Stats cards -->
<div class="row g-3 mb-4">
    <?php
    $cards = [
        ['label'=>'Tổng tickets',     'value'=>$stats['total'],       'icon'=>'bi-ticket-perforated', 'color'=>'primary'],
        ['label'=>'Đang mở',          'value'=>$stats['open'],         'icon'=>'bi-circle',            'color'=>'warning'],
        ['label'=>'Đang xử lý',       'value'=>$stats['in_progress'],  'icon'=>'bi-arrow-repeat',      'color'=>'info'],
        ['label'=>'Đã giải quyết',    'value'=>$stats['resolved'],     'icon'=>'bi-check-circle',      'color'=>'success'],
        ['label'=>'Quá hạn SLA',      'value'=>$stats['overdue'],      'icon'=>'bi-alarm',             'color'=>'danger'],
        ['label'=>'Đã escalate',      'value'=>$stats['escalated'],    'icon'=>'bi-exclamation-triangle','color'=>'dark'],
    ];
    foreach ($cards as $c):
    ?>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center py-3">
                <div class="text-<?= $c['color'] ?> mb-1">
                    <i class="bi <?= $c['icon'] ?> fs-3"></i>
                </div>
                <div class="fs-3 fw-bold"><?= $c['value'] ?></div>
                <div class="text-muted small"><?= $c['label'] ?></div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<div class="row g-4 mb-4">
    <!-- Chart by department -->
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white fw-semibold border-0 pt-3">
                <i class="bi bi-bar-chart me-2 text-primary"></i>Tickets theo bộ phận
            </div>
            <div class="card-body">
                <?php if (!empty($byDept)): ?>
                <?php
                $maxVal = max(array_map(fn($d) => (int)$d['total'], $byDept)) ?: 1;
                $colors = ['primary','warning','success','info','danger','secondary'];
                ?>
                <div class="d-flex flex-column gap-3 py-2">
                <?php foreach ($byDept as $i => $d):
                    $open     = (int)$d['open_count'];
                    $resolved = (int)$d['resolved_count'];
                    $total    = (int)$d['total'];
                    $openPct  = $total > 0 ? round($open    / $maxVal * 100) : 0;
                    $resPct   = $total > 0 ? round($resolved / $maxVal * 100) : 0;
                ?>
                <div>
                    <div class="d-flex justify-content-between mb-1 small fw-semibold">
                        <span><?= htmlspecialchars($d['department_name']) ?></span>
                        <span class="text-muted"><?= $total ?> tickets</span>
                    </div>
                    <div class="d-flex gap-1" style="height:22px">
                        <?php if ($openPct > 0): ?>
                        <div class="bg-warning rounded-start d-flex align-items-center justify-content-center text-dark"
                             style="width:<?= $openPct ?>%;font-size:11px;min-width:<?= $open>0?'28px':'0' ?>">
                            <?= $open > 0 ? $open : '' ?>
                        </div>
                        <?php endif; ?>
                        <?php if ($resPct > 0): ?>
                        <div class="bg-success <?= $openPct == 0 ? 'rounded-start' : '' ?> rounded-end d-flex align-items-center justify-content-center text-white"
                             style="width:<?= $resPct ?>%;font-size:11px;min-width:<?= $resolved>0?'28px':'0' ?>">
                            <?= $resolved > 0 ? $resolved : '' ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
                </div>
                <div class="d-flex gap-3 mt-3 small text-muted">
                    <span><span class="badge bg-warning text-dark me-1">■</span>Đang mở/xử lý</span>
                    <span><span class="badge bg-success me-1">■</span>Đã giải quyết</span>
                </div>
                <?php else: ?>
                <p class="text-muted text-center py-4">Chưa có dữ liệu</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <!-- Satisfaction rating -->
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white fw-semibold border-0 pt-3">
                <i class="bi bi-star me-2 text-warning"></i>Đánh giá hài lòng
            </div>
            <div class="card-body text-center">
                <div class="display-4 fw-bold text-warning mb-2">
                    <?= $avgRating ?> <small class="fs-5 text-muted">/ 5</small>
                </div>
                <div class="mb-3">
                    <?php for ($s = 5; $s >= 1; $s--): ?>
                    <?php $cnt = 0; foreach ($ratingDist as $r) { if ($r['rating'] == $s) $cnt = $r['count']; } ?>
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <span class="small text-muted" style="width:40px"><?= $s ?> <i class="bi bi-star-fill text-warning"></i></span>
                        <div class="progress flex-grow-1" style="height:8px">
                            <?php $total = array_sum(array_column($ratingDist, 'count')) ?: 1; ?>
                            <div class="progress-bar bg-warning" style="width:<?= round($cnt/$total*100) ?>%"></div>
                        </div>
                        <span class="small text-muted" style="width:25px"><?= $cnt ?></span>
                    </div>
                    <?php endfor; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent tickets -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white fw-semibold border-0 pt-3 d-flex justify-content-between">
        <span><i class="bi bi-clock-history me-2 text-primary"></i>Tickets gần đây</span>
        <a href="<?= APP_URL ?>/index.php?page=tickets" class="btn btn-sm btn-outline-primary">Xem tất cả</a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Mã</th><th>Tiêu đề</th><th>Danh mục</th>
                        <th>Ưu tiên</th><th>Trạng thái</th><th>Ngày tạo</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($recent as $t): ?>
                <tr class="cursor-pointer" onclick="window.location='<?= APP_URL ?>/index.php?page=tickets&action=show&id=<?= $t['id'] ?>'">
                    <td><code><?= $t['ticket_code'] ?></code></td>
                    <td><?= htmlspecialchars($t['title']) ?></td>
                    <td><?= htmlspecialchars($t['category_name']) ?></td>
                    <td><?php include __DIR__ . '/../partials/priority_badge.php'; ?></td>
                    <td><?php include __DIR__ . '/../partials/status_badge.php'; ?></td>
                    <td><?= date('d/m/Y', strtotime($t['created_at'])) ?></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($recent)): ?>
                <tr><td colspan="6" class="text-center text-muted py-4">Chưa có ticket nào</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
// Chart.js bar chart (fallback nếu CSS chart không đủ)
document.addEventListener('DOMContentLoaded', function() {
    const canvas = document.getElementById('deptChartJs');
    if (!canvas || typeof Chart === 'undefined') return;
    const deptData = <?= json_encode($byDept, JSON_UNESCAPED_UNICODE) ?>;
    new Chart(canvas, {
        type: 'bar',
        data: {
            labels: deptData.map(d => d.department_name),
            datasets: [
                { label: 'Đang mở/xử lý', data: deptData.map(d => parseInt(d.open_count)||0),  backgroundColor: 'rgba(255,193,7,0.85)' },
                { label: 'Đã giải quyết', data: deptData.map(d => parseInt(d.resolved_count)||0), backgroundColor: 'rgba(25,135,84,0.85)' }
            ]
        },
        options: {
            responsive: true,
            plugins: { legend: { position: 'bottom' } },
            scales: { x: { stacked: true }, y: { stacked: true, beginAtZero: true, ticks: { stepSize: 1 } } }
        }
    });
});
</script>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
