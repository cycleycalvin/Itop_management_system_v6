<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Core\Security;
use App\Core\View;
use App\Models\Certificate as CertificateModel;

final class ReportController
{
    public function reports(): void
    {
        Auth::requireRole(['admin', 'instructor']);
        $db = Database::connection();
        $academy = (int) ($_GET['academy_id'] ?? 0);
        $course = (int) ($_GET['course_id'] ?? 0);
        $where = [];
        if ($academy) {
            $where[] = 'c.academy_id = ' . $academy;
        }
        if ($course) {
            $where[] = 'c.id = ' . $course;
        }
        $whereSql = $where ? ' WHERE ' . implode(' AND ', $where) : '';
        View::render('reports/index', [
            'summary' => [
                'total_trainees' => (int) $db->query('SELECT COUNT(*) FROM users JOIN roles ON roles.id = users.role_id WHERE roles.slug = "trainee"')->fetchColumn(),
                'active_trainees' => (int) $db->query('SELECT COUNT(*) FROM enrolments WHERE status = "active"')->fetchColumn(),
                'completed_trainees' => (int) $db->query('SELECT COUNT(*) FROM enrolments WHERE status = "completed"')->fetchColumn(),
                'certificates_issued' => (int) $db->query('SELECT COUNT(*) FROM certificates WHERE approval_status = "approved"')->fetchColumn(),
            ],
            'academies' => $db->query('SELECT id, code, name FROM academies ORDER BY code')->fetchAll(),
            'courses' => $db->query('SELECT id, title FROM courses ORDER BY title')->fetchAll(),
            'completion' => $db->query('SELECT c.title, COUNT(e.id) AS total, SUM(e.status = "completed") AS completed, ROUND((SUM(e.status = "completed") / GREATEST(COUNT(e.id), 1)) * 100, 0) AS rate FROM courses c LEFT JOIN enrolments e ON e.course_id = c.id' . $whereSql . ' GROUP BY c.id ORDER BY c.title')->fetchAll(),
            'attendance' => $db->query('SELECT c.title, ROUND(AVG(att.status = "present") * 100, 0) AS attendance_rate, COUNT(att.id) AS records FROM courses c LEFT JOIN enrolments e ON e.course_id = c.id LEFT JOIN attendance att ON att.enrolment_id = e.id' . $whereSql . ' GROUP BY c.id ORDER BY c.title')->fetchAll(),
            'assignments' => $db->query('SELECT c.title, COUNT(a.id) AS assignments, COUNT(s.id) AS submissions, SUM(s.status = "graded") AS graded FROM courses c LEFT JOIN assignments a ON a.course_id = c.id LEFT JOIN assignment_submissions s ON s.assignment_id = a.id' . $whereSql . ' GROUP BY c.id ORDER BY c.title')->fetchAll(),
            'evaluations' => $db->query('SELECT c.title, ROUND(AVG(e.rating), 2) AS avg_rating, COUNT(e.id) AS responses FROM courses c LEFT JOIN evaluations e ON e.course_id = c.id' . $whereSql . ' GROUP BY c.id ORDER BY c.title')->fetchAll(),
            'certificates' => $db->query('SELECT c.title, COUNT(cert.id) AS total, SUM(cert.approval_status = "approved") AS approved, SUM(cert.approval_status = "pending") AS pending FROM courses c LEFT JOIN certificates cert ON cert.course_id = c.id' . $whereSql . ' GROUP BY c.id ORDER BY c.title')->fetchAll(),
            'academyId' => $academy,
            'courseId' => $course,
        ]);
    }

    public function exportCsv(): void
    {
        Auth::requireRole(['admin', 'instructor']);
        $rows = Database::connection()->query('SELECT c.title, COUNT(e.id) AS total, SUM(e.status = "completed") AS completed FROM courses c LEFT JOIN enrolments e ON e.course_id = c.id GROUP BY c.id ORDER BY c.title')->fetchAll();
        $format = Security::cleanString($_GET['format'] ?? 'csv');

        if ($format === 'pdf' && class_exists('Dompdf\\Dompdf')) {
            $html = '<h1>ITOP Training Report</h1><table width="100%" border="1" cellspacing="0" cellpadding="6"><thead><tr><th>Course</th><th>Total Participants</th><th>Completed</th></tr></thead><tbody>';
            foreach ($rows as $row) {
                $html .= '<tr><td>' . Security::e($row['title']) . '</td><td>' . (int) $row['total'] . '</td><td>' . (int) $row['completed'] . '</td></tr>';
            }
            $html .= '</tbody></table>';
            $dompdf = new \Dompdf\Dompdf();
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'landscape');
            $dompdf->render();
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="itop-report.pdf"');
            echo $dompdf->output();
            return;
        }

        header('Content-Type: ' . ($format === 'excel' ? 'application/vnd.ms-excel' : 'text/csv'));
        header('Content-Disposition: attachment; filename="itop-report.' . ($format === 'excel' ? 'xls' : 'csv') . '"');
        $out = fopen('php://output', 'w');
        fputcsv($out, ['Course', 'Total Participants', 'Completed']);
        foreach ($rows as $row) {
            fputcsv($out, [$row['title'], $row['total'], $row['completed']]);
        }
        fclose($out);
    }

    public function verifyCertificate(): void
    {
        $code = Security::cleanString($_GET['code'] ?? '');
        $stmt = Database::connection()->prepare('SELECT cert.*, u.name AS trainee_name, c.title AS course_title FROM certificates cert JOIN users u ON u.id = cert.trainee_id JOIN courses c ON c.id = cert.course_id WHERE cert.verification_code = ?');
        $stmt->execute([$code]);
        View::render('public/certificate-verify', ['certificate' => $stmt->fetch() ?: null, 'code' => $code]);
    }

    public function viewCertificate(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $code = Security::cleanString($_GET['code'] ?? '');
        $db = Database::connection();
        if ($id) {
            $stmt = $db->prepare('SELECT cert.*, u.name AS trainee_name, c.title AS course_title, t.template_id, t.template_name, t.background_image, t.logo, t.signature, t.font_family, t.font_size, t.text_color, t.layout_json FROM certificates cert JOIN users u ON u.id = cert.trainee_id JOIN courses c ON c.id = cert.course_id LEFT JOIN certificate_templates t ON t.template_id = cert.template_id WHERE cert.id = ?');
            $stmt->execute([$id]);
        } elseif ($code) {
            $stmt = $db->prepare('SELECT cert.*, u.name AS trainee_name, c.title AS course_title, t.template_id, t.template_name, t.background_image, t.logo, t.signature, t.font_family, t.font_size, t.text_color, t.layout_json FROM certificates cert JOIN users u ON u.id = cert.trainee_id JOIN courses c ON c.id = cert.course_id LEFT JOIN certificate_templates t ON t.template_id = cert.template_id WHERE cert.verification_code = ?');
            $stmt->execute([$code]);
        } else {
            http_response_code(400);
            exit('Missing certificate identifier.');
        }

        $certificate = $stmt->fetch() ?: null;
        if (!$certificate) {
            http_response_code(404);
            exit('Certificate not found.');
        }
        if (Auth::role() === 'trainee' && ((int) $certificate['trainee_id'] !== (int) Auth::id() || ($certificate['approval_status'] ?? '') !== 'approved')) {
            http_response_code(403);
            exit('Certificate is not available.');
        }

        View::render('public/certificate-template', ['certificate' => $certificate, 'template' => [
            'background_image' => $certificate['background_image'] ?? null,
            'logo' => $certificate['logo'] ?? null,
            'signature' => $certificate['signature'] ?? null,
            'font_family' => $certificate['font_family'] ?? 'Arial',
            'font_size' => $certificate['font_size'] ?? 28,
            'text_color' => $certificate['text_color'] ?? '#182230',
            'layout_json' => $certificate['layout_json'] ?? '{}',
        ]]);
    }

    public function downloadCertificate(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $code = Security::cleanString($_GET['code'] ?? '');
        $db = Database::connection();
        if ($id) {
            $stmt = $db->prepare('SELECT cert.*, u.name AS trainee_name, c.title AS course_title, t.template_id, t.template_name, t.background_image, t.logo, t.signature, t.font_family, t.font_size, t.text_color, t.layout_json FROM certificates cert JOIN users u ON u.id = cert.trainee_id JOIN courses c ON c.id = cert.course_id LEFT JOIN certificate_templates t ON t.template_id = cert.template_id WHERE cert.id = ?');
            $stmt->execute([$id]);
        } elseif ($code) {
            $stmt = $db->prepare('SELECT cert.*, u.name AS trainee_name, c.title AS course_title, t.template_id, t.template_name, t.background_image, t.logo, t.signature, t.font_family, t.font_size, t.text_color, t.layout_json FROM certificates cert JOIN users u ON u.id = cert.trainee_id JOIN courses c ON c.id = cert.course_id LEFT JOIN certificate_templates t ON t.template_id = cert.template_id WHERE cert.verification_code = ?');
            $stmt->execute([$code]);
        } else {
            http_response_code(400);
            exit('Missing certificate identifier.');
        }

        $certificate = $stmt->fetch() ?: null;
        if (!$certificate) {
            http_response_code(404);
            exit('Certificate not found.');
        }
        if (Auth::role() === 'trainee' && ((int) $certificate['trainee_id'] !== (int) Auth::id() || ($certificate['approval_status'] ?? '') !== 'approved')) {
            http_response_code(403);
            exit('Certificate is not available.');
        }

        $html = $this->certificatePdfHtml($certificate);

        // Try to render PDF with Dompdf if available
        if (class_exists('Dompdf\\Dompdf')) {
            $pdfPath = CERTIFICATE_PATH . '/certificate_' . ($certificate['id'] ?? $id) . '_' . time() . '.pdf';
            if (!is_dir(CERTIFICATE_PATH)) {
                @mkdir(CERTIFICATE_PATH, 0755, true);
            }
            $dompdf = new \Dompdf\Dompdf();
            $dompdf->setPaper('A4', 'landscape');
            $dompdf->loadHtml($html);
            $dompdf->render();
            file_put_contents($pdfPath, $dompdf->output());

            // Update certificate record with pdf_path if model exists
            if (class_exists('\App\\Models\\Certificate')) {
                (new CertificateModel())->setPdfPath((int) ($certificate['id'] ?? $id), str_replace(dirname(__DIR__, 2) . '/', '', $pdfPath));
                if (Auth::check()) {
                    (new CertificateModel())->downloadLog((int) ($certificate['id'] ?? $id), (int) Auth::id());
                }
            }

            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="' . basename($pdfPath) . '"');
            readfile($pdfPath);
            exit;
        }

        // Fallback: stream HTML with instructions
        header('Content-Type: text/html');
        echo $html;
        echo '<p style="text-align:center;">PDF generation requires <strong>dompdf/dompdf</strong>. Install via Composer: <code>composer require dompdf/dompdf</code></p>';
    }

    private function certificatePdfHtml(array $certificate): string
    {
        $number = htmlspecialchars((string) ($certificate['certificate_number'] ?? $certificate['certificate_no'] ?? ''), ENT_QUOTES, 'UTF-8');
        $name = htmlspecialchars((string) ($certificate['trainee_name'] ?? 'Participant Name'), ENT_QUOTES, 'UTF-8');
        $course = htmlspecialchars((string) ($certificate['course_title'] ?? 'Course Title'), ENT_QUOTES, 'UTF-8');
        $date = htmlspecialchars((string) ($certificate['issue_date'] ?? $certificate['issued_at'] ?? date('Y-m-d')), ENT_QUOTES, 'UTF-8');
        return '<!doctype html><html><head><meta charset="utf-8"><style>
            @page { margin: 24px; }
            body { font-family: DejaVu Sans, Arial, sans-serif; color: #182230; }
            .cert { border: 10px double #25566a; padding: 42px; height: 430px; text-align: center; }
            .title { color: #aa3338; font-size: 38px; font-weight: bold; margin-top: 28px; }
            .name { font-size: 30px; margin: 28px 0 18px; color: #000; }
            .intro { color: #244f63; font-size: 15px; }
            .course { color: #aa3338; font-size: 24px; margin: 14px auto; max-width: 760px; }
            .seal { border: 4px solid #b53638; border-radius: 50%; width: 96px; height: 96px; margin: 26px auto 10px; display: table; color: #244f63; }
            .seal span { display: table-cell; vertical-align: middle; font-weight: bold; font-size: 20px; }
            .footer { margin-top: 32px; display: table; width: 100%; font-size: 12px; color: #244f63; }
            .left, .right { display: table-cell; width: 50%; }
            .left { text-align: left; }
            .right { text-align: right; }
            .line { border-top: 1px solid #182230; width: 180px; margin-left: auto; margin-bottom: 8px; }
        </style></head><body><div class="cert">
            <div class="title">CENTEXS Certification</div>
            <div class="name">' . $name . '</div>
            <div class="intro">has successfully completed the required training and assessment for</div>
            <div class="course">' . $course . '</div>
            <div class="seal"><span>ITOP<br>Certified</span></div>
            <div class="intro">Issued On <strong>' . $date . '</strong></div>
            <div class="footer"><div class="left">Certificate No.<br><strong>' . $number . '</strong></div><div class="right"><div class="line"></div><strong>Authorized Signatory</strong><br>CENTEXS</div></div>
        </div></body></html>';
    }
}
