<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Evaluation extends Model
{
    public function save(array $data): void
    {
        $stmt = $this->db->prepare('INSERT INTO evaluations (course_id, trainee_id, rating, course_rating, instructor_rating, feedback, comments, completed_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW()) ON DUPLICATE KEY UPDATE rating=VALUES(rating), course_rating=VALUES(course_rating), instructor_rating=VALUES(instructor_rating), feedback=VALUES(feedback), comments=VALUES(comments), completed_at=NOW()');
        $stmt->execute([$data['course_id'], $data['trainee_id'], $data['course_rating'], $data['course_rating'], $data['instructor_rating'], $data['feedback'], $data['comments']]);
    }

    public function reports(): array
    {
        return $this->db->query('SELECT e.*, u.name AS trainee_name, c.title AS course_title, i.name AS instructor_name FROM evaluations e JOIN users u ON u.id = e.trainee_id JOIN courses c ON c.id = e.course_id LEFT JOIN users i ON i.id = c.instructor_id ORDER BY COALESCE(e.completed_at, e.created_at) DESC')->fetchAll();
    }

    public function completedCoursesNeedingEvaluation(int $traineeId): array
    {
        $stmt = $this->db->prepare('SELECT e.course_id, c.title FROM enrolments e JOIN courses c ON c.id = e.course_id LEFT JOIN evaluations ev ON ev.course_id = e.course_id AND ev.trainee_id = e.trainee_id WHERE e.trainee_id = ? AND e.status = "completed" AND ev.id IS NULL ORDER BY e.completed_at DESC');
        $stmt->execute([$traineeId]);
        return $stmt->fetchAll();
    }
}
