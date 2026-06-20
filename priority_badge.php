<?php
$priorityMap = [
    'low'      => ['bg-secondary', 'Thấp'],
    'medium'   => ['bg-info text-dark', 'Trung bình'],
    'high'     => ['bg-warning text-dark', 'Cao'],
    'critical' => ['bg-danger', 'Khẩn cấp'],
];
$pr = $t['priority'] ?? 'medium';
[$cls, $lbl] = $priorityMap[$pr] ?? ['bg-secondary', $pr];
echo "<span class=\"badge {$cls}\">{$lbl}</span>";
?>
