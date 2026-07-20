<?php
declare(strict_types=1);

require __DIR__ . '/app/bootstrap.php';

use App\Controllers\AdminController;
use App\Controllers\AuthController;
use App\Controllers\CommunicationController;
use App\Controllers\DashboardController;
use App\Controllers\LmsController;
use App\Controllers\PublicController;
use App\Controllers\ReportController;
use App\Controllers\TraineeController;

$page = $_GET['page'] ?? 'home';
$method = $_SERVER['REQUEST_METHOD'];

$routes = [
    'GET' => [
        'home' => [PublicController::class, 'home'],
        'about' => [PublicController::class, 'about'],
        'courses' => [PublicController::class, 'courses'],
        'course' => [PublicController::class, 'course'],
        'news' => [PublicController::class, 'news'],
        'contact' => [PublicController::class, 'contact'],
        'login' => [AuthController::class, 'loginForm'],
        'register' => [AuthController::class, 'registerForm'],
        'logout' => [AuthController::class, 'logout'],
        'dashboard' => [DashboardController::class, 'dashboard'],
        'admin-dashboard' => [DashboardController::class, 'admin'],
        'instructor-dashboard' => [DashboardController::class, 'instructor'],
        'trainee-dashboard' => [DashboardController::class, 'trainee'],
        'profile' => [TraineeController::class, 'userProfile'],
        'admin-users' => [AdminController::class, 'users'],
        'admin-courses' => [AdminController::class, 'courses'],
        'admin-enrolments' => [AdminController::class, 'enrolments'],
        'admin-certificates' => [AdminController::class, 'certificates'],
        'admin-documentation' => [AdminController::class, 'documentation'],
        'admin-documentation-export' => [AdminController::class, 'exportDocumentation'],
        'admin-evaluations' => [AdminController::class, 'evaluations'],
        'admin-master-data' => [AdminController::class, 'masterData'],
        'announcements-manage' => [AdminController::class, 'announcements'],
        'trainee-profile' => [TraineeController::class, 'profile'],
        'trainee-evaluations' => [TraineeController::class, 'evaluations'],
        'trainee-certificates' => [TraineeController::class, 'certificates'],
        'messages' => [CommunicationController::class, 'messages'],
        'messages-feed' => [CommunicationController::class, 'messagesFeed'],
        'notifications' => [CommunicationController::class, 'notifications'],
        'badge-counts' => [CommunicationController::class, 'badgeCounts'],
        'course-room' => [LmsController::class, 'courseRoom'],
        'reports' => [ReportController::class, 'reports'],
        'export-report' => [ReportController::class, 'exportCsv'],
        'verify-certificate' => [ReportController::class, 'verifyCertificate'],
        'view-certificate' => [ReportController::class, 'viewCertificate'],
        'download-certificate' => [ReportController::class, 'downloadCertificate'],
        'instructor-enrolments' => [AdminController::class, 'instructorEnrolments'],
        'admin-website-settings' => [AdminController::class, 'websiteSettings'],
        'admin-fetch-analytics-details' => [AdminController::class, 'fetchAnalyticsDetails'],
        'admin-analytics' => [AdminController::class, 'analytics'],
        'admin-analytics-detail' => [AdminController::class, 'analyticsDetail'],
        'admin-participants' => [AdminController::class, 'participants'],
        'admin-system-settings' => [AdminController::class, 'systemSettings'],
        'admin-backup-database' => [AdminController::class, 'backupDatabase'],
    ],
    'POST' => [
        'admin-save-analytics-details' => [AdminController::class, 'saveAnalyticsDetails'],
        'admin-create-custom-chart' => [AdminController::class, 'createCustomChart'],
        'admin-delete-custom-chart' => [AdminController::class, 'deleteCustomChart'],
        'admin-restore-database' => [AdminController::class, 'restoreDatabase'],
        'admin-save-system-settings' => [AdminController::class, 'saveSystemSettings'],
        'login' => [AuthController::class, 'login'],
        'register' => [AuthController::class, 'register'],
        'enroll' => [PublicController::class, 'enroll'],
        'set-user-status' => [AdminController::class, 'setUserStatus'],
        'save-user' => [AdminController::class, 'saveUser'],
        'delete-user' => [AdminController::class, 'deleteUser'],
        'save-course' => [AdminController::class, 'saveCourse'],
        'delete-course' => [AdminController::class, 'deleteCourse'],
        'set-enrolment-status' => [AdminController::class, 'setEnrolmentStatus'],
        'save-announcement' => [AdminController::class, 'saveAnnouncement'],
        'save-certificate-template' => [AdminController::class, 'saveCertificateTemplate'],
        'delete-certificate-template' => [AdminController::class, 'deleteCertificateTemplate'],
        'issue-certificate' => [AdminController::class, 'issueCertificate'],
        'review-certificate' => [AdminController::class, 'reviewCertificate'],
        'save-master-data' => [AdminController::class, 'saveMasterData'],
        'delete-master-data' => [AdminController::class, 'deleteMasterData'],
        'save-training-statistic' => [AdminController::class, 'saveTrainingStatistic'],
        'delete-training-statistic' => [AdminController::class, 'deleteTrainingStatistic'],
        'save-trainee-profile' => [TraineeController::class, 'saveProfile'],
        'save-evaluation' => [TraineeController::class, 'saveEvaluation'],
        'send-message' => [CommunicationController::class, 'sendMessage'],
        'delete-message' => [CommunicationController::class, 'deleteMessage'],
        'mark-notification-read' => [CommunicationController::class, 'markNotificationRead'],
        'delete-notification' => [CommunicationController::class, 'deleteNotification'],
        'add-material' => [LmsController::class, 'addMaterial'],
        'add-assignment' => [LmsController::class, 'addAssignment'],
        'submit-assignment' => [LmsController::class, 'submitAssignment'],
        'instructor-enrolment-status' => [AdminController::class, 'setInstructorEnrolmentStatus'],
        'save-website-settings' => [AdminController::class, 'saveWebsiteSettings'],
        'save-success-story' => [AdminController::class, 'saveSuccessStory'],
        'delete-success-story' => [AdminController::class, 'deleteSuccessStory'],
        'save-intake' => [AdminController::class, 'saveIntake'],
        'delete-intake' => [AdminController::class, 'deleteIntake'],
        'save-profile-picture' => [AdminController::class, 'saveProfilePicture'],
        'instructor-claim-course' => [AdminController::class, 'claimCourse'],
        'instructor-unassign-course' => [AdminController::class, 'unassignCourse'],
    ],
];

$handler = $routes[$method][$page] ?? null;
if (!$handler) {
    http_response_code(404);
    exit('Page not found.');
}

[$class, $action] = $handler;
(new $class())->$action();
