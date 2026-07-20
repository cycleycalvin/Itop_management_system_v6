<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class User extends Model
{
    private const ACTIVE_STATUSES = ['pending', 'active', 'inactive', 'suspended'];

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare('SELECT users.*, roles.slug AS role_slug FROM users JOIN roles ON roles.id = users.role_id WHERE users.email = ? LIMIT 1');
        $stmt->execute([$email]);
        return $stmt->fetch() ?: null;
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare('INSERT INTO users (role_id, name, email, password_hash, phone, address, status) VALUES ((SELECT id FROM roles WHERE slug = ?), ?, ?, ?, ?, ?, ?)');
        $stmt->execute([
            $data['role_slug'],
            $data['name'],
            $data['email'],
            password_hash($data['password'], PASSWORD_DEFAULT),
            $data['phone'] ?? null,
            $data['address'] ?? null,
            $data['status'] ?? 'pending',
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function all(string $search = '', int $limit = 20, int $offset = 0, string $role = '', string $status = ''): array
    {
        $like = '%' . $search . '%';
        $sql = 'SELECT users.*, roles.name AS role_name, roles.slug AS role_slug FROM users JOIN roles ON roles.id = users.role_id WHERE (users.name LIKE ? OR users.email LIKE ? OR users.phone LIKE ?)';
        $params = [$like, $like, $like];
        if ($role !== '') {
            $sql .= ' AND roles.slug = ?';
            $params[] = $role;
        }
        if ($status !== '') {
            $sql .= ' AND users.status = ?';
            $params[] = $status;
        }
        $sql .= ' ORDER BY users.created_at DESC LIMIT ? OFFSET ?';
        $stmt = $this->db->prepare($sql);
        foreach ($params as $index => $value) {
            $stmt->bindValue($index + 1, $value);
        }
        $stmt->bindValue(count($params) + 1, $limit, \PDO::PARAM_INT);
        $stmt->bindValue(count($params) + 2, $offset, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function countAll(string $search = '', string $role = '', string $status = ''): int
    {
        $like = '%' . $search . '%';
        $sql = 'SELECT COUNT(*) FROM users JOIN roles ON roles.id = users.role_id WHERE (users.name LIKE ? OR users.email LIKE ? OR users.phone LIKE ?)';
        $params = [$like, $like, $like];
        if ($role !== '') {
            $sql .= ' AND roles.slug = ?';
            $params[] = $role;
        }
        if ($status !== '') {
            $sql .= ' AND users.status = ?';
            $params[] = $status;
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT users.*, roles.name AS role_name, roles.slug AS role_slug FROM users JOIN roles ON roles.id = users.role_id WHERE users.id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function saveFromAdmin(array $data): int
    {
        $status = in_array($data['status'], self::ACTIVE_STATUSES, true) ? $data['status'] : 'pending';
        if (!empty($data['id'])) {
            $passwordSql = $data['password'] !== '' ? ', password_hash = ?' : '';
            $sql = 'UPDATE users SET role_id = (SELECT id FROM roles WHERE slug = ?), name = ?, email = ?, phone = ?, address = ?, status = ?, updated_at = NOW()' . $passwordSql . ' WHERE id = ?';
            $params = [$data['role_slug'], $data['name'], $data['email'], $data['phone'], $data['address'], $status];
            if ($data['password'] !== '') {
                $params[] = password_hash($data['password'], PASSWORD_DEFAULT);
            }
            $params[] = $data['id'];
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return (int) $data['id'];
        }

        return $this->create([
            'role_slug' => $data['role_slug'],
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'phone' => $data['phone'],
            'address' => $data['address'],
            'status' => $status,
        ]);
    }

    public function delete(int $id): void
    {
        $stmt = $this->db->prepare('DELETE FROM users WHERE id = ?');
        $stmt->execute([$id]);
    }

    public function updateProfile(int $id, array $data): void
    {
        $stmt = $this->db->prepare('UPDATE users SET name = ?, phone = ?, address = ?, updated_at = NOW() WHERE id = ?');
        $stmt->execute([$data['name'], $data['phone'], $data['address'], $id]);
    }

    public function setStatus(int $id, string $status): void
    {
        if (!in_array($status, self::ACTIVE_STATUSES, true)) {
            return;
        }
        $stmt = $this->db->prepare('UPDATE users SET status = ?, updated_at = NOW() WHERE id = ?');
        $stmt->execute([$status, $id]);
    }

    public function touchLastLogin(int $id): void
    {
        $stmt = $this->db->prepare('UPDATE users SET first_login = COALESCE(first_login, NOW()), last_login = NOW(), last_active = NOW() WHERE id = ?');
        $stmt->execute([$id]);
    }

    public function logLogin(?int $id, string $email, string $status): void
    {
        $stmt = $this->db->prepare('INSERT INTO login_activity (user_id, email, status, ip_address, user_agent) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([
            $id,
            $email,
            $status,
            $_SERVER['REMOTE_ADDR'] ?? null,
            substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255),
        ]);
    }

    public function roles(): array
    {
        return $this->db->query('SELECT * FROM roles ORDER BY id')->fetchAll();
    }
}
