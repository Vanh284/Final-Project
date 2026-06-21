<?php require_once __DIR__ . '/../layout/header.php'; ?>

<h4 class="fw-bold mb-4"><i class="bi bi-bar-chart me-2 text-primary"></i>Báo cáo & Thống kê</h4>

<!-- Summary numbers -->
<div class="row g-3 mb-4">
    <?php
    $cards = [
        ['Tổng tickets',    $stats['total'],       'bi-ticket-perforated', 'primary'],
        ['Đang mở',         $stats['open'],          'bi-circle',            'warning'],
        ['Đang xử lý',      $stats['in_progress'],   'bi-arrow-repeat',      'info'],
        ['Đã giải quyết',   $stats['resolved'],      'bi-check-circle',      'success'],
        ['Đã đóng',         $stats['closed'],         'bi-lock',              'secondary'],
        ['Quá hạn SLA',     $stats['overdue'],        'bi-alarm',             'danger'],
    ];
    foreach ($cards as [$label, $value, $icon, $color]):
    ?>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card border-0 shadow-sm text-center py-3">
            <div class="text-<?= $color ?> mb-1"><i class="bi <?= $icon ?> fs-3"></i></div>
            <div class="fs-3 fw-bold"><?= $value ?></div>
            <div class="small text-muted"><?= $label ?></div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<div class="row g-4 mb-4">
    <!-- Monthly trend: CSS bar chart -->
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold border-0 pt-3">
                <i class="bi bi-graph-up me-2 text-primary"></i>Xu hướng theo tháng (6 tháng gần nhất)
            </div>
            <div class="card-body">
                <?php if (!empty($byMonth)): ?>
                <?php $maxMonth = max(array_column($byMonth, 'total')) ?: 1; ?>
                <div class="d-flex align-items-end gap-2" style="height:160px">
                    <?php foreach ($byMonth as $m):
                        $h = round((int)$m['total'] / $maxMonth * 140);
                    ?>
                    <div class="d-flex flex-column align-items-center flex-fill">
                        <span class="small fw-bold text-primary mb-1"><?= $m['total'] ?></span>
                        <div class="bg-primary rounded-top w-100"
                             style="height:<?= max($h,4) ?>px;transition:height .3s"></div>
                        <div class="small text-muted mt-1" style="font-size:10px">
                            <?= substr($m['month'], 5) ?>/<?= substr($m['month'], 2, 2) ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <p class="text-muted text-center py-4">Chưa đủ dữ liệu (cần tickets từ tháng trước)</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Department: CSS donut-style -->
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold border-0 pt-3">
                <i class="bi bi-building me-2 text-primary"></i>Theo bộ phận
            </div>
            <div class="card-body">
                <?php
                $deptColors = ['#0d6efd','#ffc107','#198754','#dc3545','#6610f2','#0dcaf0'];
                $grandTotal = array_sum(array_column($byDept, 'total')) ?: 1;
                ?>
                <?php foreach ($byDept as $i => $d):
                    $pct = round((int)$d['total'] / $grandTotal * 100);
                    $col = $deptColors[$i % count($deptColors)];
                ?>
                <div class="mb-3">
                    <div class="d-flex justify-content-between small mb-1">
                        <span class="fw-semibold"><?= htmlspecialchars($d['department_name']) ?></span>
                        <span class="text-muted"><?= $d['total'] ?> tickets (<?= $pct ?>%)</span>
                    </div>
                    <div class="progress" style="height:20px;border-radius:6px">
                        <div class="progress-bar" role="progressbar"
                             style="width:<?= $pct ?>%;background-color:<?= $col ?>;font-size:12px">
                            <?= $pct >= 15 ? $pct.'%' : '' ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<!-- Department table -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white fw-semibold border-0 pt-3">
        <i class="bi bi-table me-2 text-primary"></i>Chi tiết theo bộ phận
    </div>
    <div class="card-body p-0">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr><th>Bộ phận</th><th>Tổng</th><th>Đang xử lý</th><th>Đã hoàn thành</th><th>Tỉ lệ hoàn thành</th></tr>
            </thead>
            <tbody>
            <?php foreach ($byDept as $d): ?>
            <?php $rate = $d['total'] > 0 ? round($d['resolved_count']/$d['total']*100) : 0; ?>
            <tr>
                <td class="fw-semibold"><?= htmlspecialchars($d['department_name']) ?></td>
                <td><?= $d['total'] ?></td>
                <td><?= $d['open_count'] ?></td>
                <td><?= $d['resolved_count'] ?></td>
                <td style="width:200px">
                    <div class="d-flex align-items-center gap-2">
                        <div class="progress flex-grow-1" style="height:8px">
                            <div class="progress-bar bg-success" style="width:<?= $rate ?>%"></div>
                        </div>
                        <span class="small text-muted"><?= $rate ?>%</span>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Satisfaction -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white fw-semibold border-0 pt-3">
        <i class="bi bi-star me-2 text-warning"></i>Điểm hài lòng trung bình: <strong class="text-warning"><?= $avgRating ?>/5</strong>
    </div>
    <div class="card-body">
        <?php for ($s = 5; $s >= 1; $s--): ?>
        <?php $cnt = 0; foreach ($ratingDist as $r) { if ($r['rating'] == $s) $cnt = $r['count']; } ?>
        <?php $total = array_sum(array_column($ratingDist, 'count')) ?: 1; ?>
        <div class="d-flex align-items-center gap-3 mb-2">
            <span style="width:70px" class="small"><?= $s ?> <i class="bi bi-star-fill text-warning"></i></span>
            <div class="progress flex-grow-1" style="height:12px">
                <div class="progress-bar bg-warning" style="width:<?= round($cnt/$total*100) ?>%"></div>
            </div>
            <span class="small text-muted" style="width:60px"><?= $cnt ?> đánh giá</span>
        </div>
        <?php endfor; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
