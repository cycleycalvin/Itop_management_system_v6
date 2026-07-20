<?php
declare(strict_types=1);

define('APP_NAME', 'CENTEXS ITOP Management System');

// Dynamically detect base URL
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || ($_SERVER['SERVER_PORT'] ?? '') == 443) ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
$dir = ($dir === '/' || $dir === '\\') ? '' : rtrim($dir, '/');
define('APP_URL', $protocol . $host . $dir);
define('UPLOAD_PATH', dirname(__DIR__) . '/storage/uploads');
define('SUBMISSION_PATH', dirname(__DIR__) . '/storage/submissions');
define('CERTIFICATE_PATH', dirname(__DIR__) . '/storage/certificates');
define('MAX_UPLOAD_BYTES', 25 * 1024 * 1024);

define('DB_HOST', 'localhost');
define('DB_NAME', 'centexs_itop_ims');
define('DB_USER', 'root');
define('DB_PASS', '');

date_default_timezone_set('Asia/Kuala_Lumpur');

