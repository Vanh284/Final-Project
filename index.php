<?php
// Redirect từ /helpdesk/ về /helpdesk/public/
$publicUrl = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\') . '/public/';
header('Location: ' . $publicUrl);
exit;
