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

    public function allTrainees(string $search = '', int $limit = 20, int $offset = 0, array $filters = []): array
    {
        $like = '%' . $search . '%';
        $sql = 'SELECT u.*, 
                       c.name AS company_name, 
                       inst.name AS institution_name, 
                       l.name AS location_name, 
                       p.name AS profession_name,
                       tp.education, tp.employment, tp.emergency_contact,
                       (SELECT COUNT(*) FROM enrolments WHERE trainee_id = u.id AND status = "active") AS active_courses_count,
                       (SELECT COUNT(*) FROM enrolments WHERE trainee_id = u.id AND status = "completed") AS completed_courses_count
                FROM users u
                JOIN roles r ON r.id = u.role_id AND r.slug = "trainee"
                LEFT JOIN trainee_profiles tp ON tp.user_id = u.id
                LEFT JOIN companies c ON c.id = u.company_id
                LEFT JOIN institutions inst ON inst.id = u.institution_id
                LEFT JOIN locations l ON l.id = u.location_id
                LEFT JOIN professions p ON p.id = u.profession_id
                WHERE (u.name LIKE ? OR u.email LIKE ? OR u.phone LIKE ? OR u.identity_number LIKE ?)';
        
        $params = [$like, $like, $like, $like];

        if (!empty($filters['location_id'])) {
            $sql .= ' AND u.location_id = ?';
            $params[] = (int) $filters['location_id'];
        }
        if (!empty($filters['company_id'])) {
            $sql .= ' AND u.company_id = ?';
            $params[] = (int) $filters['company_id'];
        }
        if (!empty($filters['institution_id'])) {
            $sql .= ' AND u.institution_id = ?';
            $params[] = (int) $filters['institution_id'];
        }
        if (!empty($filters['profession_id'])) {
            $sql .= ' AND u.profession_id = ?';
            $params[] = (int) $filters['profession_id'];
        }
        if (!empty($filters['status'])) {
            $sql .= ' AND u.status = ?';
            $params[] = $filters['status'];
        }
        if (!empty($filters['academy_id'])) {
            $sql .= ' AND EXISTS (SELECT 1 FROM enrolments e JOIN courses co ON co.id = e.course_id WHERE e.trainee_id = u.id AND co.academy_id = ?)';
            $params[] = (int) $filters['academy_id'];
        }

        $sql .= ' ORDER BY u.name LIMIT ? OFFSET ?';
        
        $stmt = $this->db->prepare($sql);
        foreach ($params as $index => $value) {
            $stmt->bindValue($index + 1, $value);
        }
        $stmt->bindValue(count($params) + 1, $limit, \PDO::PARAM_INT);
        $stmt->bindValue(count($params) + 2, $offset, \PDO::PARAM_INT);
        
        $stmt->execute();
        $trainees = $stmt->fetchAll();

        foreach ($trainees as &$t) {
            $t['profile_completion'] = self::calculateCompletion($t);
        }

        return $trainees;
    }

    public function countTrainees(string $search = '', array $filters = []): int
    {
        $like = '%' . $search . '%';
        $sql = 'SELECT COUNT(DISTINCT u.id)
                FROM users u
                JOIN roles r ON r.id = u.role_id AND r.slug = "trainee"
                LEFT JOIN trainee_profiles tp ON tp.user_id = u.id
                WHERE (u.name LIKE ? OR u.email LIKE ? OR u.phone LIKE ? OR u.identity_number LIKE ?)';
        
        $params = [$like, $like, $like, $like];

        if (!empty($filters['location_id'])) {
            $sql .= ' AND u.location_id = ?';
            $params[] = (int) $filters['location_id'];
        }
        if (!empty($filters['company_id'])) {
            $sql .= ' AND u.company_id = ?';
            $params[] = (int) $filters['company_id'];
        }
        if (!empty($filters['institution_id'])) {
            $sql .= ' AND u.institution_id = ?';
            $params[] = (int) $filters['institution_id'];
        }
        if (!empty($filters['profession_id'])) {
            $sql .= ' AND u.profession_id = ?';
            $params[] = (int) $filters['profession_id'];
        }
        if (!empty($filters['status'])) {
            $sql .= ' AND u.status = ?';
            $params[] = $filters['status'];
        }
        if (!empty($filters['academy_id'])) {
            $sql .= ' AND EXISTS (SELECT 1 FROM enrolments e JOIN courses co ON co.id = e.course_id WHERE e.trainee_id = u.id AND co.academy_id = ?)';
            $params[] = (int) $filters['academy_id'];
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    public function traineeDetail(int $userId): ?array
    {
        $sql = 'SELECT u.*, 
                       c.name AS company_name, 
                       inst.name AS institution_name, 
                       l.name AS location_name, 
                       p.name AS profession_name,
                       tp.education, tp.employment, tp.emergency_contact
                FROM users u
                LEFT JOIN trainee_profiles tp ON tp.user_id = u.id
                LEFT JOIN companies c ON c.id = u.company_id
                LEFT JOIN institutions inst ON inst.id = u.institution_id
                LEFT JOIN locations l ON l.id = u.location_id
                LEFT JOIN professions p ON p.id = u.profession_id
                WHERE u.id = ? LIMIT 1';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        $trainee = $stmt->fetch();
        if ($trainee) {
            $trainee['profile_completion'] = self::calculateCompletion($trainee);
        }
        return $trainee ?: null;
    }

    public function updateTraineeMasterData(int $userId, array $data): void
    {
        $stmt = $this->db->prepare('UPDATE users SET company_id = ?, institution_id = ?, location_id = ?, profession_id = ?, updated_at = NOW() WHERE id = ?');
        $stmt->execute([
            $data['company_id'] ?: null,
            $data['institution_id'] ?: null,
            $data['location_id'] ?: null,
            $data['profession_id'] ?: null,
            $userId
        ]);
    }

    public static function calculateCompletion(array $user): int
    {
        $fields = [
            'phone', 'address', 'identity_number', 'gender', 'date_of_birth',
            'profile_picture', 'company_id', 'institution_id', 'location_id', 'profession_id'
        ];
        $filled = 0;
        foreach ($fields as $field) {
            if (isset($user[$field]) && $user[$field] !== null && $user[$field] !== '') {
                $filled++;
            }
        }
        return (int) round(($filled / count($fields)) * 100);
    }
}

