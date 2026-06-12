<?php
/**
 * Helper: trả về base URL của ứng dụng
 * Tự động tính từ SCRIPT_NAME, không cần hardcode
 * Ví dụ: /ins3064/Finalproject/helpdesk/public
 */
function base_url(string $path = ''): string {
    // BASE_URL được define trong index.php
    $base = defined('BASE_URL') ? BASE_URL : ($_SESSION['base_url'] ?? '');
    return $base . ($path ? '/' . ltrim($path, '/') : '');
}

function url(string $page, array $params = []): string {
    $query = array_merge(['page' => $page], $params);
    return base_url('?') . http_build_query($query);
}
