<?php
declare(strict_types=1);

namespace App\Core;

final class Activity
{
    public static function log(string $action, ?int $userId = null): void
    {
        try {
            $stmt = Database::connection()->prepare('INSERT INTO activity_logs (user_id, action, ip_address, user_agent) VALUES (?, ?, ?, ?)');
            $stmt->execute([
                $userId ?? Auth::id(),
                $action,
                $_SERVER['REMOTE_ADDR'] ?? null,
                function_exists('mb_substr') ? mb_substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255) : substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255),
            ]);
        } catch (\Throwable $e) {
            // Logging must not break the main user flow.
        }
    }
}
