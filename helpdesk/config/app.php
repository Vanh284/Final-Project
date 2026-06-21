<?php
define('APP_NAME',    'Campus Helpdesk');
// Auto-detect APP_URL from server environment
if (!defined('APP_URL')) {
    $scheme   = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host     = $_SERVER['HTTP_HOST'] ?? 'localhost';
    // Derive base path from current script location relative to document root
    $docRoot  = rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT'] ?? ''), '/');
    $selfDir  = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_FILENAME'] ?? __FILE__)), '/');
    $basePath = str_replace($docRoot, '', $selfDir);
    define('APP_URL', $scheme . '://' . $host . $basePath);
}
define('APP_VERSION', '1.0.0');

// Session lifetime (seconds)
define('SESSION_LIFETIME', 3600);

// Pagination
define('ITEMS_PER_PAGE', 10);

// Upload
define('UPLOAD_DIR',      __DIR__ . '/../public/uploads/');
define('UPLOAD_MAX_SIZE', 5 * 1024 * 1024); // 5 MB
define('UPLOAD_ALLOWED',  ['jpg','jpeg','png','gif','pdf','doc','docx','zip']);

// Timezone
date_default_timezone_set('Asia/Ho_Chi_Minh');

// Error reporting (set to 0 in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);
