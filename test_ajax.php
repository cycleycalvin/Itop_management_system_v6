<?php
declare(strict_types=1);
require __DIR__ . '/app/bootstrap.php';

// Mock session login as admin
$_SESSION['user'] = [
    'id' => 1,
    'name' => 'CENTEXS Administrator',
    'email' => 'admin@centexs.local',
    'role_slug' => 'admin',
];

ob_start();
$_GET['id'] = '3'; // Daniel Ling is ID 3 usually
$controller = new \App\Controllers\AdminController();
$controller->participantDetail();
$response = ob_get_clean();

file_put_contents(__DIR__ . '/ajax_response.json', $response);
echo "RESPONSE_WRITTEN";
