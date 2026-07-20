<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Course extends Model
{
    private ?bool $academySchemaReady = null;

    private function academySchemaReady(): bool
    {
        if ($this->academySchemaReady !== null) {
            return $this->academySchemaReady;
        }

        try {
            $hasAcademiesTable = (bool) $this->db->query("SHOW TABLES LIKE 'academies'")->fetchColumn();
            $hasAcademyColumn = (bool) $this->db->query("SHOW COLUMNS FROM courses LIKE 'academy_id'")->fetchColumn();
            $this->academySchemaReady = $hasAcademiesTable && $hasAcademyColumn;
        } catch (\Throwable $exception) {
            $this->academySchemaReady = false;
        }

        return $this->academySchemaReady;
    }

    public function publicList(string $search = ''): array
    {
        $like = '%' . $search . '%';
        $sql = 'SELECT courses.*, users.name AS instructor_name, NULL AS academy_code, NULL AS academy_name, COUNT(enrolments.id) AS participant_count FROM courses LEFT JOIN users ON users.id = courses.instructor_id LEFT JOIN enrolments ON enrolments.course_id = courses.id AND enrolments.status IN ("active","completed") WHERE courses.status IN ("published","active") AND (courses.title LIKE ? OR courses.category LIKE ?) GROUP BY courses.id ORDER BY courses.start_date ASC';
        if ($this->academySchemaReady()) {
            $sql = 'SELECT courses.*, users.name AS instructor_name, academies.code AS academy_code, academies.name AS academy_name, COUNT(enrolments.id) AS participant_count FROM courses LEFT JOIN users ON users.id = courses.instructor_id LEFT JOIN academies ON academies.id = courses.academy_id LEFT JOIN enrolments ON enrolments.course_id = courses.id AND enrolments.status IN ("active","completed") WHERE courses.status IN ("published","active") AND (courses.title LIKE ? OR courses.category LIKE ?) GROUP BY courses.id ORDER BY courses.start_date ASC';
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$like, $like]);
        return $stmt->fetchAll();
    }

    public function publicByAcademy(string $academyCode, string $search = ''): array
    {
        if (!$this->academySchemaReady()) {
            return [];
        }

        $like = '%' . $search . '%';
        $stmt = $this->db->prepare('SELECT courses.*, users.name AS instructor_name, academies.code AS academy_code, academies.name AS academy_name, COUNT(enrolments.id) AS participant_count FROM courses LEFT JOIN users ON users.id = courses.instructor_id LEFT JOIN academies ON academies.id = courses.academy_id LEFT JOIN enrolments ON enrolments.course_id = courses.id AND enrolments.status IN ("active","completed") WHERE courses.status IN ("published","active") AND academies.code = ? AND (courses.title LIKE ? OR courses.category LIKE ?) GROUP BY courses.id ORDER BY courses.category, courses.title');
        $stmt->execute([$academyCode, $like, $like]);
        return $stmt->fetchAll();
    }

    public function publicAcademies(): array
    {
        if (!$this->academySchemaReady()) {
            return [];
        }

        return $this->db->query('SELECT a.*, COUNT(c.id) AS course_count, COALESCE(SUM(ts.participants), 0) AS participant_count FROM academies a LEFT JOIN courses c ON c.academy_id = a.id AND c.status IN ("published","active") LEFT JOIN training_statistics ts ON ts.academy_id = a.id WHERE a.code IN ("ADGEA","IESGA") GROUP BY a.id ORDER BY FIELD(a.code, "ADGEA", "IESGA")')->fetchAll();
    }

    public function academyByCode(string $academyCode): ?array
    {
        if (!$this->academySchemaReady()) {
            return null;
        }

        $stmt = $this->db->prepare('SELECT * FROM academies WHERE code = ? LIMIT 1');
        $stmt->execute([$academyCode]);
        return $stmt->fetch() ?: null;
    }

    public function all(string $search = '', string $status = '', string $category = ''): array
    {
        $like = '%' . $search . '%';
        $sql = 'SELECT courses.*, users.name AS instructor_name, NULL AS academy_code, NULL AS academy_name, COUNT(enrolments.id) AS participant_count FROM courses LEFT JOIN users ON users.id = courses.instructor_id LEFT JOIN enrolments ON enrolments.course_id = courses.id AND enrolments.status IN ("active","completed") WHERE (courses.title LIKE ? OR courses.category LIKE ?)';
        if ($this->academySchemaReady()) {
            $sql = 'SELECT courses.*, users.name AS instructor_name, academies.code AS academy_code, academies.name AS academy_name, COUNT(enrolments.id) AS participant_count FROM courses LEFT JOIN users ON users.id = courses.instructor_id LEFT JOIN academies ON academies.id = courses.academy_id LEFT JOIN enrolments ON enrolments.course_id = courses.id AND enrolments.status IN ("active","completed") WHERE (courses.title LIKE ? OR courses.category LIKE ?)';
        }
        $params = [$like, $like];
        if ($status !== '') {
            $sql .= ' AND courses.status = ?';
            $params[] = $status;
        }
        if ($category !== '') {
            $sql .= ' AND courses.category = ?';
            $params[] = $category;
        }
        $sql .= ' GROUP BY courses.id ORDER BY courses.created_at DESC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function assignedTo(int $instructorId): array
    {
        $sql = 'SELECT courses.*, NULL AS academy_code, NULL AS academy_name, COUNT(enrolments.id) AS participant_count FROM courses LEFT JOIN enrolments ON enrolments.course_id = courses.id AND enrolments.status IN ("active","completed") WHERE instructor_id = ? GROUP BY courses.id ORDER BY start_date DESC';
        if ($this->academySchemaReady()) {
            $sql = 'SELECT courses.*, academies.code AS academy_code, academies.name AS academy_name, COUNT(enrolments.id) AS participant_count FROM courses LEFT JOIN academies ON academies.id = courses.academy_id LEFT JOIN enrolments ON enrolments.course_id = courses.id AND enrolments.status IN ("active","completed") WHERE instructor_id = ? GROUP BY courses.id ORDER BY start_date DESC';
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$instructorId]);
        return $stmt->fetchAll();
    }

    public function find(int $id): ?array
    {
        $sql = 'SELECT courses.*, users.name AS instructor_name, NULL AS academy_code, NULL AS academy_name FROM courses LEFT JOIN users ON users.id = courses.instructor_id WHERE courses.id = ?';
        if ($this->academySchemaReady()) {
            $sql = 'SELECT courses.*, users.name AS instructor_name, academies.code AS academy_code, academies.name AS academy_name FROM courses LEFT JOIN users ON users.id = courses.instructor_id LEFT JOIN academies ON academies.id = courses.academy_id WHERE courses.id = ?';
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function save(array $data): int
    {
        $supportsAcademySchema = $this->academySchemaReady();

        if (!empty($data['id'])) {
            if ($supportsAcademySchema) {
                $stmt = $this->db->prepare('UPDATE courses SET title=?, category=?, description=?, start_date=?, end_date=?, capacity=?, max_participants=?, status=?, course_status=?, thumbnail_image=?, instructor_id=?, fee=?, created_by=COALESCE(created_by, ?), academy_id=?, updated_at=NOW() WHERE id=?');
                $stmt->execute([$data['title'], $data['category'], $data['description'], $data['start_date'], $data['end_date'], $data['capacity'], $data['max_participants'], $data['status'], $data['course_status'], $data['thumbnail_image'], $data['instructor_id'] ?: null, $data['fee'], $data['created_by'], $data['academy_id'] ?? null, $data['id']]);
            } else {
                $stmt = $this->db->prepare('UPDATE courses SET title=?, category=?, description=?, start_date=?, end_date=?, capacity=?, max_participants=?, status=?, course_status=?, thumbnail_image=?, instructor_id=?, fee=?, created_by=COALESCE(created_by, ?), updated_at=NOW() WHERE id=?');
                $stmt->execute([$data['title'], $data['category'], $data['description'], $data['start_date'], $data['end_date'], $data['capacity'], $data['max_participants'], $data['status'], $data['course_status'], $data['thumbnail_image'], $data['instructor_id'] ?: null, $data['fee'], $data['created_by'], $data['id']]);
            }
            return (int) $data['id'];
        }

        if ($supportsAcademySchema) {
            $stmt = $this->db->prepare('INSERT INTO courses (title, category, description, start_date, end_date, capacity, max_participants, status, course_status, thumbnail_image, instructor_id, fee, created_by, academy_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([$data['title'], $data['category'], $data['description'], $data['start_date'], $data['end_date'], $data['capacity'], $data['max_participants'], $data['status'], $data['course_status'], $data['thumbnail_image'], $data['instructor_id'] ?: null, $data['fee'], $data['created_by'], $data['academy_id'] ?? null]);
        } else {
            $stmt = $this->db->prepare('INSERT INTO courses (title, category, description, start_date, end_date, capacity, max_participants, status, course_status, thumbnail_image, instructor_id, fee, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([$data['title'], $data['category'], $data['description'], $data['start_date'], $data['end_date'], $data['capacity'], $data['max_participants'], $data['status'], $data['course_status'], $data['thumbnail_image'], $data['instructor_id'] ?: null, $data['fee'], $data['created_by']]);
        }
        return (int) $this->db->lastInsertId();
    }

    public function delete(int $id): void
    {
        $stmt = $this->db->prepare('DELETE FROM courses WHERE id = ?');
        $stmt->execute([$id]);
    }

    public function categories(): array
    {
        return $this->db->query('SELECT DISTINCT category FROM courses WHERE category <> "" ORDER BY category')->fetchAll();
    }

    public function instructors(): array
    {
        return $this->db->query('SELECT users.id, users.name FROM users JOIN roles ON roles.id = users.role_id WHERE roles.slug = "instructor" AND users.status = "active" ORDER BY users.name')->fetchAll();
    }
}
