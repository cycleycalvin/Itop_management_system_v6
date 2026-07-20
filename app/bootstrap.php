<?php
declare(strict_types=1);

require __DIR__ . '/../config/config.php';

$composerAutoload = dirname(__DIR__) . '/vendor/autoload.php';
if (is_file($composerAutoload)) {
    require $composerAutoload;
}

spl_autoload_register(static function (string $class): void {
    $prefix = 'App\\';
    if (str_starts_with($class, $prefix)) {
        $path = __DIR__ . '/' . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
        if (is_file($path)) {
            require $path;
        }
    }
});

session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();

// Check for maintenance mode
try {
    $db = \App\Core\Model::getDb();
    $stmt = $db->prepare("SELECT setting_value FROM website_settings WHERE setting_key = 'maintenance_mode' LIMIT 1");
    $stmt->execute();
    $isMaintenance = ($stmt->fetchColumn() === '1');

    if ($isMaintenance) {
        $page = $_GET['page'] ?? 'home';
        $isAdmin = (\App\Core\Auth::role() === 'admin');
        $isAuthRoute = in_array($page, ['login', 'logout'], true);

        if (!$isAdmin && !$isAuthRoute) {
            http_response_code(503);
            include __DIR__ . '/Views/public/maintenance.php';
            exit;
        }
    }
} catch (\Throwable $e) {
    // Fail-safe: if table doesn't exist or DB not connected, continue
}
