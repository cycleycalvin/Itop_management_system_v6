<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Notification extends Model
{
    public function recent(int $userId, string $search = '', string $type = ''): array
    {
        $like = '%' . $search . '%';
        $sql = 'SELECT n.*, sender.name AS sender_name FROM notifications n LEFT JOIN users sender ON sender.id = n.sender_id WHERE n.user_id = ? AND n.deleted_at IS NULL AND (n.title LIKE ? OR n.description LIKE ?)';
        $params = [$userId, $like, $like];
        if ($type !== '') {
            $sql .= ' AND n.notification_type = ?';
            $params[] = $type;
        }
        $sql .= ' ORDER BY n.created_at DESC LIMIT 50';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function unreadCount(int $userId): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM notifications WHERE user_id = ? AND read_at IS NULL AND deleted_at IS NULL');
        $stmt->execute([$userId]);
        return (int) $stmt->fetchColumn();
    }

    public function types(): array
    {
        return $this->db->query('SELECT DISTINCT notification_type FROM notifications ORDER BY notification_type')->fetchAll();
    }

    public function create(int $userId, ?int $senderId, string $type, string $title, string $description = '', string $url = ''): void
    {
        $stmt = $this->db->prepare('INSERT INTO notifications (user_id, sender_id, notification_type, title, description, related_url) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->execute([$userId, $senderId, $type, $title, $description, $url]);
    }

    public function markRead(int $userId, int $id): void
    {
        $stmt = $this->db->prepare('UPDATE notifications SET read_at = NOW() WHERE id = ? AND user_id = ?');
        $stmt->execute([$id, $userId]);
    }

    public function markAllRead(int $userId): void
    {
        $stmt = $this->db->prepare('UPDATE notifications SET read_at = NOW() WHERE user_id = ? AND read_at IS NULL');
        $stmt->execute([$userId]);
    }

    public function delete(int $userId, int $id): void
    {
        $stmt = $this->db->prepare('UPDATE notifications SET deleted_at = NOW() WHERE id = ? AND user_id = ?');
        $stmt->execute([$id, $userId]);
    }
}
