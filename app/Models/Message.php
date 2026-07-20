<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Message extends Model
{
    public function conversations(int $userId, string $search = ''): array
    {
        $like = '%' . $search . '%';
        $stmt = $this->db->prepare('SELECT c.*, MAX(m.created_at) AS last_message_at, SUM(m.sender_id <> ? AND m.read_at IS NULL AND m.deleted_at IS NULL) AS unread_count, GROUP_CONCAT(DISTINCT u.name ORDER BY u.name SEPARATOR ", ") AS participants FROM conversations c JOIN conversation_participants cp ON cp.conversation_id = c.id AND cp.user_id = ? AND cp.deleted_at IS NULL JOIN conversation_participants allp ON allp.conversation_id = c.id JOIN users u ON u.id = allp.user_id LEFT JOIN messages m ON m.conversation_id = c.id AND m.deleted_at IS NULL WHERE c.subject LIKE ? OR u.name LIKE ? GROUP BY c.id ORDER BY COALESCE(MAX(m.created_at), c.created_at) DESC');
        $stmt->execute([$userId, $userId, $like, $like]);
        return $stmt->fetchAll();
    }

    public function messages(int $conversationId, int $userId): array
    {
        $check = $this->db->prepare('SELECT COUNT(*) FROM conversation_participants WHERE conversation_id = ? AND user_id = ? AND deleted_at IS NULL');
        $check->execute([$conversationId, $userId]);
        if (!$check->fetchColumn()) {
            return [];
        }

        $read = $this->db->prepare('UPDATE messages SET read_at = COALESCE(read_at, NOW()) WHERE conversation_id = ? AND sender_id <> ?');
        $read->execute([$conversationId, $userId]);
        $stmt = $this->db->prepare('SELECT m.*, u.name AS sender_name, u.profile_picture FROM messages m JOIN users u ON u.id = m.sender_id WHERE m.conversation_id = ? AND m.deleted_at IS NULL ORDER BY m.created_at ASC');
        $stmt->execute([$conversationId]);
        return $stmt->fetchAll();
    }

    public function contacts(int $userId): array
    {
        $stmt = $this->db->prepare('SELECT users.id, users.name, users.email, roles.slug AS role_slug FROM users JOIN roles ON roles.id = users.role_id WHERE users.id <> ? AND users.status = "active" ORDER BY roles.id, users.name');
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function unreadCount(int $userId): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM messages m JOIN conversation_participants cp ON cp.conversation_id = m.conversation_id AND cp.user_id = ? WHERE m.sender_id <> ? AND m.read_at IS NULL AND m.deleted_at IS NULL AND cp.deleted_at IS NULL');
        $stmt->execute([$userId, $userId]);
        return (int) $stmt->fetchColumn();
    }

    public function start(int $senderId, int $receiverId, string $subject, string $body, ?string $attachment = null): int
    {
        $this->db->beginTransaction();
        $stmt = $this->db->prepare('INSERT INTO conversations (subject, created_by) VALUES (?, ?)');
        $stmt->execute([$subject ?: 'Conversation', $senderId]);
        $conversationId = (int) $this->db->lastInsertId();
        $participant = $this->db->prepare('INSERT INTO conversation_participants (conversation_id, user_id) VALUES (?, ?)');
        $participant->execute([$conversationId, $senderId]);
        $participant->execute([$conversationId, $receiverId]);
        $message = $this->db->prepare('INSERT INTO messages (conversation_id, sender_id, body, attachment_path) VALUES (?, ?, ?, ?)');
        $message->execute([$conversationId, $senderId, $body, $attachment]);
        $this->db->commit();
        return $conversationId;
    }

    public function reply(int $conversationId, int $senderId, string $body, ?string $attachment = null): void
    {
        $stmt = $this->db->prepare('INSERT INTO messages (conversation_id, sender_id, body, attachment_path) SELECT ?, ?, ?, ? WHERE EXISTS (SELECT 1 FROM conversation_participants WHERE conversation_id = ? AND user_id = ? AND deleted_at IS NULL)');
        $stmt->execute([$conversationId, $senderId, $body, $attachment, $conversationId, $senderId]);
    }

    public function deleteMessage(int $messageId, int $senderId): void
    {
        $stmt = $this->db->prepare('UPDATE messages SET deleted_at = NOW() WHERE id = ? AND sender_id = ?');
        $stmt->execute([$messageId, $senderId]);
    }
}
