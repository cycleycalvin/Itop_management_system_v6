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
            'master_data_stats' => $db->query('SELECT ts.*, a.code AS academy_code, a.name AS academy_name, c.title AS course_title FROM training_statistics ts JOIN academies a ON a.id = ts.academy_id LEFT JOIN courses c ON c.id = ts.course_id ORDER BY a.code, ts.participants DESC')->fetchAll(),
            'academyId' => $academy,
            'courseId' => $course,
        ]);
    }

    public function exportCsv(): void
    {
        Auth::requireRole(['admin', 'instructor']);
        $db = Database::connection();
        $format = Security::cleanString($_GET['format'] ?? 'csv');
        $reportType = Security::cleanString($_GET['report_type'] ?? 'completion');

        $rows = [];
        $headers = [];
        $title = 'ITOP Training Report';

        switch ($reportType) {
            case 'attendance':
                $title = 'Attendance Report';
                $headers = ['Course', 'Attendance Rate (%)', 'Total Records'];
                $data = $db->query('SELECT c.title, ROUND(AVG(att.status = "present") * 100, 0) AS attendance_rate, COUNT(att.id) AS records FROM courses c LEFT JOIN enrolments e ON e.course_id = c.id LEFT JOIN attendance att ON att.enrolment_id = e.id GROUP BY c.id ORDER BY c.title')->fetchAll();
                foreach ($data as $row) {
                    $rows[] = [$row['title'], $row['attendance_rate'], $row['records']];
                }
                break;
            case 'certificates':
                $title = 'Certificates Report';
                $headers = ['Course', 'Total Certificates', 'Approved', 'Pending'];
                $data = $db->query('SELECT c.title, COUNT(cert.id) AS total, SUM(cert.approval_status = "approved") AS approved, SUM(cert.approval_status = "pending") AS pending FROM courses c LEFT JOIN certificates cert ON cert.course_id = c.id GROUP BY c.id ORDER BY c.title')->fetchAll();
                foreach ($data as $row) {
                    $rows[] = [$row['title'], (int)$row['total'], (int)$row['approved'], (int)$row['pending']];
                }
                break;
            case 'master_data':
                $title = 'Master Data Statistics Report';
                $headers = ['Academy', 'Course Name', 'Participants'];
                $data = $db->query('SELECT a.code AS academy_code, ts.course_name, ts.participants FROM training_statistics ts JOIN academies a ON a.id = ts.academy_id ORDER BY a.code, ts.participants DESC')->fetchAll();
                foreach ($data as $row) {
                    $rows[] = [$row['academy_code'], $row['course_name'], $row['participants']];
                }
                break;
            case 'completion':
            default:
                $title = 'Course Completion Report';
                $headers = ['Course', 'Total Participants', 'Completed', 'Completion Rate (%)'];
                $data = $db->query('SELECT c.title, COUNT(e.id) AS total, SUM(e.status = "completed") AS completed, ROUND((SUM(e.status = "completed") / GREATEST(COUNT(e.id), 1)) * 100, 0) AS rate FROM courses c LEFT JOIN enrolments e ON e.course_id = c.id GROUP BY c.id ORDER BY c.title')->fetchAll();
                foreach ($data as $row) {
                    $rows[] = [$row['title'], (int)$row['total'], (int)$row['completed'], $row['rate']];
                }
                break;
        }

        if ($format === 'pdf' && class_exists('Dompdf\\Dompdf')) {
            $html = '<h1>' . Security::e($title) . '</h1><table width="100%" border="1" cellspacing="0" cellpadding="6"><thead><tr>';
            foreach ($headers as $header) {
                $html .= '<th>' . Security::e($header) . '</th>';
            }
            $html .= '</tr></thead><tbody>';
            foreach ($rows as $row) {
                $html .= '<tr>';
                foreach ($row as $col) {
                    $html .= '<td>' . Security::e((string)$col) . '</td>';
                }
                $html .= '</tr>';
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
        fputcsv($out, $headers);
        foreach ($rows as $row) {
            fputcsv($out, $row);
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
            $options = new \Dompdf\Options();
            $options->set('isRemoteEnabled', true);
            $dompdf = new \Dompdf\Dompdf($options);
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

    public function exportWord(): void
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

        $html = $this->certificateWordHtml($certificate);

        $filename = 'certificate_' . ($certificate['certificate_no'] ?? $certificate['id'] ?? time()) . '.doc';
        
        header("Content-Type: application/force-download");
        header("Content-Type: application/msword");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Content-Disposition: attachment; filename=\"$filename\"");
        
        // Outputting HTML structure, Word will interpret it as a document.
        echo $html;
        exit;
    }

    private function certificateWordHtml(array $certificate): string
    {
        $number = htmlspecialchars((string) (!empty($certificate['certificate_number']) ? $certificate['certificate_number'] : (!empty($certificate['certificate_no']) ? $certificate['certificate_no'] : '')), ENT_QUOTES, 'UTF-8');
        $rawName = !empty($certificate['trainee_name']) ? $certificate['trainee_name'] : 'Participant Name';
        $name = htmlspecialchars((string) $rawName, ENT_QUOTES, 'UTF-8');
        $course = htmlspecialchars((string) (!empty($certificate['course_title']) ? $certificate['course_title'] : 'Course Title'), ENT_QUOTES, 'UTF-8');
        $date = htmlspecialchars((string) ($certificate['issue_date'] ?? $certificate['issued_at'] ?? date('Y-m-d')), ENT_QUOTES, 'UTF-8');
        $formattedDate = strtoupper(date('d F Y', strtotime($date)));

        $layout = [];
        if (!empty($certificate['layout_json'])) {
            $layout = json_decode((string) $certificate['layout_json'], true) ?: [];
        }

        $title = htmlspecialchars((string) ($layout['title'] ?? 'CERTIFICATE'), ENT_QUOTES, 'UTF-8');
        $intro = htmlspecialchars((string) ($layout['intro'] ?? 'This is to certify that'), ENT_QUOTES, 'UTF-8');
        $introScript = htmlspecialchars((string) ($layout['intro_script'] ?? 'has successfully completed the training programme for :'), ENT_QUOTES, 'UTF-8');
        $description = htmlspecialchars((string) ($layout['description'] ?? 'Congratulations on your active participation in this program...'), ENT_QUOTES, 'UTF-8');
        $signatoryName = htmlspecialchars((string) ($layout['signatory_name'] ?? 'Dato Haji Syeed Mohd Hussien Bin Wan Abd Rahman'), ENT_QUOTES, 'UTF-8');
        $signatoryTitle = htmlspecialchars((string) ($layout['signatory_title'] ?? 'Chief Executive Officer'), ENT_QUOTES, 'UTF-8');
        $organization = htmlspecialchars((string) ($layout['organization'] ?? 'Centre for Technology Excellence Sarawak'), ENT_QUOTES, 'UTF-8');
        $showWatermark = (bool) ($layout['show_watermark'] ?? true);
        $showQr = (bool) ($layout['show_qr'] ?? true);

        $textColor = htmlspecialchars((string) ($certificate['text_color'] ?? '#000000'), ENT_QUOTES, 'UTF-8');

        $logoUrl = !empty($certificate['logo']) ? APP_URL . '/storage/uploads/' . $certificate['logo'] : APP_URL . '/public/assets/img/centexs-logo-with-outline-1.png';
        $sigUrl = !empty($certificate['signature']) ? APP_URL . '/storage/uploads/' . $certificate['signature'] : '';
        $bgUrl = !empty($certificate['background_image']) ? APP_URL . '/storage/uploads/' . $certificate['background_image'] : APP_URL . '/public/assets/img/centexs-logo-with-outline-1.png';

        $verifyUrl = APP_URL . '/index.php?page=verify-certificate&code=' . urlencode((string) $certificate['verification_code']);
        $qrCodeSrc = 'https://api.qrserver.com/v1/create-qr-code/?size=120x120&data=' . urlencode($verifyUrl);

        // Get actual logo size to prevent stretching in Word
        $logoPath = !empty($certificate['logo']) ? dirname(__DIR__, 2) . '/storage/uploads/' . $certificate['logo'] : dirname(__DIR__, 2) . '/public/assets/img/centexs-logo-with-outline-1.png';
        $logoImg = '<img src="' . $logoUrl . '" style="height: 160px; width: auto; border: none; margin-bottom: 20px;">';
        if (file_exists($logoPath)) {
            $size = @getimagesize($logoPath);
            if ($size && $size[1] > 0) {
                $ratio = $size[0] / $size[1];
                $targetHeight = 160;
                $targetWidth = round($targetHeight * $ratio);
                $logoImg = '<img src="' . $logoUrl . '" width="' . $targetWidth . '" height="' . $targetHeight . '" style="width: ' . $targetWidth . 'px; height: ' . $targetHeight . 'px; border: none; margin-bottom: 20px;">';
            }
        }

        // Signature size
        $sigImgHtml = '<br><br><br>';
        if (!empty($sigUrl)) {
            $sigPath = dirname(__DIR__, 2) . '/storage/uploads/' . $certificate['signature'];
            if (file_exists($sigPath)) {
                $size = @getimagesize($sigPath);
                if ($size && $size[1] > 0) {
                    $ratio = $size[0] / $size[1];
                    $targetHeight = 90;
                    $targetWidth = round($targetHeight * $ratio);
                    $sigImgHtml = '<img src="' . $sigUrl . '" width="' . $targetWidth . '" height="' . $targetHeight . '" style="border: none;">';
                }
            } else {
                $sigImgHtml = '<img src="' . $sigUrl . '" height="90" style="border: none;">';
            }
        }

        $vmlWatermark = '';
        if ($showWatermark && !empty($bgUrl)) {
            $bgWidth = 450;
            $bgHeight = 450;
            $bgPath = !empty($certificate['background_image']) ? dirname(__DIR__, 2) . '/storage/uploads/' . $certificate['background_image'] : dirname(__DIR__, 2) . '/public/assets/img/centexs-logo-with-outline-1.png';
            if (file_exists($bgPath)) {
                $size = @getimagesize($bgPath);
                if ($size && $size[1] > 0) {
                    $ratio = $size[0] / $size[1];
                    $bgHeight = 450;
                    $bgWidth = round($bgHeight * $ratio);
                }
            }
            $vmlWatermark = '
            <!--[if gte vml 1]>
            <w:pict>
                <v:rect id="watermark"
                    style="position:absolute;z-index:-1;width:' . $bgWidth . 'pt;height:' . $bgHeight . 'pt;
                    mso-position-horizontal:center;mso-position-horizontal-relative:margin;
                    mso-position-vertical:center;mso-position-vertical-relative:margin;"
                    stroked="f">
                    <v:fill src="' . $bgUrl . '" type="frame" opacity="0.15"/>
                </v:rect>
            </w:pict>
            <![endif]-->';
        }

        return '
<html xmlns:v="urn:schemas-microsoft-com:vml"
xmlns:o="urn:schemas-microsoft-com:office:office"
xmlns:w="urn:schemas-microsoft-com:office:word"
xmlns:m="http://schemas-microsoft.com/office/2004/12/omml"
xmlns="http://www.w3.org/TR/REC-html40">
<head>
<meta http-equiv=Content-Type content="text/html; charset=utf-8">
<meta name=ProgId content=Word.Document>
<meta name=Generator content="Microsoft Word 15">
<meta name=Originator content="Microsoft Word 15">
<!--[if gte mso 9]><xml>
 <w:WordDocument>
  <w:View>Print</w:View>
  <w:Zoom>100</w:Zoom>
  <w:DoNotOptimizeForBrowser/>
 </w:WordDocument>
</xml><![endif]-->
<style>
body { font-family: "Arial", sans-serif; color: ' . $textColor . '; text-align: center; background-color: white; }
p { margin: 0; padding: 0; }
</style>
</head>
<body style="text-align: center;">
    ' . $vmlWatermark . '
    <div style="text-align: center; margin-top: 20px;">
        ' . $logoImg . '
        
        <p style="font-size: 38pt; font-weight: bold; margin-top: 10px;">CERTIFICATE</p>
        <p style="font-size: 20pt; font-weight: bold; margin-bottom: 20px;">OF COMPLETION</p>
        
        <p style="font-size: 14pt; font-weight: bold; margin-bottom: 20px;">' . $intro . '</p>
        
        <p style="font-size: 22pt; font-weight: bold; text-transform: uppercase; margin-bottom: 20px;">' . $name . '</p>
        
        <p style="font-size: 16pt; font-family: \'Brush Script MT\', \'Monotype Corsiva\', cursive; font-style: italic; margin-bottom: 20px;">' . $introScript . '</p>
        
        <p style="font-size: 18pt; font-weight: bold; text-transform: uppercase; margin-bottom: 5px;">' . $course . '</p>
        <p style="font-size: 12pt; font-weight: bold; margin-bottom: 20px;">(' . $formattedDate . ')</p>
        
        <div style="font-size: 11pt; line-height: 1.5; margin: 0 10%; margin-bottom: 40px;">' . $description . '</div>
        
        <table width="100%" border="0" cellpadding="0" cellspacing="0" style="margin-top: 40px;">
            <tr>
                <td width="25%" align="left" valign="bottom">
                    ' . ($showQr ? '
                    <img src="' . $qrCodeSrc . '" width="90" height="90" style="border: none;">
                    <p style="font-size: 8pt; font-weight: bold; margin-top: 5px;">Certificate No.</p>
                    <p style="font-size: 9pt; font-weight: bold;">' . $number . '</p>
                    ' : '') . '
                </td>
                <td width="50%" align="center" valign="bottom">
                    ' . $sigImgHtml . '
                    <div style="border-top: 1.5px solid black; width: 350px; margin: 5px auto 0 auto; padding-top: 5px;">
                        <p style="font-size: 11pt; font-weight: bold;">' . $signatoryName . '</p>
                        <p style="font-size: 10pt;">' . $signatoryTitle . '</p>
                        <p style="font-size: 9pt; color: #555;">' . $organization . '</p>
                    </div>
                </td>
                <td width="25%"></td>
            </tr>
        </table>
    </div>
</body>
</html>';
    }

    private function certificatePdfHtml(array $certificate): string
    {
        $number = htmlspecialchars((string) (!empty($certificate['certificate_number']) ? $certificate['certificate_number'] : (!empty($certificate['certificate_no']) ? $certificate['certificate_no'] : '')), ENT_QUOTES, 'UTF-8');
        $rawName = !empty($certificate['trainee_name']) ? $certificate['trainee_name'] : 'Participant Name';
        $name = htmlspecialchars((string) $rawName, ENT_QUOTES, 'UTF-8');
        $course = htmlspecialchars((string) (!empty($certificate['course_title']) ? $certificate['course_title'] : 'Course Title'), ENT_QUOTES, 'UTF-8');
        $date = htmlspecialchars((string) ($certificate['issue_date'] ?? $certificate['issued_at'] ?? date('Y-m-d')), ENT_QUOTES, 'UTF-8');
        $formattedDate = strtoupper(date('d F Y', strtotime($date)));

        $layout = [];
        if (!empty($certificate['layout_json'])) {
            $layout = json_decode((string) $certificate['layout_json'], true) ?: [];
        }

        $style = $layout['style'] ?? [];
        $title = htmlspecialchars((string) ($layout['title'] ?? 'CERTIFICATE'), ENT_QUOTES, 'UTF-8');
        $intro = htmlspecialchars((string) ($layout['intro'] ?? 'This is to certify that'), ENT_QUOTES, 'UTF-8');
        $introScript = htmlspecialchars((string) ($layout['intro_script'] ?? 'has successfully completed the training programme for :'), ENT_QUOTES, 'UTF-8');
        $description = htmlspecialchars((string) ($layout['description'] ?? 'Congratulations on your active participation in this program which have equipped you with valuable knowledge and skills on Artificial Intelligence (AI), Ethical Use of AI, Instructional Design Planning, Educational Data Analytics, AI for Visuals and Audio, and AI-Based Tasks.'), ENT_QUOTES, 'UTF-8');
        $signatoryName = htmlspecialchars((string) ($layout['signatory_name'] ?? 'Dato Haji Syeed Mohd Hussien Bin Wan Abd Rahman'), ENT_QUOTES, 'UTF-8');
        $signatoryTitle = htmlspecialchars((string) ($layout['signatory_title'] ?? 'Chief Executive Officer'), ENT_QUOTES, 'UTF-8');
        $organization = htmlspecialchars((string) ($layout['organization'] ?? 'Centre for Technology Excellence Sarawak'), ENT_QUOTES, 'UTF-8');
        $showWatermark = (bool) ($layout['show_watermark'] ?? true);
        $showQr = (bool) ($layout['show_qr'] ?? true);

        $textColor = htmlspecialchars((string) ($certificate['text_color'] ?? '#000000'), ENT_QUOTES, 'UTF-8');
        $accentColor = htmlspecialchars((string) ($style['accent_color'] ?? '#aa3338'), ENT_QUOTES, 'UTF-8');
        $patternOpacity = (float) ($style['pattern_opacity'] ?? 0.15);

        // Map web URLs for Dompdf rendering to bypass local directory chroot restrictions
        $logoUrl = !empty($certificate['logo']) ? APP_URL . '/storage/uploads/' . $certificate['logo'] : APP_URL . '/public/assets/img/centexs-logo-with-outline-1.png';
        $sigUrl = !empty($certificate['signature']) ? APP_URL . '/storage/uploads/' . $certificate['signature'] : '';
        $bgUrl = !empty($certificate['background_image']) ? APP_URL . '/storage/uploads/' . $certificate['background_image'] : APP_URL . '/public/assets/img/centexs-logo-with-outline-1.png';

        $verifyUrl = APP_URL . '/index.php?page=verify-certificate&code=' . urlencode((string) $certificate['verification_code']);
        $qrCodeSrc = 'https://api.qrserver.com/v1/create-qr-code/?size=120x120&data=' . urlencode($verifyUrl);

        $sigImageHtml = '';
        if (!empty($sigUrl)) {
            $sigImageHtml = '<img src="' . $sigUrl . '" style="height: 90px; display: block; margin: 0 auto; border: none;">';
        } else {
            $sigImageHtml = '<div style="height: 60px;"></div>'; // spacer if no signature
        }

        return '<!doctype html><html><head><meta charset="utf-8"><style>
            @page { size: A4 portrait; margin: 0; }
            html, body { margin: 0; padding: 0; background: #fff; font-family: DejaVu Sans, Helvetica, Arial, sans-serif; color: ' . $textColor . '; }
            
            .watermark { 
                position: absolute; 
                top: 75mm; 
                left: 35mm; 
                width: 140mm; 
                height: 140mm; 
                opacity: ' . $patternOpacity . '; 
                text-align: center; 
                z-index: -1; 
            }
            .watermark img { width: 100%; height: 100%; object-fit: contain; }
            
            .content {
                padding-top: 30mm;
                text-align: center;
                position: relative;
                z-index: 1;
            }
            
            .footer {
                position: absolute;
                bottom: 25mm;
                left: 15mm;
                width: 180mm;
                z-index: 2;
            }
        </style></head><body>
            
            ' . ($showWatermark ? '<div class="watermark"><img src="' . $bgUrl . '"></div>' : '') . '
            
            <div class="content">
                <img src="' . $logoUrl . '" style="height: 160px; display: inline-block; border: none; margin-bottom: 20px;">
                
                <div style="font-size: 52px; font-weight: 900; line-height: 1.1; margin: 0;">CERTIFICATE</div>
                <div style="font-size: 28px; font-weight: bold; margin: 0 0 20px 0;">OF COMPLETION</div>
                
                <div style="font-size: 16px; margin: 0 0 25px 0; font-weight: bold;">' . $intro . '</div>
                
                <div style="font-size: 26px; font-weight: bold; text-transform: uppercase; margin: 0 0 25px 0;">' . $name . '</div>
                
                <div style="font-size: 24px; margin: 0 0 20px 0; font-family: \'Brush Script MT\', \'Lucida Handwriting\', \'Monotype Corsiva\', \'Times New Roman\', serif; font-style: italic;">' . $introScript . '</div>
                
                <div style="font-size: 22px; font-weight: bold; text-transform: uppercase; margin: 0 0 5px 0;">' . $course . '</div>
                <div style="font-size: 16px; font-weight: bold; margin: 0 0 25px 0;">(' . $formattedDate . ')</div>
                
                <div style="font-size: 14px; line-height: 1.5; max-width: 85%; margin: 0 auto;">' . $description . '</div>
            </div>
            
            <div class="footer">
                <table style="width: 100%; border: none; border-collapse: collapse; margin: 0;">
                    <tr>
                        ' . ($showQr ? '<td style="width: 25%; text-align: left; vertical-align: bottom; padding: 0; border: none;">
                            <img src="' . $qrCodeSrc . '" style="width: 90px; height: 90px; display: block; border: none; margin-bottom: 5px;">
                            <div style="font-size: 10px; color: ' . $accentColor . '; font-weight: bold; margin-bottom: 2px;">Certificate No.</div>
                            <strong style="font-size: 11px; display: block;">' . $number . '</strong>
                        </td>' : '<td style="width: 25%; border: none;"></td>') . '
                        
                        <td style="width: 50%; text-align: center; vertical-align: bottom; padding: 0; border: none;">
                            <table style="margin: 0 auto; border: none; border-collapse: collapse; width: 350px; text-align: center;">
                                <tr>
                                    <td style="text-align: center; padding: 0 0 5px 0; border: none;">
                                        ' . $sigImageHtml . '
                                    </td>
                                </tr>
                                <tr>
                                    <td style="border: none; border-top: 1.5px solid #000; padding: 8px 0 0 0; text-align: center; line-height: 1.2;">
                                        <strong style="font-size: 14px; display: block; margin-bottom: 3px;">' . $signatoryName . '</strong>
                                        <span style="font-size: 12px; display: block; color: #333; margin-bottom: 2px;">' . $signatoryTitle . '</span>
                                        <span style="font-size: 11px; display: block; color: #555;">' . $organization . '</span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                        
                        ' . ($showQr ? '<td style="width: 25%; border: none;"></td>' : '') . '
                    </tr>
                </table>
            </div>
        </body></html>';
    }
}
