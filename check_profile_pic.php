<?php
require __DIR__ . '/app/bootstrap.php';
$db = \App\Core\Model::getDb();
$users = $db->query('SELECT id, name, email, profile_picture FROM users')->fetchAll();
$out = "";
foreach ($users as $u) {
    $out .= "ID: {$u['id']} | Name: {$u['name']} | Email: {$u['email']} | Profile Pic: '" . ($u['profile_picture'] ?? 'NULL') . "'\n";
}
file_put_contents(__DIR__ . '/check_profile_pic.txt', $out);
echo "Done!";
