<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Activity;
use App\Core\Auth;
use App\Core\Security;
use App\Core\View;
use App\Models\Certificate;
use App\Models\Content;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Evaluation;
use App\Models\MasterData;
use App\Models\TraineeProfile;
use App\Models\User;

final class AdminController
{
    public function users(): void
    {
        Auth::requireRole(['admin']);
        $userModel = new User();
        $q = Security::cleanString($_GET['q'] ?? '');
        $role = Security::cleanString($_GET['role'] ?? '');
        $status = Security::cleanString($_GET['status'] ?? '');
        $page = max(1, (int) ($_GET['p'] ?? 1));
        $perPage = 10;
        $totalAll = $userModel->countAll($q, '', $status);
        $totalAdmin = $userModel->countAll($q, 'admin', $status);
        $totalInstructor = $userModel->countAll($q, 'instructor', $status);
        $totalTrainee = $userModel->countAll($q, 'trainee', $status);
        View::render('admin/users', [
            'users' => $userModel->all($q, $perPage, ($page - 1) * $perPage, $role, $status),
            'roles' => $userModel->roles(),
            'q' => $q,
            'role' => $role,
            'status' => $status,
            'pageNo' => $page,
            'totalPages' => max(1, (int) ceil($userModel->countAll($q, $role, $status) / $perPage)),
            'totalFiltered' => $userModel->countAll($q, $role, $status),
            'totalAll' => $totalAll,
            'totalAdmin' => $totalAdmin,
            'totalInstructor' => $totalInstructor,
            'totalTrainee' => $totalTrainee,
        ]);
    }

    public function userDetail(): void
    {
        Auth::requireRole(['admin']);
        $id = (int) ($_GET['id'] ?? 0);
        $user = (new User())->find($id);
        header('Content-Type: application/json');
        if (!$user) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'User not found']);
            exit;
        }
        echo json_encode(['status' => 'success', 'user' => $user]);
        exit;
    }

    public function saveUser(): void
    {
        Auth::requireRole(['admin']);
        Security::verifyCsrf();
        $id = (int) ($_POST['id'] ?? 0);
        $password = (string) ($_POST['password'] ?? '');
        if (!$id && strlen($password) < 8) {
            exit('New users require a password of at least 8 characters.');
        }
        (new User())->saveFromAdmin([
            'id' => $id,
            'role_slug' => Security::cleanString($_POST['role_slug'] ?? 'trainee'),
            'name' => Security::cleanString($_POST['name'] ?? ''),
            'email' => filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL) ?: '',
            'password' => $password,
            'phone' => Security::cleanString($_POST['phone'] ?? ''),
            'address' => Security::cleanString($_POST['address'] ?? '', 1000),
            'status' => Security::cleanString($_POST['status'] ?? 'pending'),
        ]);
        Activity::log('Saved user account');
        header('Location: index.php?page=admin-users');
    }

    public function deleteUser(): void
    {
        Auth::requireRole(['admin']);
        Security::verifyCsrf();
        $id = (int) ($_POST['id'] ?? 0);
        if ($id && $id !== Auth::id()) {
            (new User())->delete($id);
            Activity::log('Deleted user account');
        }
        header('Location: index.php?page=admin-users');
    }

    public function setUserStatus(): void
    {
        Auth::requireRole(['admin']);
        Security::verifyCsrf();
        (new User())->setStatus((int) $_POST['id'], Security::cleanString($_POST['status']));
        Activity::log('Updated user status');
        header('Location: index.php?page=admin-users');
    }

    public function courses(): void
    {
        Auth::requireRole(['admin']);
        $courseModel = new Course();
        $q = Security::cleanString($_GET['q'] ?? '');
        $status = Security::cleanString($_GET['status'] ?? '');
        $category = Security::cleanString($_GET['category'] ?? '');
        
        $db = \App\Core\Model::getDb();
        $totalAll = (int) $db->query('SELECT COUNT(*) FROM courses')->fetchColumn();
        $totalActive = (int) $db->query('SELECT COUNT(*) FROM courses WHERE status = "active"')->fetchColumn();
        $totalDraft = (int) $db->query('SELECT COUNT(*) FROM courses WHERE status = "draft"')->fetchColumn();
        $totalArchived = (int) $db->query('SELECT COUNT(*) FROM courses WHERE status = "archived"')->fetchColumn();

        $totalAdgea = 0;
        $totalIesga = 0;
        try {
            $totalAdgea = (int) $db->query('SELECT COUNT(*) FROM courses WHERE academy_id = (SELECT id FROM academies WHERE code = "ADGEA")')->fetchColumn();
            $totalIesga = (int) $db->query('SELECT COUNT(*) FROM courses WHERE academy_id = (SELECT id FROM academies WHERE code = "IESGA")')->fetchColumn();
        } catch (\Exception $e) {}

        View::render('admin/courses', [
            'courses' => $courseModel->all($q, $status, $category),
            'instructors' => $courseModel->instructors(),
            'categories' => $courseModel->categories(),
            'editing' => isset($_GET['edit']) ? $courseModel->find((int) $_GET['edit']) : null,
            'q' => $q,
            'status' => $status,
            'category' => $category,
            'totalAll' => $totalAll,
            'totalActive' => $totalActive,
            'totalDraft' => $totalDraft,
            'totalArchived' => $totalArchived,
            'totalAdgea' => $totalAdgea,
            'totalIesga' => $totalIesga,
        ]);
    }

    public function courseDetail(): void
    {
        Auth::requireRole(['admin']);
        $id = (int) ($_GET['id'] ?? 0);
        $course = (new Course())->find($id);
        header('Content-Type: application/json');
        if (!$course) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Course not found']);
            exit;
        }
        
        // Add participant count
        $db = \App\Core\Model::getDb();
        $participantCount = (int) $db->query('SELECT COUNT(*) FROM enrolments WHERE course_id = ' . $id . ' AND status IN ("active","completed")')->fetchColumn();
        $course['participant_count'] = $participantCount;
        
        echo json_encode(['status' => 'success', 'course' => $course]);
        exit;
    }

    public function saveCourse(): void
    {
        Auth::requireRole(['admin', 'instructor']);
        Security::verifyCsrf();
        $thumbnail = Security::cleanString($_POST['existing_thumbnail'] ?? '');
        if (!empty($_FILES['thumbnail']['name'])) {
            $uploaded = Security::validateUpload($_FILES['thumbnail'], ['jpg', 'jpeg', 'png', 'webp']);
            if ($uploaded) {
                move_uploaded_file($_FILES['thumbnail']['tmp_name'], UPLOAD_PATH . '/' . $uploaded);
                $thumbnail = $uploaded;
            }
        }
        $capacity = (int) ($_POST['capacity'] ?? 0);
        $instructorId = Auth::role() === 'instructor' ? (int) Auth::id() : (int) ($_POST['instructor_id'] ?? 0);
        
        (new Course())->save([
            'id' => (int) ($_POST['id'] ?? 0),
            'title' => Security::cleanString($_POST['title'] ?? ''),
            'category' => Security::cleanString($_POST['category'] ?? ''),
            'description' => Security::cleanString($_POST['description'] ?? '', 3000),
            'start_date' => $_POST['start_date'] ?: null,
            'end_date' => $_POST['end_date'] ?: null,
            'capacity' => $capacity,
            'max_participants' => (int) ($_POST['max_participants'] ?? $capacity),
            'status' => Security::cleanString($_POST['status'] ?? 'draft'),
            'course_status' => Security::cleanString($_POST['course_status'] ?? ($_POST['status'] ?? 'draft')),
            'thumbnail_image' => $thumbnail ?: null,
            'instructor_id' => $instructorId ?: null,
            'fee' => (float) ($_POST['fee'] ?? 0),
            'created_by' => Auth::id(),
            'academy_id' => (int) ($_POST['academy_id'] ?? 0) ?: null,
        ]);
        Activity::log('Saved course');
        
        if (Auth::role() === 'instructor') {
            header('Location: index.php?page=instructor-dashboard');
        } else {
            header('Location: index.php?page=admin-courses');
        }
    }

    public function deleteCourse(): void
    {
        Auth::requireRole(['admin']);
        Security::verifyCsrf();
        (new Course())->delete((int) ($_POST['id'] ?? 0));
        Activity::log('Deleted course');
        header('Location: index.php?page=admin-courses');
    }

    public function enrolments(): void
    {
        Auth::requireRole(['admin']);
        $db = \App\Core\Model::getDb();
        $totalAll = (int) $db->query('SELECT COUNT(*) FROM enrolments')->fetchColumn();
        $totalPending = (int) $db->query('SELECT COUNT(*) FROM enrolments WHERE status = "pending"')->fetchColumn();
        $totalActive = (int) $db->query('SELECT COUNT(*) FROM enrolments WHERE status = "active"')->fetchColumn();
        $totalCompleted = (int) $db->query('SELECT COUNT(*) FROM enrolments WHERE status = "completed"')->fetchColumn();
        $totalRejected = (int) $db->query('SELECT COUNT(*) FROM enrolments WHERE status = "rejected"')->fetchColumn();

        View::render('admin/enrolments', [
            'enrolments' => (new Enrollment())->allEnrolments(),
            'totalAll' => $totalAll,
            'totalPending' => $totalPending,
            'totalActive' => $totalActive,
            'totalCompleted' => $totalCompleted,
            'totalRejected' => $totalRejected,
        ]);
    }

    public function enrolmentDetail(): void
    {
        Auth::requireRole(['admin']);
        $id = (int) ($_GET['id'] ?? 0);
        
        $db = \App\Core\Model::getDb();
        $sql = 'SELECT e.*, c.title AS course_title, c.category AS course_category, c.description AS course_description, c.start_date AS course_start, c.end_date AS course_end, c.capacity AS course_capacity, c.max_participants AS course_max, c.fee AS course_fee, u.name AS trainee_name, u.email AS trainee_email, u.phone AS trainee_phone, u.address AS trainee_address, instr.name AS instructor_name FROM enrolments e JOIN courses c ON c.id = e.course_id JOIN users u ON u.id = e.trainee_id LEFT JOIN users instr ON instr.id = c.instructor_id WHERE e.id = ?';
        $stmt = $db->prepare($sql);
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        
        header('Content-Type: application/json');
        if (!$row) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Enrolment not found']);
            exit;
        }
        
        // Fetch current occupancy
        $participantCount = (int) $db->query('SELECT COUNT(*) FROM enrolments WHERE course_id = ' . (int) $row['course_id'] . ' AND status IN ("active","completed")')->fetchColumn();
        $row['course_occupancy'] = $participantCount;
        
        echo json_encode(['status' => 'success', 'enrolment' => $row]);
        exit;
    }

    public function setEnrolmentStatus(): void
    {
        Auth::requireRole(['admin']);
        Security::verifyCsrf();
        (new Enrollment())->setStatus((int) $_POST['id'], Security::cleanString($_POST['status']));
        Activity::log('Updated enrolment status');
        header('Location: index.php?page=admin-enrolments');
    }

    /** Instructor enrollment management */
    public function instructorEnrolments(): void
    {
        Auth::requireRole(['instructor']);
        View::render('instructor/enrolments', [
            'enrolments' => (new Enrollment())->forInstructor((int) Auth::id()),
        ]);
    }

    public function setInstructorEnrolmentStatus(): void
    {
        Auth::requireRole(['instructor']);
        Security::verifyCsrf();
        $enrolmentId = (int) $_POST['id'];
        $enrollment = new Enrollment();
        if (!$enrollment->belongsToInstructor($enrolmentId, (int) Auth::id())) {
            exit('Unauthorized.');
        }
        $enrollment->setStatus($enrolmentId, Security::cleanString($_POST['status']));
        Activity::log('Instructor updated enrolment status');
        header('Location: index.php?page=instructor-enrolments');
    }

    /** Website settings */
    public function websiteSettings(): void
    {
        Auth::requireRole(['admin']);
        $db = \App\Core\Model::getDb();
        $settings = [];
        foreach ($db->query('SELECT setting_key, setting_value FROM website_settings')->fetchAll() as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        $successStories = [];
        try { $successStories = $db->query('SELECT * FROM success_stories ORDER BY sort_order, id')->fetchAll(); } catch (\Exception $e) {}
        $intakes = [];
        try { $intakes = $db->query('SELECT i.*, a.code AS academy_code FROM upcoming_intakes i LEFT JOIN academies a ON a.id = i.academy_id ORDER BY intake_date')->fetchAll(); } catch (\Exception $e) {}
        $academies = [];
        try { $academies = $db->query('SELECT * FROM academies ORDER BY code')->fetchAll(); } catch (\Exception $e) {}
        View::render('admin/website-settings', [
            'settings' => $settings,
            'successStories' => $successStories,
            'intakes' => $intakes,
            'academies' => $academies,
        ]);
    }

    public function saveWebsiteSettings(): void
    {
        Auth::requireRole(['admin']);
        Security::verifyCsrf();
        $db = \App\Core\Model::getDb();
        $keys = ['hero_title', 'hero_subtitle', 'footer_about', 'footer_address', 'footer_phone', 'footer_email', 'footer_social_facebook', 'footer_social_linkedin', 'show_upcoming_intakes', 'show_success_stories', 'show_announcements_home'];
        foreach ($keys as $key) {
            if (isset($_POST[$key])) {
                $stmt = $db->prepare('INSERT INTO website_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)');
                $stmt->execute([$key, Security::cleanString($_POST[$key], 2000)]);
            }
        }
        Activity::log('Updated website settings');
        header('Location: index.php?page=admin-website-settings');
    }

    public function saveSuccessStory(): void
    {
        Auth::requireRole(['admin']);
        Security::verifyCsrf();
        $db = \App\Core\Model::getDb();
        $id = (int) ($_POST['id'] ?? 0);
        if ($id) {
            $stmt = $db->prepare('UPDATE success_stories SET trainee_name=?, course_title=?, quote=?, is_active=?, sort_order=? WHERE id=?');
            $stmt->execute([Security::cleanString($_POST['trainee_name']), Security::cleanString($_POST['course_title']), Security::cleanString($_POST['quote'] ?? '', 2000), isset($_POST['is_active']) ? 1 : 0, (int) ($_POST['sort_order'] ?? 0), $id]);
        } else {
            $stmt = $db->prepare('INSERT INTO success_stories (trainee_name, course_title, quote, is_active, sort_order) VALUES (?, ?, ?, ?, ?)');
            $stmt->execute([Security::cleanString($_POST['trainee_name']), Security::cleanString($_POST['course_title']), Security::cleanString($_POST['quote'] ?? '', 2000), isset($_POST['is_active']) ? 1 : 0, (int) ($_POST['sort_order'] ?? 0)]);
        }
        Activity::log('Saved success story');
        header('Location: index.php?page=admin-website-settings');
    }

    public function deleteSuccessStory(): void
    {
        Auth::requireRole(['admin']);
        Security::verifyCsrf();
        $db = \App\Core\Model::getDb();
        $stmt = $db->prepare('DELETE FROM success_stories WHERE id = ?');
        $stmt->execute([(int) ($_POST['id'] ?? 0)]);
        Activity::log('Deleted success story');
        header('Location: index.php?page=admin-website-settings');
    }

    public function saveIntake(): void
    {
        Auth::requireRole(['admin']);
        Security::verifyCsrf();
        $db = \App\Core\Model::getDb();
        $id = (int) ($_POST['id'] ?? 0);
        if ($id) {
            $stmt = $db->prepare('UPDATE upcoming_intakes SET academy_id=?, intake_title=?, intake_date=?, description=?, is_active=? WHERE id=?');
            $stmt->execute([(int) ($_POST['academy_id'] ?? 0) ?: null, Security::cleanString($_POST['intake_title']), $_POST['intake_date'], Security::cleanString($_POST['description'] ?? '', 2000), isset($_POST['is_active']) ? 1 : 0, $id]);
        } else {
            $stmt = $db->prepare('INSERT INTO upcoming_intakes (academy_id, intake_title, intake_date, description, is_active) VALUES (?, ?, ?, ?, ?)');
            $stmt->execute([(int) ($_POST['academy_id'] ?? 0) ?: null, Security::cleanString($_POST['intake_title']), $_POST['intake_date'], Security::cleanString($_POST['description'] ?? '', 2000), isset($_POST['is_active']) ? 1 : 0]);
        }
        Activity::log('Saved upcoming intake');
        header('Location: index.php?page=admin-website-settings');
    }

    public function deleteIntake(): void
    {
        Auth::requireRole(['admin']);
        Security::verifyCsrf();
        $db = \App\Core\Model::getDb();
        $stmt = $db->prepare('DELETE FROM upcoming_intakes WHERE id = ?');
        $stmt->execute([(int) ($_POST['id'] ?? 0)]);
        Activity::log('Deleted upcoming intake');
        header('Location: index.php?page=admin-website-settings');
    }

    public function saveProfilePicture(): void
    {
        Auth::requireLogin();
        Security::verifyCsrf();
        if (!empty($_FILES['profile_picture']['name'])) {
            $uploaded = Security::validateUpload($_FILES['profile_picture'], ['jpg', 'jpeg', 'png', 'webp']);
            if ($uploaded) {
                move_uploaded_file($_FILES['profile_picture']['tmp_name'], UPLOAD_PATH . '/' . $uploaded);
                $db = \App\Core\Model::getDb();
                $stmt = $db->prepare('UPDATE users SET profile_picture = ?, updated_at = NOW() WHERE id = ?');
                $stmt->execute([$uploaded, Auth::id()]);
                Activity::log('Updated profile picture');
            }
        }
        header('Location: index.php?page=profile');
    }

    public function announcements(): void
    {
        Auth::requireRole(['admin', 'instructor']);
        $role = Auth::role();
        $userId = (int) Auth::id();
        View::render('admin/announcements', ['announcements' => (new Content())->announcements(false, $role, $userId)]);
    }

    public function saveAnnouncement(): void
    {
        Auth::requireRole(['admin', 'instructor']);
        Security::verifyCsrf();
        $isPublic = Auth::role() === 'admin' ? (isset($_POST['is_public']) ? 1 : 0) : 0;
        (new Content())->addAnnouncement([
            'title' => Security::cleanString($_POST['title'] ?? ''),
            'body' => Security::cleanString($_POST['body'] ?? '', 4000),
            'is_public' => $isPublic,
            'created_by' => Auth::id(),
        ]);
        Activity::log('Published announcement');
        header('Location: index.php?page=announcements-manage');
    }

    public function certificates(): void
    {
        Auth::requireRole(['admin']);
        $certificateModel = new Certificate();

        // AJAX handler for completed trainees without certificates
        if (isset($_GET['get_completed_course_id'])) {
            $courseId = (int) $_GET['get_completed_course_id'];
            $db = \App\Core\Model::getDb();
            $stmt = $db->prepare('SELECT u.id, u.name, u.email FROM enrolments e JOIN users u ON u.id = e.trainee_id WHERE e.course_id = ? AND e.status = "completed" AND e.attendance_requirement_met = 1 AND e.assessments_completed = 1 AND e.evaluation_submitted = 1 AND u.id NOT IN (SELECT trainee_id FROM certificates WHERE course_id = ? AND status != "revoked")');
            $stmt->execute([$courseId, $courseId]);
            header('Content-Type: application/json');
            echo json_encode($stmt->fetchAll());
            exit;
        }

        // Auto-seed default CENTEXS Sarawak template if table is empty
        $templates = $certificateModel->templates();
        if (empty($templates)) {
            $sarawakLayout = json_encode([
                'style' => [
                    'template' => 'centexs_sarawak',
                    'title_color' => '#000000',
                    'accent_color' => '#aa3338',
                    'border_color' => '#aa3338',
                    'seal_color' => '#b53638',
                    'pattern_opacity' => 0.05,
                ],
                'show_seal' => false,
                'show_verification' => true,
                'show_watermark' => true,
                'show_qr' => true,
                'title' => 'CERTIFICATE OF COMPLETION',
                'intro' => 'This is to certify that',
                'intro_script' => 'has successfully completed the training programme for :',
                'description' => 'Congratulations on your active participation in this program which have equipped you with valuable knowledge and skills on Artificial Intelligence (AI), Ethical Use of AI, Instructional Design Planning, Educational Data Analytics, AI for Visuals and Audio, and AI-Based Tasks.',
                'signatory_name' => 'Dato Haji Syeed Mohd Hussien Bin Wan Abd Rahman',
                'signatory_title' => 'Chief Executive Officer',
                'organization' => 'Centre for Technology Excellence Sarawak',
                'script_font' => 'Playball'
            ], JSON_PRETTY_PRINT);

            $certificateModel->saveTemplate([
                'template_name' => 'CENTEXS Sarawak Certificate of Completion',
                'background_image' => null,
                'logo' => null,
                'signature' => null,
                'font_family' => 'Inter, sans-serif',
                'font_size' => 28,
                'text_color' => '#182230',
                'layout_json' => $sarawakLayout,
                'status' => 'active',
            ]);
            $templates = $certificateModel->templates();
        }

        View::render('admin/certificates', [
            'certificates' => $certificateModel->records(Security::cleanString($_GET['q'] ?? '')),
            'templates' => $templates,
            'activeTemplates' => $certificateModel->activeTemplates(),
            'courses' => (new Course())->all(),
            'trainees' => (new User())->all('', 200, 0, 'trainee', 'active'),
            'editingTemplate' => isset($_GET['template']) ? $certificateModel->template((int) $_GET['template']) : null,
            'q' => Security::cleanString($_GET['q'] ?? ''),
        ]);
    }

    public function saveCertificateTemplate(): void
    {
        Auth::requireRole(['admin']);
        Security::verifyCsrf();
        $certificateModel = new Certificate();
        $existing = !empty($_POST['template_id']) ? $certificateModel->template((int) $_POST['template_id']) : [];
        $background = $this->storeOptionalUpload('background_image', ['jpg', 'jpeg', 'png', 'webp']) ?: ($existing['background_image'] ?? null);
        $logo = $this->storeOptionalUpload('logo', ['jpg', 'jpeg', 'png', 'webp']) ?: ($existing['logo'] ?? null);
        $signature = $this->storeOptionalUpload('signature', ['jpg', 'jpeg', 'png', 'webp']) ?: ($existing['signature'] ?? null);
        $certificateModel->saveTemplate([
            'template_id' => (int) ($_POST['template_id'] ?? 0),
            'template_name' => Security::cleanString($_POST['template_name'] ?? ''),
            'background_image' => $background,
            'logo' => $logo,
            'signature' => $signature,
            'font_family' => Security::cleanString($_POST['font_family'] ?? 'Arial'),
            'font_size' => (int) ($_POST['font_size'] ?? 28),
            'text_color' => Security::cleanString($_POST['text_color'] ?? '#182230'),
            'layout_json' => Security::cleanString($_POST['layout_json'] ?? '{}', 4000),
            'status' => Security::cleanString($_POST['status'] ?? 'active'),
        ]);
        Activity::log('Saved certificate template');
        header('Location: index.php?page=admin-certificates');
    }

    public function deleteCertificateTemplate(): void
    {
        Auth::requireRole(['admin']);
        Security::verifyCsrf();
        (new Certificate())->deleteTemplate((int) ($_POST['template_id'] ?? 0));
        Activity::log('Deleted certificate template');
        header('Location: index.php?page=admin-certificates');
    }

    public function issueCertificate(): void
    {
        Auth::requireRole(['admin']);
        Security::verifyCsrf();
        (new Certificate())->issue([
            'course_id' => (int) ($_POST['course_id'] ?? 0),
            'trainee_id' => (int) ($_POST['trainee_id'] ?? 0),
            'template_id' => (int) ($_POST['template_id'] ?? 0),
            'certificate_number' => Security::cleanString($_POST['certificate_number'] ?? ''),
            'verification_code' => Security::cleanString($_POST['verification_code'] ?? ''),
            'pdf_path' => Security::cleanString($_POST['pdf_path'] ?? ''),
            'issue_date' => $_POST['issue_date'] ?: date('Y-m-d'),
            'issued_by' => Auth::id(),
            'status' => Security::cleanString($_POST['status'] ?? 'issued'),
        ]);
        Activity::log('Issued certificate');
        header('Location: index.php?page=admin-certificates');
    }

    public function reviewCertificate(): void
    {
        Auth::requireRole(['admin']);
        Security::verifyCsrf();
        $status = Security::cleanString($_POST['approval_status'] ?? 'pending');
        if (!in_array($status, ['approved', 'rejected'], true)) {
            header('Location: index.php?page=admin-certificates');
            return;
        }
        (new Certificate())->approve((int) ($_POST['id'] ?? 0), (int) Auth::id(), $status, Security::cleanString($_POST['remarks'] ?? '', 1000));
        Activity::log(ucfirst($status) . ' certificate');
        header('Location: index.php?page=admin-certificates');
    }

    public function certificateLogs(): void
    {
        Auth::requireRole(['admin']);
        $id = (int) ($_GET['id'] ?? 0);
        $certificateModel = new Certificate();
        $logs = $certificateModel->getDownloadLogs($id);
        header('Content-Type: application/json');
        echo json_encode(['status' => 'success', 'logs' => $logs]);
        exit;
    }

    public function bulkIssueCertificates(): void
    {
        Auth::requireRole(['admin']);
        Security::verifyCsrf();
        $courseId = (int) ($_POST['course_id'] ?? 0);
        $templateId = (int) ($_POST['template_id'] ?? 0);
        $traineeIds = $_POST['trainee_ids'] ?? [];

        if ($courseId && !empty($traineeIds)) {
            $certificateModel = new Certificate();
            $db = \App\Core\Model::getDb();
            try {
                $db->beginTransaction();
                foreach ($traineeIds as $traineeId) {
                    $certificateModel->issue([
                        'course_id' => $courseId,
                        'trainee_id' => (int) $traineeId,
                        'template_id' => $templateId,
                        'certificate_number' => '',
                        'verification_code' => '',
                        'pdf_path' => '',
                        'issue_date' => date('Y-m-d'),
                        'issued_by' => Auth::id(),
                        'status' => 'issued',
                    ]);
                }
                $db->commit();
                Activity::log('Bulk issued certificates for course ID ' . $courseId);
            } catch (\Exception $e) {
                $db->rollBack();
            }
        }
        header('Location: index.php?page=admin-certificates');
    }

    public function revokeCertificate(): void
    {
        Auth::requireRole(['admin']);
        Security::verifyCsrf();
        $id = (int) ($_POST['id'] ?? 0);
        if ($id) {
            (new Certificate())->revoke($id);
            Activity::log('Revoked certificate ID ' . $id);
        }
        header('Location: index.php?page=admin-certificates');
    }

    public function documentation(): void
    {
        Auth::requireRole(['admin']);
        $profileModel = new TraineeProfile();
        $traineeId = (int) ($_GET['trainee_id'] ?? 0);
        View::render('admin/documentation', [
            'trainees' => $profileModel->adminList(Security::cleanString($_GET['q'] ?? '')),
            'selected' => $traineeId ? $profileModel->adminDetail($traineeId) : null,
            'documents' => $traineeId ? $profileModel->documents($traineeId) : [],
            'q' => Security::cleanString($_GET['q'] ?? ''),
        ]);
    }

    public function exportDocumentation(): void
    {
        Auth::requireRole(['admin']);
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="trainee-documentation.csv"');
        $out = fopen('php://output', 'w');
        fputcsv($out, ['Name', 'Email', 'Phone', 'Identity Number', 'Education', 'Employment', 'Emergency Contact', 'Documents']);
        foreach ((new TraineeProfile())->adminList(Security::cleanString($_GET['q'] ?? '')) as $row) {
            fputcsv($out, [$row['name'], $row['email'], $row['phone'], $row['identity_number'], $row['education'], $row['employment'], $row['emergency_contact'], $row['document_count']]);
        }
        fclose($out);
    }

    public function evaluations(): void
    {
        Auth::requireRole(['admin']);
        $db = \App\Core\Model::getDb();
        $stats = $db->query('SELECT COUNT(*) AS total_count, AVG(COALESCE(course_rating, rating)) AS avg_course, AVG(instructor_rating) AS avg_instructor FROM evaluations')->fetch();
        View::render('admin/evaluations', [
            'evaluations' => (new Evaluation())->reports(),
            'totalCount' => (int) ($stats['total_count'] ?? 0),
            'avgCourse' => (float) ($stats['avg_course'] ?? 0),
            'avgInstructor' => (float) ($stats['avg_instructor'] ?? 0),
        ]);
    }

    public function masterData(): void
    {
        Auth::requireRole(['admin']);
        $masterData = new MasterData();
        $table = Security::cleanString($_GET['table'] ?? 'academies');
        $tables = $masterData->tables();
        if (!isset($tables[$table])) {
            $table = 'academies';
        }
        $editId = (int) ($_GET['edit'] ?? 0);
        View::render('admin/master-data', [
            'tables' => $tables,
            'table' => $table,
            'rows' => $masterData->list($table),
            'editing' => $editId ? $masterData->find($table, $editId) : null,
            'locations' => $masterData->list('locations'),
            'academies' => $masterData->list('academies'),
            'courses' => (new Course())->all(),
            'statistics' => $masterData->statistics(),
        ]);
    }

    public function saveMasterData(): void
    {
        Auth::requireRole(['admin']);
        Security::verifyCsrf();
        $table = Security::cleanString($_POST['table'] ?? 'academies');
        (new MasterData())->save($table, [
            'id' => (int) ($_POST['id'] ?? 0),
            'code' => Security::cleanString($_POST['code'] ?? ''),
            'name' => Security::cleanString($_POST['name'] ?? ''),
            'description' => Security::cleanString($_POST['description'] ?? '', 2000),
            'location_id' => (int) ($_POST['location_id'] ?? 0) ?: null,
            'status' => Security::cleanString($_POST['status'] ?? 'active'),
        ]);
        Activity::log('Saved ITOP master data');
        header('Location: index.php?page=admin-master-data&table=' . urlencode($table));
    }

    public function deleteMasterData(): void
    {
        Auth::requireRole(['admin']);
        Security::verifyCsrf();
        $table = Security::cleanString($_POST['table'] ?? 'academies');
        (new MasterData())->delete($table, (int) ($_POST['id'] ?? 0));
        Activity::log('Deleted ITOP master data');
        header('Location: index.php?page=admin-master-data&table=' . urlencode($table));
    }

    public function saveTrainingStatistic(): void
    {
        Auth::requireRole(['admin']);
        Security::verifyCsrf();
        (new MasterData())->saveTrainingStatistic([
            'id' => (int) ($_POST['id'] ?? 0),
            'academy_id' => (int) ($_POST['academy_id'] ?? 0),
            'course_id' => (int) ($_POST['course_id'] ?? 0),
            'course_name' => Security::cleanString($_POST['course_name'] ?? ''),
            'participants' => (int) ($_POST['participants'] ?? 0),
        ]);
        Activity::log('Saved ITOP training statistic');
        header('Location: index.php?page=admin-master-data&table=training_statistics');
    }

    public function deleteTrainingStatistic(): void
    {
        Auth::requireRole(['admin']);
        Security::verifyCsrf();
        (new MasterData())->deleteTrainingStatistic((int) ($_POST['id'] ?? 0));
        Activity::log('Deleted ITOP training statistic');
        header('Location: index.php?page=admin-master-data&table=training_statistics');
    }

    private function storeOptionalUpload(string $field, array $extensions): ?string
    {
        if (empty($_FILES[$field]['name'])) {
            return null;
        }
        $filename = Security::validateUpload($_FILES[$field], $extensions);
        if (!$filename) {
            return null;
        }
        move_uploaded_file($_FILES[$field]['tmp_name'], UPLOAD_PATH . '/' . $filename);
        return $filename;
    }

    public function claimCourse(): void
    {
        Auth::requireRole(['instructor']);
        Security::verifyCsrf();
        $courseId = (int) ($_POST['course_id'] ?? 0);
        if ($courseId) {
            $db = \App\Core\Model::getDb();
            $stmt = $db->prepare('UPDATE courses SET instructor_id = ? WHERE id = ? AND instructor_id IS NULL');
            $stmt->execute([Auth::id(), $courseId]);
            Activity::log('Instructor claimed course');
        }
        header('Location: index.php?page=instructor-dashboard');
    }

    public function unassignCourse(): void
    {
        Auth::requireRole(['instructor']);
        Security::verifyCsrf();
        $courseId = (int) ($_POST['course_id'] ?? 0);
        if ($courseId) {
            $db = \App\Core\Model::getDb();
            $stmt = $db->prepare('UPDATE courses SET instructor_id = NULL WHERE id = ? AND instructor_id = ?');
            $stmt->execute([$courseId, Auth::id()]);
            Activity::log('Instructor unassigned from course');
        }
        header('Location: index.php?page=instructor-dashboard');
    }

    public function fetchAnalyticsDetails(): void
    {
        Auth::requireRole(['admin']);
        $chartKey = Security::cleanString($_GET['chart_key'] ?? '');
        $db = \App\Core\Model::getDb();
        $data = [];

        // Fetch lookup dictionaries for adding new rows
        $academies = $db->query('SELECT id, code, name FROM academies ORDER BY name')->fetchAll();
        $categories = $db->query('SELECT id, name FROM training_categories ORDER BY name')->fetchAll();
        $companies = $db->query('SELECT id, name FROM companies ORDER BY name')->fetchAll();
        $professions = $db->query('SELECT id, name FROM professions ORDER BY name')->fetchAll();

        if (strpos($chartKey, 'custom_manual_') === 0) {
            $customId = (int) str_replace('custom_manual_', '', $chartKey);
            $stmt = $db->prepare("SELECT id, label, value, 'custom_analytics_data' AS source_table FROM custom_analytics_data WHERE custom_analytic_id = ?");
            $stmt->execute([$customId]);
            $data = $stmt->fetchAll();
        } else {
            switch ($chartKey) {
                case 'programme':
                    $data = $db->query('SELECT ts.id, CONCAT(a.code, " — ", ts.course_name) AS label, ts.participants AS value, "training_statistics" AS source_table FROM training_statistics ts JOIN academies a ON a.id = ts.academy_id ORDER BY ts.course_name')->fetchAll();
                    break;
                case 'course_participants':
                case 'popularity':
                    $data = $db->query('SELECT id, course_name AS label, participants AS value, "training_statistics" AS source_table FROM training_statistics ORDER BY course_name')->fetchAll();
                    break;
                case 'years':
                    $data = $db->query('SELECT id, report_year AS label, participants AS value, "yearly_reports" AS source_table FROM yearly_reports ORDER BY report_year')->fetchAll();
                    break;
                case 'categories':
                    $data = $db->query('SELECT ps.id, tc.name AS label, ps.participant_count AS value, "participant_statistics" AS source_table FROM participant_statistics ps JOIN training_categories tc ON tc.id = ps.category_id WHERE ps.statistic_type = "category" ORDER BY tc.name')->fetchAll();
                    break;
                case 'companies':
                    $data = $db->query('SELECT ps.id, co.name AS label, ps.participant_count AS value, "participant_statistics" AS source_table FROM participant_statistics ps JOIN companies co ON co.id = ps.company_id WHERE ps.statistic_type = "company" ORDER BY co.name')->fetchAll();
                    break;
                case 'professions':
                    $data = $db->query('SELECT ps.id, p.name AS label, ps.participant_count AS value, "participant_statistics" AS source_table FROM participant_statistics ps JOIN professions p ON p.id = ps.profession_id WHERE ps.statistic_type = "profession" ORDER BY p.name')->fetchAll();
                    break;
                case 'completion':
                    $data = $db->query('SELECT id, metric_label AS label, metric_value AS value, "dashboard_summary" AS source_table FROM dashboard_summary WHERE metric_key = "training_completion_rate"')->fetchAll();
                    break;
                case 'monthly':
                    $data = $db->query('SELECT "N/A" AS id, DATE_FORMAT(created_at, "%Y-%m") AS label, COUNT(*) AS value, "enrolments" AS source_table FROM enrolments GROUP BY DATE_FORMAT(created_at, "%Y-%m") ORDER BY label DESC LIMIT 12')->fetchAll();
                    break;
                case 'certificates':
                    $data = $db->query('SELECT "N/A" AS id, DATE_FORMAT(COALESCE(issue_date, issued_at), "%Y-%m") AS label, COUNT(*) AS value, "certificates" AS source_table FROM certificates GROUP BY DATE_FORMAT(COALESCE(issue_date, issued_at), "%Y-%m") ORDER BY label DESC LIMIT 12')->fetchAll();
                    break;
            }
        }

        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'success',
            'data' => $data,
            'lookups' => [
                'academies' => $academies,
                'categories' => $categories,
                'companies' => $companies,
                'professions' => $professions,
            ]
        ]);
        exit;
    }

    public function saveAnalyticsDetails(): void
    {
        Auth::requireRole(['admin']);
        Security::verifyCsrf();
        
        $raw = file_get_contents('php://input');
        $payload = json_decode($raw, true);
        $updates = $payload['updates'] ?? [];
        $newRows = $payload['new_rows'] ?? [];

        $db = \App\Core\Model::getDb();
        $db->beginTransaction();
        try {
            // Process updates
            foreach ($updates as $up) {
                $table = Security::cleanString($up['table'] ?? '');
                $id = (int) ($up['id'] ?? 0);
                $val = (int) ($up['value'] ?? 0);

                if ($table === 'training_statistics') {
                    $stmt = $db->prepare('UPDATE training_statistics SET participants = ? WHERE id = ?');
                    $stmt->execute([$val, $id]);
                } elseif ($table === 'yearly_reports') {
                    $stmt = $db->prepare('UPDATE yearly_reports SET participants = ? WHERE id = ?');
                    $stmt->execute([$val, $id]);
                } elseif ($table === 'participant_statistics') {
                    $stmt = $db->prepare('UPDATE participant_statistics SET participant_count = ? WHERE id = ?');
                    $stmt->execute([$val, $id]);
                } elseif ($table === 'dashboard_summary') {
                    $stmt = $db->prepare('UPDATE dashboard_summary SET metric_value = ? WHERE id = ?');
                    $stmt->execute([$val, $id]);
                } elseif ($table === 'custom_analytics_data') {
                    $stmt = $db->prepare('UPDATE custom_analytics_data SET value = ? WHERE id = ?');
                    $stmt->execute([$val, $id]);
                }
            }

            // Insert new rows
            foreach ($newRows as $nr) {
                $table = Security::cleanString($nr['table'] ?? '');
                $val = (int) ($nr['value'] ?? 0);

                if ($table === 'training_statistics') {
                    $academyId = (int) ($nr['academy_id'] ?? 0);
                    $courseName = Security::cleanString($nr['course_name'] ?? '');
                    if ($academyId && $courseName !== '') {
                        $stmt = $db->prepare('INSERT INTO training_statistics (academy_id, course_name, participants) VALUES (?, ?, ?)');
                        $stmt->execute([$academyId, $courseName, $val]);
                    }
                } elseif ($table === 'yearly_reports') {
                    $year = (int) ($nr['label'] ?? 0);
                    if ($year > 0) {
                        $stmt = $db->prepare('INSERT INTO yearly_reports (report_year, participants) VALUES (?, ?)');
                        $stmt->execute([$year, $val]);
                    }
                } elseif ($table === 'participant_statistics') {
                    $type = Security::cleanString($nr['type'] ?? '');
                    if ($type === 'category') {
                        $catId = (int) ($nr['category_id'] ?? 0);
                        if ($catId) {
                            $stmt = $db->prepare('INSERT INTO participant_statistics (statistic_type, category_id, participant_count) VALUES ("category", ?, ?)');
                            $stmt->execute([$catId, $val]);
                        }
                    } elseif ($type === 'company') {
                        $compId = (int) ($nr['company_id'] ?? 0);
                        if ($compId) {
                            $stmt = $db->prepare('INSERT INTO participant_statistics (statistic_type, company_id, participant_count) VALUES ("company", ?, ?)');
                            $stmt->execute([$compId, $val]);
                        }
                    } elseif ($type === 'profession') {
                        $profId = (int) ($nr['profession_id'] ?? 0);
                        if ($profId) {
                            $stmt = $db->prepare('INSERT INTO participant_statistics (statistic_type, profession_id, participant_count) VALUES ("profession", ?, ?)');
                            $stmt->execute([$profId, $val]);
                        }
                    }
                } elseif ($table === 'custom_analytics_data') {
                    $customId = (int) ($nr['custom_analytic_id'] ?? 0);
                    $label = Security::cleanString($nr['label'] ?? '');
                    if ($customId && $label !== '') {
                        $stmt = $db->prepare('INSERT INTO custom_analytics_data (custom_analytic_id, label, value) VALUES (?, ?, ?)');
                        $stmt->execute([$customId, $label, $val]);
                    }
                }
            }

            $db->commit();
            Activity::log('Updated dashboard analytics metrics manually');
            header('Content-Type: application/json');
            echo json_encode(['status' => 'success']);
        } catch (\Exception $e) {
            $db->rollBack();
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        exit;
    }

    public function createCustomChart(): void
    {
        Auth::requireRole(['admin']);
        Security::verifyCsrf();

        $raw = file_get_contents('php://input');
        $payload = json_decode($raw, true);
        $title = Security::cleanString($payload['title'] ?? '');
        $chartType = Security::cleanString($payload['chart_type'] ?? 'bar');
        $dataSource = Security::cleanString($payload['data_source'] ?? '');

        if ($title === '' || $dataSource === '') {
            header('Content-Type: application/json');
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Title and Data Source are required.']);
            exit;
        }

        $db = \App\Core\Model::getDb();
        try {
            $stmt = $db->prepare('INSERT INTO custom_analytics (title, chart_type, data_source) VALUES (?, ?, ?)');
            $stmt->execute([$title, $chartType, $dataSource]);
            $customId = $db->lastInsertId();

            Activity::log('Created new custom analytics chart: ' . $title);

            header('Content-Type: application/json');
            echo json_encode(['status' => 'success', 'id' => $customId]);
        } catch (\Exception $e) {
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        exit;
    }

    public function deleteCustomChart(): void
    {
        Auth::requireRole(['admin']);
        Security::verifyCsrf();

        $raw = file_get_contents('php://input');
        $payload = json_decode($raw, true);
        $id = (int) ($payload['id'] ?? 0);

        if (!$id) {
            header('Content-Type: application/json');
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Invalid Chart ID.']);
            exit;
        }

        $db = \App\Core\Model::getDb();
        try {
            $stmt = $db->prepare('DELETE FROM custom_analytics WHERE id = ?');
            $stmt->execute([$id]);

            Activity::log('Deleted custom analytics chart ID: ' . $id);

            header('Content-Type: application/json');
            echo json_encode(['status' => 'success']);
        } catch (\Exception $e) {
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        exit;
    }

    /* ── Analytics Full Page ──────────────────────────── */
    public function analytics(): void
    {
        Auth::requireRole(['admin']);
        $filters = [
            'academy_id' => $_GET['academy_id'] ?? '',
            'course_id' => $_GET['course_id'] ?? '',
            'instructor_id' => $_GET['instructor_id'] ?? '',
            'start_date' => $_GET['start_date'] ?? '',
            'end_date' => $_GET['end_date'] ?? '',
        ];

        $content = new Content();
        $analytics = $content->filteredAnalytics($filters);
        $stats = $content->stats();

        $db = \App\Core\Model::getDb();
        $academies = $db->query('SELECT id, code, name FROM academies ORDER BY name')->fetchAll();
        $courses = $db->query('SELECT id, title FROM courses ORDER BY title')->fetchAll();
        $instructors = $db->query('SELECT users.id, users.name FROM users JOIN roles ON roles.id = users.role_id WHERE roles.slug = "instructor" ORDER BY users.name')->fetchAll();

        View::render('admin/analytics', [
            'analytics' => $analytics,
            'stats' => $stats,
            'filters' => $filters,
            'academies' => $academies,
            'courses' => $courses,
            'instructors' => $instructors,
        ]);
    }

    /* ── Analytics Drill-Down Detail ──────────────────── */
    public function analyticsDetail(): void
    {
        Auth::requireRole(['admin']);
        $chartKey = Security::cleanString($_GET['chart'] ?? '');
        $filters = [
            'academy_id' => $_GET['academy_id'] ?? '',
            'course_id' => $_GET['course_id'] ?? '',
            'instructor_id' => $_GET['instructor_id'] ?? '',
            'start_date' => $_GET['start_date'] ?? '',
            'end_date' => $_GET['end_date'] ?? '',
        ];

        $content = new Content();
        $analytics = $content->filteredAnalytics($filters);
        $chartData = $analytics[$chartKey] ?? [];

        $titles = [
            'programme' => 'Participants by Academy',
            'monthly' => 'Monthly Registration Trend',
            'years' => 'Participants by Year',
            'completion' => 'Course Completion Rate',
            'certificates' => 'Certificate Issuance',
            'course_participants' => 'Participants by Course',
            'categories' => 'Participants by Category',
        ];

        View::render('admin/analytics-detail', [
            'chartKey' => $chartKey,
            'chartTitle' => $titles[$chartKey] ?? ucwords(str_replace('_', ' ', $chartKey)),
            'chartData' => $chartData,
            'filters' => $filters,
        ]);
    }

    /* ── Participants Page ────────────────────────────── */
    public function participants(): void
    {
        Auth::requireRole(['admin']);
        $userModel = new User();
        $masterModel = new MasterData();
        $db = \App\Core\Model::getDb();

        $q = Security::cleanString($_GET['q'] ?? '');
        $page = max(1, (int) ($_GET['p'] ?? 1));
        $perPage = 20;

        $academyCode = Security::cleanString($_GET['academy'] ?? '');
        $academyId = 0;
        if ($academyCode !== '') {
            $stmt = $db->prepare('SELECT id FROM academies WHERE code = ? LIMIT 1');
            $stmt->execute([$academyCode]);
            $academyId = (int) ($stmt->fetchColumn() ?: 0);
        }

        $filters = [
            'location_id' => $_GET['location_id'] ?? '',
            'company_id' => $_GET['company_id'] ?? '',
            'institution_id' => $_GET['institution_id'] ?? '',
            'profession_id' => $_GET['profession_id'] ?? '',
            'status' => $_GET['status'] ?? '',
            'academy_id' => $academyId,
        ];

        // Stats card values
        $totalTrainees = (int) $db->query('SELECT COUNT(*) FROM users JOIN roles ON roles.id = users.role_id WHERE roles.slug = "trainee"')->fetchColumn();
        $activeTrainees = (int) $db->query('SELECT COUNT(*) FROM users JOIN roles ON roles.id = users.role_id WHERE roles.slug = "trainee" AND users.status = "active"')->fetchColumn();
        $enrolledTrainees = (int) $db->query('SELECT COUNT(DISTINCT trainee_id) FROM enrolments WHERE status = "active"')->fetchColumn();
        $completedTrainees = (int) $db->query('SELECT COUNT(DISTINCT trainee_id) FROM enrolments WHERE status = "completed"')->fetchColumn();

        // Academy counts (enrolled in at least one course in that academy)
        $totalAdgeaTrainees = (int) $db->query('SELECT COUNT(DISTINCT e.trainee_id) FROM enrolments e JOIN courses c ON c.id = e.course_id JOIN academies a ON a.id = c.academy_id WHERE a.code = "ADGEA"')->fetchColumn();
        $totalIesgaTrainees = (int) $db->query('SELECT COUNT(DISTINCT e.trainee_id) FROM enrolments e JOIN courses c ON c.id = e.course_id JOIN academies a ON a.id = c.academy_id WHERE a.code = "IESGA"')->fetchColumn();

        // Load master data dropdowns
        $locations = $masterModel->list('locations');
        $companies = $masterModel->list('companies');
        $institutions = $masterModel->list('institutions');
        $professions = $masterModel->list('professions');

        View::render('admin/participants', [
            'users' => $userModel->allTrainees($q, $perPage, ($page - 1) * $perPage, $filters),
            'q' => $q,
            'pageNo' => $page,
            'totalPages' => max(1, (int) ceil($userModel->countTrainees($q, $filters) / $perPage)),
            'totalTrainees' => $totalTrainees,
            'activeTrainees' => $activeTrainees,
            'enrolledTrainees' => $enrolledTrainees,
            'completedTrainees' => $completedTrainees,
            'totalAdgea' => $totalAdgeaTrainees,
            'totalIesga' => $totalIesgaTrainees,
            'locations' => $locations,
            'companies' => $companies,
            'institutions' => $institutions,
            'professions' => $professions,
            'filters' => $filters,
            'academyCode' => $academyCode,
        ]);
    }

    /* ── Participant Detail API (AJAX) ────────────────── */
    public function participantDetail(): void
    {
        Auth::requireRole(['admin']);
        $userId = (int) ($_GET['id'] ?? 0);
        $userModel = new User();
        $trainee = $userModel->traineeDetail($userId);
        
        header('Content-Type: application/json');
        if (!$trainee) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Trainee not found']);
            exit;
        }

        $db = \App\Core\Model::getDb();
        
        // Fetch courses enrolled
        $coursesStmt = $db->prepare('
            SELECT e.status AS enrolment_status, e.progress_percent, e.completed_at,
                   c.title AS course_title, c.start_date, c.end_date,
                   a.code AS academy_code
            FROM enrolments e
            JOIN courses c ON c.id = e.course_id
            LEFT JOIN academies a ON a.id = c.academy_id
            WHERE e.trainee_id = ?
            ORDER BY e.created_at DESC
        ');
        $coursesStmt->execute([$userId]);
        $courses = $coursesStmt->fetchAll();

        // Fetch certificates
        $certStmt = $db->prepare('
            SELECT cert.certificate_no, cert.issued_at, cert.pdf_path, cert.status AS cert_status,
                   c.title AS course_title
            FROM certificates cert
            JOIN courses c ON c.id = cert.course_id
            WHERE cert.trainee_id = ?
            ORDER BY cert.issued_at DESC
        ');
        $certStmt->execute([$userId]);
        $certificates = $certStmt->fetchAll();

        echo json_encode([
            'status' => 'success',
            'trainee' => $trainee,
            'courses' => $courses,
            'certificates' => $certificates
        ]);
        exit;
    }

    /* ── Update Participant Master Data Links ─────────── */
    public function updateParticipant(): void
    {
        Auth::requireRole(['admin']);
        Security::verifyCsrf();
        
        $userId = (int) ($_POST['id'] ?? 0);
        $userModel = new User();
        $trainee = $userModel->find($userId);
        
        if (!$trainee) {
            $_SESSION['flash_error'] = 'Participant not found.';
            header('Location: index.php?page=admin-participants');
            return;
        }

        $userModel->updateTraineeMasterData($userId, [
            'company_id' => !empty($_POST['company_id']) ? (int) $_POST['company_id'] : null,
            'institution_id' => !empty($_POST['institution_id']) ? (int) $_POST['institution_id'] : null,
            'location_id' => !empty($_POST['location_id']) ? (int) $_POST['location_id'] : null,
            'profession_id' => !empty($_POST['profession_id']) ? (int) $_POST['profession_id'] : null,
        ]);

        Activity::log('Updated master data links for trainee ID ' . $userId);
        $_SESSION['flash_success'] = 'Trainee profile links updated successfully.';
        header('Location: index.php?page=admin-participants');
    }


    /* ── System Settings Page ─────────────────────────── */
    public function systemSettings(): void
    {
        Auth::requireRole(['admin']);
        $db = \App\Core\Model::getDb();

        $search = Security::cleanString($_GET['search'] ?? '');
        
        if ($search !== '') {
            $stmt = $db->prepare('
                SELECT al.*, u.name AS user_name, u.email AS user_email 
                FROM activity_logs al 
                LEFT JOIN users u ON u.id = al.user_id 
                WHERE u.name LIKE ? OR al.action LIKE ? OR al.details LIKE ?
                ORDER BY al.created_at DESC 
                LIMIT 100
            ');
            $like = '%' . $search . '%';
            $stmt->execute([$like, $like, $like]);
            $logs = $stmt->fetchAll();
        } else {
            $logs = $db->query('
                SELECT al.*, u.name AS user_name, u.email AS user_email 
                FROM activity_logs al 
                LEFT JOIN users u ON u.id = al.user_id 
                ORDER BY al.created_at DESC 
                LIMIT 50
            ')->fetchAll();
        }

        $uploadWritable = is_writable(UPLOAD_PATH);
        $submissionWritable = is_writable(SUBMISSION_PATH);
        $certWritable = is_writable(CERTIFICATE_PATH);

        // Maintenance mode
        $maintenanceMode = false;
        try {
            $stmt = $db->prepare("SELECT setting_value FROM website_settings WHERE setting_key = 'maintenance_mode'");
            $stmt->execute();
            $maintenanceMode = ($stmt->fetchColumn() === '1');
        } catch (\Exception $e) {}

        View::render('admin/system-settings', [
            'logs' => $logs,
            'uploadWritable' => $uploadWritable,
            'submissionWritable' => $submissionWritable,
            'certWritable' => $certWritable,
            'search' => $search,
            'maintenanceMode' => $maintenanceMode,
        ]);
    }

    /* ── Save System Settings (Maintenance Mode etc.) ── */
    public function saveSystemSettings(): void
    {
        Auth::requireRole(['admin']);
        Security::verifyCsrf();
        $db = \App\Core\Model::getDb();

        $maintenanceMode = isset($_POST['maintenance_mode']) ? '1' : '0';

        // Upsert maintenance_mode
        $stmt = $db->prepare("SELECT COUNT(*) FROM website_settings WHERE setting_key = 'maintenance_mode'");
        $stmt->execute();
        if ((int) $stmt->fetchColumn() > 0) {
            $db->prepare("UPDATE website_settings SET setting_value = ? WHERE setting_key = 'maintenance_mode'")->execute([$maintenanceMode]);
        } else {
            $db->prepare("INSERT INTO website_settings (setting_key, setting_value) VALUES ('maintenance_mode', ?)")->execute([$maintenanceMode]);
        }

        Activity::log('Updated system settings: maintenance_mode=' . $maintenanceMode);
        header('Location: index.php?page=admin-system-settings');
    }

    /* ── Database Backup (Download .sql) ──────────────── */
    public function backupDatabase(): void
    {
        Auth::requireRole(['admin']);
        $db = \App\Core\Model::getDb();

        $tables = $db->query("SHOW TABLES")->fetchAll(\PDO::FETCH_COLUMN);
        $dbName = $db->query("SELECT DATABASE()")->fetchColumn();

        $sql = "-- CENTEXS ITOP IMS Database Backup\n";
        $sql .= "-- Generated: " . date('Y-m-d H:i:s') . "\n";
        $sql .= "-- Database: " . $dbName . "\n\n";
        $sql .= "SET FOREIGN_KEY_CHECKS = 0;\n\n";

        foreach ($tables as $table) {
            // Table structure
            $createStmt = $db->query("SHOW CREATE TABLE `{$table}`")->fetch(\PDO::FETCH_ASSOC);
            $sql .= "DROP TABLE IF EXISTS `{$table}`;\n";
            $sql .= $createStmt['Create Table'] . ";\n\n";

            // Table data
            $rows = $db->query("SELECT * FROM `{$table}`")->fetchAll(\PDO::FETCH_ASSOC);
            if (!empty($rows)) {
                $columns = array_keys($rows[0]);
                $colList = implode('`, `', $columns);
                foreach ($rows as $row) {
                    $values = array_map(function ($val) use ($db) {
                        if ($val === null) return 'NULL';
                        return $db->quote($val);
                    }, array_values($row));
                    $sql .= "INSERT INTO `{$table}` (`{$colList}`) VALUES (" . implode(', ', $values) . ");\n";
                }
                $sql .= "\n";
            }
        }

        $sql .= "SET FOREIGN_KEY_CHECKS = 1;\n";

        Activity::log('Downloaded database backup');

        $filename = 'centexs_itop_backup_' . date('Y-m-d_His') . '.sql';
        header('Content-Type: application/sql');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($sql));
        echo $sql;
        exit;
    }

    /* ── Database Restore (Upload .sql) ───────────────── */
    public function restoreDatabase(): void
    {
        Auth::requireRole(['admin']);
        Security::verifyCsrf();

        if (empty($_FILES['sql_file']['tmp_name']) || $_FILES['sql_file']['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['flash_error'] = 'No file uploaded or upload error.';
            header('Location: index.php?page=admin-system-settings');
            return;
        }

        $ext = strtolower(pathinfo($_FILES['sql_file']['name'], PATHINFO_EXTENSION));
        if ($ext !== 'sql') {
            $_SESSION['flash_error'] = 'Only .sql files are accepted.';
            header('Location: index.php?page=admin-system-settings');
            return;
        }

        $sqlContent = file_get_contents($_FILES['sql_file']['tmp_name']);
        if (empty($sqlContent)) {
            $_SESSION['flash_error'] = 'Uploaded file is empty.';
            header('Location: index.php?page=admin-system-settings');
            return;
        }

        $db = \App\Core\Model::getDb();
        try {
            $db->exec('SET FOREIGN_KEY_CHECKS = 0');

            // Split by semicolons, filtering empty statements
            $statements = array_filter(array_map('trim', explode(";\n", $sqlContent)), fn($s) => $s !== '' && $s !== '--');
            foreach ($statements as $stmt) {
                if (trim($stmt) === '') continue;
                $db->exec($stmt);
            }

            $db->exec('SET FOREIGN_KEY_CHECKS = 1');
            Activity::log('Restored database from uploaded backup file');
            $_SESSION['flash_success'] = 'Database restored successfully from backup.';
        } catch (\Exception $e) {
            $db->exec('SET FOREIGN_KEY_CHECKS = 1');
            $_SESSION['flash_error'] = 'Restore failed: ' . $e->getMessage();
        }

        header('Location: index.php?page=admin-system-settings');
    }
}
