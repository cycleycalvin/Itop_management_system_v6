<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class TraineeProfile extends Model
{
    public function findByUser(int $userId): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM trainee_profiles WHERE user_id = ?');
        $stmt->execute([$userId]);
        return $stmt->fetch() ?: null;
    }

    public function save(int $userId, array $data): void
    {
        $stmt = $this->db->prepare('INSERT INTO trainee_profiles (user_id, identity_number, phone, address, education, employment, emergency_contact, profile_picture) VALUES (?, ?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE identity_number=VALUES(identity_number), phone=VALUES(phone), address=VALUES(address), education=VALUES(education), employment=VALUES(employment), emergency_contact=VALUES(emergency_contact), profile_picture=COALESCE(VALUES(profile_picture), profile_picture), updated_at=NOW()');
        $stmt->execute([$userId, $data['identity_number'], $data['phone'], $data['address'], $data['education'], $data['employment'], $data['emergency_contact'], $data['profile_picture']]);

        $userStmt = $this->db->prepare('UPDATE users SET phone = ?, address = ?, profile_picture = COALESCE(?, profile_picture), updated_at = NOW() WHERE id = ?');
        $userStmt->execute([$data['phone'], $data['address'], $data['profile_picture'], $userId]);
    }

    public function documents(int $userId): array
    {
        $stmt = $this->db->prepare('SELECT * FROM trainee_documents WHERE user_id = ? ORDER BY uploaded_at DESC');
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function addDocument(int $userId, string $type, string $fileName, string $filePath): void
    {
        $stmt = $this->db->prepare('INSERT INTO trainee_documents (user_id, document_type, file_name, file_path) VALUES (?, ?, ?, ?)');
        $stmt->execute([$userId, $type, $fileName, $filePath]);
    }

    public function adminList(string $search = ''): array
    {
        $like = '%' . $search . '%';
        $stmt = $this->db->prepare('SELECT u.id, u.name, u.email, u.phone, u.status, p.identity_number, p.education, p.employment, p.emergency_contact, p.updated_at, COUNT(d.document_id) AS document_count FROM users u JOIN roles r ON r.id = u.role_id AND r.slug = "trainee" LEFT JOIN trainee_profiles p ON p.user_id = u.id LEFT JOIN trainee_documents d ON d.user_id = u.id WHERE u.name LIKE ? OR u.email LIKE ? OR p.identity_number LIKE ? OR p.education LIKE ? GROUP BY u.id ORDER BY u.name');
        $stmt->execute([$like, $like, $like, $like]);
        return $stmt->fetchAll();
    }

    public function adminDetail(int $userId): ?array
    {
        $stmt = $this->db->prepare('SELECT u.*, p.* FROM users u LEFT JOIN trainee_profiles p ON p.user_id = u.id WHERE u.id = ?');
        $stmt->execute([$userId]);
        return $stmt->fetch() ?: null;
    }
}
