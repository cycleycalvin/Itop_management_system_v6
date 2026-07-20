<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Enrollment extends Model
{
    public function request(int $courseId, int $traineeId): void
    {
        $stmt = $this->db->prepare('INSERT IGNORE INTO enrolments (course_id, trainee_id, status) VALUES (?, ?, "pending")');
        $stmt->execute([$courseId, $traineeId]);
    }

    public function forTrainee(int $traineeId): array
    {
        $stmt = $this->db->prepare('SELECT e.*, c.title, c.category, c.description, c.start_date, c.end_date, c.status AS course_status, c.thumbnail_image, c.capacity, c.max_participants, users.name AS instructor_name, counts.participant_count FROM enrolments e JOIN courses c ON c.id = e.course_id LEFT JOIN users ON users.id = c.instructor_id LEFT JOIN (SELECT course_id, COUNT(*) AS participant_count FROM enrolments WHERE status IN ("active","completed") GROUP BY course_id) counts ON counts.course_id = c.id WHERE e.trainee_id = ? ORDER BY e.created_at DESC');
        $stmt->execute([$traineeId]);
        return $stmt->fetchAll();
    }

    public function pending(): array
    {
        return $this->db->query('SELECT e.*, c.title AS course_title, u.name AS trainee_name, u.email FROM enrolments e JOIN courses c ON c.id = e.course_id JOIN users u ON u.id = e.trainee_id WHERE e.status = "pending" ORDER BY e.created_at DESC')->fetchAll();
    }

    /** All enrolments for admin view (all statuses) */
    public function allEnrolments(): array
    {
        return $this->db->query('SELECT e.*, c.title AS course_title, u.name AS trainee_name, u.email, instr.name AS instructor_name FROM enrolments e JOIN courses c ON c.id = e.course_id JOIN users u ON u.id = e.trainee_id LEFT JOIN users instr ON instr.id = c.instructor_id ORDER BY e.created_at DESC')->fetchAll();
    }

    /** Enrolments for courses assigned to a specific instructor */
    public function forInstructor(int $instructorId): array
    {
        $stmt = $this->db->prepare('SELECT e.*, c.title AS course_title, u.name AS trainee_name, u.email FROM enrolments e JOIN courses c ON c.id = e.course_id JOIN users u ON u.id = e.trainee_id WHERE c.instructor_id = ? ORDER BY e.created_at DESC');
        $stmt->execute([$instructorId]);
        return $stmt->fetchAll();
    }

    public function setStatus(int $id, string $status): void
    {
        $stmt = $this->db->prepare('UPDATE enrolments SET status = ?, updated_at = NOW() WHERE id = ?');
        $stmt->execute([$status, $id]);
    }

    /** Verify that an enrolment belongs to a course taught by the given instructor */
    public function belongsToInstructor(int $enrolmentId, int $instructorId): bool
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM enrolments e JOIN courses c ON c.id = e.course_id WHERE e.id = ? AND c.instructor_id = ?');
        $stmt->execute([$enrolmentId, $instructorId]);
        return (int) $stmt->fetchColumn() > 0;
    }
}
