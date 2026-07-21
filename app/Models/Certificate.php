<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Certificate extends Model
{
    public function templates(): array
    {
        return $this->db->query('SELECT * FROM certificate_templates ORDER BY created_at DESC')->fetchAll();
    }

    public function activeTemplates(): array
    {
        return $this->db->query('SELECT * FROM certificate_templates WHERE status = "active" ORDER BY template_name')->fetchAll();
    }

    public function template(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM certificate_templates WHERE template_id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function saveTemplate(array $data): int
    {
        if (!empty($data['template_id'])) {
            $stmt = $this->db->prepare('UPDATE certificate_templates SET template_name=?, background_image=?, logo=?, signature=?, font_family=?, font_size=?, text_color=?, layout_json=?, status=?, updated_at=NOW() WHERE template_id=?');
            $stmt->execute([$data['template_name'], $data['background_image'], $data['logo'], $data['signature'], $data['font_family'], $data['font_size'], $data['text_color'], $data['layout_json'], $data['status'], $data['template_id']]);
            return (int) $data['template_id'];
        }

        $stmt = $this->db->prepare('INSERT INTO certificate_templates (template_name, background_image, logo, signature, font_family, font_size, text_color, layout_json, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([$data['template_name'], $data['background_image'], $data['logo'], $data['signature'], $data['font_family'], $data['font_size'], $data['text_color'], $data['layout_json'], $data['status']]);
        return (int) $this->db->lastInsertId();
    }

    public function deleteTemplate(int $id): void
    {
        $stmt = $this->db->prepare('DELETE FROM certificate_templates WHERE template_id = ?');
        $stmt->execute([$id]);
    }

    public function records(string $search = ''): array
    {
        $like = '%' . $search . '%';
        $stmt = $this->db->prepare('SELECT cert.*, COALESCE(cert.certificate_number, cert.certificate_no) AS display_number, u.name AS trainee_name, c.title AS course_title, i.name AS instructor_name, t.template_name FROM certificates cert JOIN users u ON u.id = cert.trainee_id JOIN courses c ON c.id = cert.course_id LEFT JOIN users i ON i.id = c.instructor_id LEFT JOIN certificate_templates t ON t.template_id = cert.template_id WHERE u.name LIKE ? OR c.title LIKE ? OR cert.certificate_no LIKE ? OR cert.certificate_number LIKE ? ORDER BY COALESCE(cert.issue_date, cert.issued_at) DESC');
        $stmt->execute([$like, $like, $like, $like]);
        return $stmt->fetchAll();
    }

    public function forTrainee(int $traineeId, string $search = ''): array
    {
        $like = '%' . $search . '%';
        $stmt = $this->db->prepare('SELECT cert.*, COALESCE(cert.certificate_number, cert.certificate_no) AS display_number, c.title AS course_title, i.name AS instructor_name, t.template_name, t.logo, t.signature, t.font_family, t.font_size, t.text_color, t.layout_json FROM certificates cert JOIN courses c ON c.id = cert.course_id JOIN enrolments e ON e.course_id = cert.course_id AND e.trainee_id = cert.trainee_id LEFT JOIN users i ON i.id = c.instructor_id LEFT JOIN certificate_templates t ON t.template_id = cert.template_id WHERE cert.trainee_id = ? AND cert.approval_status = "approved" AND e.status = "completed" AND e.attendance_requirement_met = 1 AND e.assessments_completed = 1 AND e.evaluation_submitted = 1 AND (c.title LIKE ? OR cert.certificate_no LIKE ? OR cert.certificate_number LIKE ?) ORDER BY COALESCE(cert.issue_date, cert.issued_at) DESC');
        $stmt->execute([$traineeId, $like, $like, $like]);
        return $stmt->fetchAll();
    }

    public function issue(array $data): int
    {
        $number = $data['certificate_number'] ?: 'ITOP-' . date('Y') . '-' . str_pad((string) random_int(1, 999999), 6, '0', STR_PAD_LEFT);
        $verification = $data['verification_code'] ?: 'CENTEXS-' . bin2hex(random_bytes(5));
        $stmt = $this->db->prepare('INSERT INTO certificates (course_id, trainee_id, template_id, certificate_no, certificate_number, verification_code, file_path, pdf_path, issued_at, issue_date, issued_by, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE template_id=VALUES(template_id), file_path=VALUES(file_path), pdf_path=VALUES(pdf_path), issue_date=VALUES(issue_date), issued_by=VALUES(issued_by), status=VALUES(status)');
        $stmt->execute([
            $data['course_id'],
            $data['trainee_id'],
            $data['template_id'] ?: null,
            $number,
            $number,
            $verification,
            $data['pdf_path'],
            $data['pdf_path'],
            $data['issue_date'],
            $data['issue_date'],
            $data['issued_by'],
            $data['status'],
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function setPdfPath(int $id, string $path): void
    {
        $stmt = $this->db->prepare('UPDATE certificates SET pdf_path = ? WHERE id = ?');
        $stmt->execute([$path, $id]);
    }

    public function approve(int $id, int $reviewerId, string $status, string $remarks = ''): void
    {
        $approvedAt = $status === 'approved' ? 'NOW()' : 'NULL';
        $stmt = $this->db->prepare('UPDATE certificates SET approval_status = ?, approved_by = ?, approved_at = ' . $approvedAt . ', rejection_reason = ? WHERE id = ?');
        $stmt->execute([$status, $reviewerId, $status === 'rejected' ? $remarks : null, $id]);

        $log = $this->db->prepare('INSERT INTO certificate_approvals (certificate_id, reviewer_id, status, remarks) VALUES (?, ?, ?, ?)');
        $log->execute([$id, $reviewerId, $status, $remarks]);
    }

    public function downloadLog(int $id, int $userId): void
    {
        $stmt = $this->db->prepare('INSERT INTO certificate_download_logs (certificate_id, user_id, ip_address) VALUES (?, ?, ?)');
        $stmt->execute([$id, $userId, $_SERVER['REMOTE_ADDR'] ?? null]);
    }

    public function getDownloadLogs(int $id): array
    {
        $stmt = $this->db->prepare('SELECT l.*, u.name AS user_name, u.email AS user_email FROM certificate_download_logs l JOIN users u ON u.id = l.user_id WHERE l.certificate_id = ? ORDER BY l.downloaded_at DESC');
        $stmt->execute([$id]);
        return $stmt->fetchAll();
    }

    public function revoke(int $id): void
    {
        $stmt = $this->db->prepare('UPDATE certificates SET status = "revoked" WHERE id = ?');
        $stmt->execute([$id]);
    }
}
