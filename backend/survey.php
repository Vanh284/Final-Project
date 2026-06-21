<?php require_once __DIR__ . '/../layout/header.php'; ?>

<div class="d-flex align-items-center gap-2 mb-4">
    <h4 class="fw-bold mb-0">
        <i class="bi bi-star-half me-2 text-warning"></i>Báo cáo Satisfaction Survey
    </h4>
    <span class="badge bg-warning-subtle text-warning border border-warning-subtle ms-2">Backend Focus #3</span>
</div>

<!-- Overall stats row -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm text-center py-3">
            <div class="text-warning mb-1"><i class="bi bi-star-fill fs-3"></i></div>
            <div class="display-6 fw-bold"><?= $overall['avg_rating'] ?? '–' ?></div>
            <div class="small text-muted">Điểm TB / 5</div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm text-center py-3">
            <div class="text-primary mb-1"><i class="bi bi-chat-square-heart fs-3"></i></div>
            <div class="display-6 fw-bold"><?= $overall['total_surveys'] ?? 0 ?></div>
            <div class="small text-muted">Tổng đánh giá</div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm text-center py-3">
            <div class="text-success mb-1"><i class="bi bi-hand-thumbs-up fs-3"></i></div>
            <div class="display-6 fw-bold text-success"><?= $overall['satisfaction_rate'] ?? 0 ?>%</div>
            <div class="small text-muted">Tỉ lệ hài lòng (≥4★)</div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm text-center py-3">
            <div class="text-danger mb-1"><i class="bi bi-hand-thumbs-down fs-3"></i></div>
            <div class="display-6 fw-bold text-danger"><?= $overall['dissatisfied'] ?? 0 ?></div>
            <div class="small text-muted">Không hài lòng (≤2★)</div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
<!-- Rating distribution + trend -->
<div class="col-lg-5">
    <div class="card border-0 shadow-sm h-100">
        <div class="card-header bg-white fw-semibold pt-3 border-0">
            <i class="bi bi-bar-chart me-2 text-warning"></i>Phân bố đánh giá
        </div>
        <div class="card-body">
            <?php
            $totalS = (int)($overall['total_surveys'] ?? 0) ?: 1;
            $stars  = [5=>'five_star',4=>'four_star',3=>'three_star',2=>'two_star',1=>'one_star'];
            foreach (array_reverse($stars, true) as $num => $key):
                $cnt = (int)($overall[$key] ?? 0);
                $pct = round($cnt / $totalS * 100);
            ?>
            <div class="d-flex align-items-center gap-2 mb-2">
                <div class="text-warning" style="width:70px;font-size:13px">
                    <?= str_repeat('★', $num) ?><?= str_repeat('☆', 5-$num) ?>
                </div>
                <div class="progress flex-grow-1" style="height:14px">
                    <div class="progress-bar <?= $num >= 4 ? 'bg-success' : ($num == 3 ? 'bg-warning' : 'bg-danger') ?>"
                         style="width:<?= $pct ?>%"></div>
                </div>
                <div class="small text-muted" style="width:55px"><?= $cnt ?> (<?= $pct ?>%)</div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Monthly trend: CSS bar chart -->
<div class="col-lg-7">
    <div class="card border-0 shadow-sm h-100">
        <div class="card-header bg-white fw-semibold pt-3 border-0">
            <i class="bi bi-graph-up me-2 text-primary"></i>Xu hướng 6 tháng gần nhất
        </div>
        <div class="card-body">
            <?php if (!empty($trend)): ?>
            <?php $maxT = max(array_column($trend, 'total')) ?: 1; ?>
            <div class="d-flex align-items-end gap-2 mb-2" style="height:140px">
                <?php foreach ($trend as $tr):
                    $h = round((int)$tr['total'] / $maxT * 120);
                    $starH = round((float)$tr['avg_rating'] / 5 * 120);
                ?>
                <div class="d-flex flex-column align-items-center flex-fill gap-1">
                    <span class="small text-warning fw-bold"><?= $tr['avg_rating'] ?>★</span>
                    <div class="w-100 d-flex align-items-end justify-content-center gap-1" style="height:100px">
                        <div class="bg-primary rounded-top" style="width:40%;height:<?= max($h,4) ?>px" title="<?= $tr['total'] ?> đánh giá"></div>
                        <div class="bg-warning rounded-top" style="width:40%;height:<?= max($starH,4) ?>px" title="TB <?= $tr['avg_rating'] ?>★"></div>
                    </div>
                    <div class="small text-muted" style="font-size:10px"><?= substr($tr['month'],5) ?>/<?= substr($tr['month'],2,2) ?></div>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="d-flex gap-3 small text-muted mt-1">
                <span><span class="badge bg-primary me-1">■</span>Số đánh giá</span>
                <span><span class="badge bg-warning text-dark me-1">■</span>Điểm TB</span>
            </div>
            <?php else: ?>
            <p class="text-muted text-center py-4">Chưa đủ dữ liệu</p>
            <?php endif; ?>
        </div>
    </div>
</div>
</div>

<div class="row g-4 mb-4">
<!-- By department -->
<div class="col-lg-6">
    <div class="card border-0 shadow-sm h-100">
        <div class="card-header bg-white fw-semibold pt-3 border-0">
            <i class="bi bi-building me-2 text-primary"></i>Đánh giá theo Bộ phận
        </div>
        <div class="card-body p-0">
            <table class="table table-hover align-middle mb-0 small">
                <thead class="table-light">
                    <tr><th>Bộ phận</th><th>Đánh giá</th><th>Điểm TB</th><th>Hài lòng</th></tr>
                </thead>
                <tbody>
                <?php foreach ($byDept as $d): ?>
                <?php $satisfiedPct = $d['total'] > 0 ? round($d['satisfied']/$d['total']*100) : 0; ?>
                <tr>
                    <td class="fw-semibold"><?= htmlspecialchars($d['department_name']) ?></td>
                    <td><?= $d['total'] ?></td>
                    <td>
                        <span class="text-warning fw-bold"><?= $d['avg_rating'] ?></span>
                        <span class="text-muted">/ 5</span>
                    </td>
                    <td>
                        <div class="d-flex align-items-center gap-1">
                            <div class="progress flex-grow-1" style="height:6px">
                                <div class="progress-bar bg-success" style="width:<?= $satisfiedPct ?>%"></div>
                            </div>
                            <small><?= $satisfiedPct ?>%</small>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($byDept)): ?>
                <tr><td colspan="4" class="text-center text-muted py-3">Chưa có dữ liệu</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Staff performance -->
<div class="col-lg-6">
    <div class="card border-0 shadow-sm h-100">
        <div class="card-header bg-white fw-semibold pt-3 border-0">
            <i class="bi bi-person-badge me-2 text-info"></i>Hiệu suất Nhân viên
        </div>
        <div class="card-body p-0">
            <table class="table table-hover align-middle mb-0 small">
                <thead class="table-light">
                    <tr><th>Nhân viên</th><th>Đánh giá</th><th>Điểm TB</th><th>TB xử lý</th></tr>
                </thead>
                <tbody>
                <?php foreach ($staffPerf as $sp): ?>
                <tr>
                    <td>
                        <div class="fw-semibold"><?= htmlspecialchars($sp['full_name']) ?></div>
                        <div class="text-muted small"><?= htmlspecialchars($sp['department_name']) ?></div>
                    </td>
                    <td><?= $sp['total_surveys'] ?: '–' ?></td>
                    <td>
                        <?php if ($sp['avg_rating']): ?>
                        <span class="text-warning fw-bold"><?= $sp['avg_rating'] ?></span>
                        <?php else: ?><span class="text-muted">–</span><?php endif; ?>
                    </td>
                    <td class="text-muted">
                        <?php if ($sp['avg_resolve_minutes']): ?>
                        <?= $sp['avg_resolve_minutes'] >= 60
                            ? round($sp['avg_resolve_minutes']/60, 1).'h'
                            : $sp['avg_resolve_minutes'].'m' ?>
                        <?php else: ?>–<?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($staffPerf)): ?>
                <tr><td colspan="4" class="text-center text-muted py-3">Chưa có dữ liệu</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</div>

<!-- Low-rated alerts -->
<?php if (!empty($lowRated)): ?>
<div class="card border-0 shadow-sm mb-4 border-start border-4 border-danger">
    <div class="card-header bg-white fw-semibold pt-3 border-0">
        <i class="bi bi-exclamation-octagon me-2 text-danger"></i>Đánh giá thấp cần xử lý (≤ 2★)
    </div>
    <div class="card-body p-0">
        <table class="table table-hover align-middle mb-0 small">
            <thead class="table-light">
                <tr><th>Ticket</th><th>Đánh giá</th><th>Bộ phận</th><th>Nhận xét</th><th>Người dùng</th></tr>
            </thead>
            <tbody>
            <?php foreach ($lowRated as $lr): ?>
            <tr>
                <td>
                    <a href="<?= APP_URL ?>/index.php?page=tickets&action=show&id=<?= $lr['ticket_id'] ?>"
                       class="text-decoration-none fw-semibold">
                        <?= htmlspecialchars($lr['ticket_code']) ?>
                    </a>
                    <div class="text-muted small"><?= mb_strimwidth(htmlspecialchars($lr['title']), 0, 35, '…') ?></div>
                </td>
                <td>
                    <span class="text-danger fw-bold fs-5"><?= str_repeat('★', $lr['rating']) ?><?= str_repeat('☆', 5-$lr['rating']) ?></span>
                </td>
                <td><?= htmlspecialchars($lr['department_name']) ?></td>
                <td class="text-muted fst-italic"><?= $lr['comment'] ? '"'.htmlspecialchars($lr['comment']).'"' : '–' ?></td>
                <td><?= htmlspecialchars($lr['submitter_name']) ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- All surveys table -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white fw-semibold pt-3 border-0 d-flex justify-content-between">
        <span><i class="bi bi-list-ul me-2 text-secondary"></i>Tất cả đánh giá (<?= $totalSurveys ?>)</span>
    </div>
    <div class="card-body p-0">
        <table class="table table-hover align-middle mb-0 small">
            <thead class="table-light">
                <tr><th>Ticket</th><th>Đánh giá</th><th>Bộ phận</th><th>Nhận xét</th><th>Người dùng</th><th>Ngày</th></tr>
            </thead>
            <tbody>
            <?php foreach ($surveys as $sv): ?>
            <tr>
                <td>
                    <a href="<?= APP_URL ?>/index.php?page=tickets&action=show&id=<?= $sv['ticket_id'] ?? '' ?>"
                       class="text-decoration-none fw-semibold">
                        <?= htmlspecialchars($sv['ticket_code']) ?>
                    </a>
                    <div class="text-muted"><?= mb_strimwidth(htmlspecialchars($sv['title']), 0, 30, '…') ?></div>
                </td>
                <td>
                    <?php $starClass = $sv['rating'] >= 4 ? 'text-success' : ($sv['rating'] == 3 ? 'text-warning' : 'text-danger'); ?>
                    <span class="<?= $starClass ?> fw-bold"><?= str_repeat('★', $sv['rating']) ?><?= str_repeat('☆', 5-$sv['rating']) ?></span>
                    <span class="text-muted">(<?= $sv['rating'] ?>)</span>
                </td>
                <td><?= htmlspecialchars($sv['department_name']) ?></td>
                <td class="text-muted fst-italic" style="max-width:200px">
                    <?= $sv['comment'] ? mb_strimwidth(htmlspecialchars($sv['comment']), 0, 60, '…') : '–' ?>
                </td>
                <td><?= htmlspecialchars($sv['submitter_name']) ?></td>
                <td class="text-muted"><?= date('d/m/Y', strtotime($sv['submitted_at'])) ?></td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($surveys)): ?>
            <tr><td colspan="6" class="text-center text-muted py-4">Chưa có đánh giá nào</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php if ($pages > 1): ?>
    <div class="card-footer bg-white">
        <?php include __DIR__ . '/../partials/pagination.php'; ?>
    </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
