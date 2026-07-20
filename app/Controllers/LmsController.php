<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Activity;
use App\Core\Auth;
use App\Core\Security;
use App\Core\View;
use App\Models\Course;
use App\Models\Lms;

final class LmsController
{
    public function courseRoom(): void
    {
        Auth::requireLogin();
        $courseId = (int) ($_GET['course_id'] ?? 0);
        $lms = new Lms();
        View::render('courses/room', [
            'course' => (new Course())->find($courseId),
            'materials' => $lms->materials($courseId),
            'assignments' => $lms->assignments($courseId),
            'quizzes' => $lms->quizzes($courseId),
        ]);
    }

    public function addMaterial(): void
    {
        Auth::requireRole(['admin', 'instructor']);
        Security::verifyCsrf();
        $filename = null;
        if (!empty($_FILES['material']['name'])) {
            $filename = Security::validateUpload($_FILES['material'], ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'mp4', 'zip']);
            move_uploaded_file($_FILES['material']['tmp_name'], UPLOAD_PATH . '/' . $filename);
        }

        (new Lms())->addMaterial([
            'course_id' => (int) $_POST['course_id'],
            'title' => Security::cleanString($_POST['title'] ?? ''),
            'type' => Security::cleanString($_POST['type'] ?? 'document'),
            'file_path' => $filename,
            'external_url' => filter_var($_POST['external_url'] ?? '', FILTER_VALIDATE_URL) ?: null,
            'uploaded_by' => Auth::id(),
        ]);
        Activity::log('Uploaded material');
        header('Location: index.php?page=course-room&course_id=' . (int) $_POST['course_id']);
    }

    public function addAssignment(): void
    {
        Auth::requireRole(['admin', 'instructor']);
        Security::verifyCsrf();
        (new Lms())->addAssignment([
            'course_id' => (int) $_POST['course_id'],
            'title' => Security::cleanString($_POST['title'] ?? ''),
            'instructions' => Security::cleanString($_POST['instructions'] ?? '', 2000),
            'due_date' => $_POST['due_date'] ?: null,
            'max_score' => (float) ($_POST['max_score'] ?? 100),
            'created_by' => Auth::id(),
        ]);
        Activity::log('Created assignment');
        header('Location: index.php?page=course-room&course_id=' . (int) $_POST['course_id']);
    }

    public function submitAssignment(): void
    {
        Auth::requireRole(['trainee']);
        Security::verifyCsrf();
        $filename = Security::validateUpload($_FILES['submission'], ['pdf', 'doc', 'docx', 'zip', 'jpg', 'png']);
        if (!$filename) {
            exit('A submission file is required.');
        }
        move_uploaded_file($_FILES['submission']['tmp_name'], SUBMISSION_PATH . '/' . $filename);
        (new Lms())->submitAssignment([
            'assignment_id' => (int) $_POST['assignment_id'],
            'trainee_id' => Auth::id(),
            'file_path' => $filename,
            'notes' => Security::cleanString($_POST['notes'] ?? '', 1000),
        ]);
        Activity::log('Submitted assignment');
        header('Location: index.php?page=trainee-dashboard');
    }
}

