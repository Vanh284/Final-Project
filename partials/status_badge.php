<?php
$statusMap = [
    'open'        => ['bg-primary',         'Mở'],
    'in_progress' => ['bg-info text-dark',  'Đang xử lý'],
    'pending'     => ['bg-warning text-dark','Chờ phản hồi'],
    'resolved'    => ['bg-success',         'Đã giải quyết'],
    'closed'      => ['bg-secondary',       'Đã đóng'],
    'cancelled'   => ['bg-dark',            'Đã huỷ'],
];
$st = $t['status'] ?? 'open';
[$cls, $lbl] = $statusMap[$st] ?? ['bg-secondary', $st];
echo "<span class=\"badge {$cls}\">{$lbl}</span>";
?>
