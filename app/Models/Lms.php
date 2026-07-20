<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Lms extends Model
{
    public function materials(int $courseId): array
    {
        $stmt = $this->db->prepare('SELECT * FROM learning_materials WHERE course_id = ? ORDER BY created_at DESC');
        $stmt->execute([$courseId]);
        return $stmt->fetchAll();
    }

    public function assignments(int $courseId): array
    {
        $stmt = $this->db->prepare('SELECT * FROM assignments WHERE course_id = ? ORDER BY due_date ASC');
        $stmt->execute([$courseId]);
        return $stmt->fetchAll();
    }

    public function quizzes(int $courseId): array
    {
        $stmt = $this->db->prepare('SELECT * FROM quizzes WHERE course_id = ? ORDER BY created_at DESC');
        $stmt->execute([$courseId]);
        return $stmt->fetchAll();
    }

    public function addMaterial(array $data): void
    {
        $stmt = $this->db->prepare('INSERT INTO learning_materials (course_id, title, type, file_path, external_url, uploaded_by) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->execute([$data['course_id'], $data['title'], $data['type'], $data['file_path'], $data['external_url'], $data['uploaded_by']]);
    }

    public function addAssignment(array $data): void
    {
        $stmt = $this->db->prepare('INSERT INTO assignments (course_id, title, instructions, due_date, max_score, created_by) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->execute([$data['course_id'], $data['title'], $data['instructions'], $data['due_date'], $data['max_score'], $data['created_by']]);
    }

    public function submitAssignment(array $data): void
    {
        $stmt = $this->db->prepare('INSERT INTO assignment_submissions (assignment_id, trainee_id, file_path, notes) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE file_path = VALUES(file_path), notes = VALUES(notes), submitted_at = NOW(), status = "submitted"');
        $stmt->execute([$data['assignment_id'], $data['trainee_id'], $data['file_path'], $data['notes']]);
    }

    public function gradeSubmission(int $submissionId, float $score, string $feedback): void
    {
        $stmt = $this->db->prepare('UPDATE assignment_submissions SET score = ?, feedback = ?, status = "graded", graded_at = NOW() WHERE id = ?');
        $stmt->execute([$score, $feedback, $submissionId]);
    }

    public function submissionsForInstructor(int $instructorId): array
    {
        $stmt = $this->db->prepare('SELECT s.*, a.title AS assignment_title, c.title AS course_title, u.name AS trainee_name FROM assignment_submissions s JOIN assignments a ON a.id = s.assignment_id JOIN courses c ON c.id = a.course_id JOIN users u ON u.id = s.trainee_id WHERE c.instructor_id = ? ORDER BY s.submitted_at DESC');
        $stmt->execute([$instructorId]);
        return $stmt->fetchAll();
    }
}

