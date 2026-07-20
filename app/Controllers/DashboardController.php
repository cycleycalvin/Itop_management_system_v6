<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\View;
use App\Models\Certificate;
use App\Models\Content;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Evaluation;
use App\Models\Lms;
use App\Models\Notification;
use App\Models\TraineeProfile;
use App\Models\User;

final class DashboardController
{
    public function dashboard(): void
    {
        Auth::requireLogin();
        match (Auth::role()) {
            'admin' => $this->admin(),
            'instructor' => $this->instructor(),
            default => $this->traineeOverview(),
        };
    }

    public function admin(): void
    {
        Auth::requireRole(['admin']);
        $content = new Content();
        $db = \App\Core\Model::getDb();
        $pendingUsersCount = (int) $db->query("SELECT COUNT(*) FROM users WHERE status = 'pending'")->fetchColumn();
        View::render('dashboard/admin', [
            'stats' => $content->stats(),
            'trends' => $content->trends(),
            'analytics' => $content->dashboardAnalytics(),
            'activity' => $content->recentActivity(),
            'announcements' => $content->announcements(),
            'pendingUsersCount' => $pendingUsersCount,
        ]);
    }

    public function instructor(): void
    {
        Auth::requireRole(['instructor']);
        $courseModel = new Course();
        $userId = (int) Auth::id();

        $editingId = isset($_GET['edit']) ? (int) $_GET['edit'] : 0;
        $editingCourse = null;
        if ($editingId) {
            $course = $courseModel->find($editingId);
            if ($course && (int) $course['instructor_id'] === $userId) {
                $editingCourse = $course;
            }
        }

        View::render('dashboard/instructor', [
            'courses' => $courseModel->assignedTo($userId),
            'submissions' => (new Lms())->submissionsForInstructor($userId),
            'editing' => $editingCourse,
        ]);
    }

    /** Trainee Overview — distinct from "My Learning" */
    public function traineeOverview(): void
    {
        Auth::requireRole(['trainee']);
        $userId = (int) Auth::id();
        $enrolments = (new Enrollment())->forTrainee($userId);
        $certificates = (new Certificate())->forTrainee($userId);
        $notificationCount = (new Notification())->unreadCount($userId);
        $profile = (new TraineeProfile())->findByUser($userId);
        $user = (new User())->find($userId);
        $announcements = (new Content())->announcements(false, 'trainee', $userId);

        View::render('dashboard/trainee-overview', [
            'enrolments' => $enrolments,
            'certificates' => $certificates,
            'notificationCount' => $notificationCount,
            'profile' => $profile,
            'user' => $user,
            'announcements' => $announcements,
        ]);
    }

    /** Trainee My Learning page */
    public function trainee(): void
    {
        Auth::requireRole(['trainee']);
        View::render('dashboard/trainee', [
            'enrolments' => (new Enrollment())->forTrainee((int) Auth::id()),
            'pendingEvaluations' => (new Evaluation())->completedCoursesNeedingEvaluation((int) Auth::id()),
            'announcements' => (new Content())->announcements(false, 'trainee', (int) Auth::id()),
        ]);
    }
}
