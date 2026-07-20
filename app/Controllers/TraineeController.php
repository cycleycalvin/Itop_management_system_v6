<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Activity;
use App\Core\Auth;
use App\Core\Security;
use App\Core\View;
use App\Models\Certificate;
use App\Models\Evaluation;
use App\Models\TraineeProfile;
use App\Models\User;

final class TraineeController
{
    public function userProfile(): void
    {
        Auth::requireLogin();
        $profileModel = new TraineeProfile();
        View::render('profile/index', [
            'user' => (new User())->find((int) Auth::id()),
            'profile' => $profileModel->findByUser((int) Auth::id()),
            'documents' => $profileModel->documents((int) Auth::id()),
            'learning' => $this->learningSummary((int) Auth::id()),
        ]);
    }

    public function profile(): void
    {
        Auth::requireLogin();
        $userId = (int) Auth::id();
        $profileModel = new TraineeProfile();
        View::render('trainee/profile', [
            'user' => (new User())->find($userId),
            'profile' => $profileModel->findByUser($userId),
            'documents' => $profileModel->documents($userId),
        ]);
    }

    public function saveProfile(): void
    {
        Auth::requireLogin();
        Security::verifyCsrf();
        $userId = (int) Auth::id();
        $profilePicture = null;
        if (!empty($_FILES['profile_picture']['name'])) {
            $profilePicture = Security::validateUpload($_FILES['profile_picture'], ['jpg', 'jpeg', 'png', 'webp']);
            if ($profilePicture) {
                move_uploaded_file($_FILES['profile_picture']['tmp_name'], UPLOAD_PATH . '/' . $profilePicture);
            }
        }

        // Update standard user columns on users table
        $db = \App\Core\Model::getDb();
        $stmt = $db->prepare('UPDATE users SET name = ?, phone = ?, address = ?, identity_number = ?, gender = ?, date_of_birth = ?, institution_company = ?, time_zone = ?, profile_picture = COALESCE(?, profile_picture), updated_at = NOW() WHERE id = ?');
        $stmt->execute([
            Security::cleanString($_POST['name'] ?? ''),
            Security::cleanString($_POST['phone'] ?? ''),
            Security::cleanString($_POST['address'] ?? '', 1000),
            Security::cleanString($_POST['identity_number'] ?? ''),
            Security::cleanString($_POST['gender'] ?? ''),
            !empty($_POST['date_of_birth']) ? $_POST['date_of_birth'] : null,
            Security::cleanString($_POST['institution_company'] ?? ''),
            Security::cleanString($_POST['time_zone'] ?? 'Asia/Kuala_Lumpur'),
            $profilePicture,
            $userId
        ]);

        // If trainee, also update trainee_profiles table and handle document uploads
        if (Auth::role() === 'trainee') {
            $profileModel = new TraineeProfile();
            $profileModel->save($userId, [
                'identity_number' => Security::cleanString($_POST['identity_number'] ?? ''),
                'phone' => Security::cleanString($_POST['phone'] ?? ''),
                'address' => Security::cleanString($_POST['address'] ?? '', 1000),
                'education' => Security::cleanString($_POST['education'] ?? '', 2000),
                'employment' => Security::cleanString($_POST['employment'] ?? '', 2000),
                'emergency_contact' => Security::cleanString($_POST['emergency_contact'] ?? '', 1000),
                'profile_picture' => $profilePicture,
            ]);

            if (!empty($_FILES['document']['name'])) {
                $document = Security::validateUpload($_FILES['document'], ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png']);
                if ($document) {
                    move_uploaded_file($_FILES['document']['tmp_name'], UPLOAD_PATH . '/' . $document);
                    $profileModel->addDocument($userId, Security::cleanString($_POST['document_type'] ?? 'Supporting Document'), $_FILES['document']['name'], $document);
                }
            }
        }

        Activity::log('Updated profile');
        header('Location: index.php?page=profile');
    }

    public function evaluations(): void
    {
        Auth::requireRole(['trainee']);
        View::render('trainee/evaluations', [
            'courses' => (new Evaluation())->completedCoursesNeedingEvaluation((int) Auth::id()),
        ]);
    }

    public function saveEvaluation(): void
    {
        Auth::requireRole(['trainee']);
        Security::verifyCsrf();
        (new Evaluation())->save([
            'course_id' => (int) ($_POST['course_id'] ?? 0),
            'trainee_id' => Auth::id(),
            'course_rating' => max(1, min(5, (int) ($_POST['course_rating'] ?? 1))),
            'instructor_rating' => max(1, min(5, (int) ($_POST['instructor_rating'] ?? 1))),
            'feedback' => Security::cleanString($_POST['feedback'] ?? '', 3000),
            'comments' => Security::cleanString($_POST['comments'] ?? '', 3000),
        ]);
        Activity::log('Submitted course evaluation');
        header('Location: index.php?page=trainee-evaluations');
    }

    public function certificates(): void
    {
        Auth::requireRole(['trainee']);
        View::render('trainee/certificates', [
            'certificates' => (new Certificate())->forTrainee((int) Auth::id(), Security::cleanString($_GET['q'] ?? '')),
            'q' => Security::cleanString($_GET['q'] ?? ''),
        ]);
    }

    private function learningSummary(int $userId): array
    {
        $db = \App\Core\Database::connection();
        $stmt = $db->prepare('SELECT e.*, c.title AS course_title, c.start_date, c.end_date, i.name AS instructor_name FROM enrolments e JOIN courses c ON c.id = e.course_id LEFT JOIN users i ON i.id = c.instructor_id WHERE e.trainee_id = ? ORDER BY e.created_at DESC LIMIT 5');
        $stmt->execute([$userId]);
        $courses = $stmt->fetchAll();
        $activity = $db->prepare('SELECT * FROM login_activity WHERE user_id = ? ORDER BY created_at DESC LIMIT 8');
        $activity->execute([$userId]);
        return [
            'courses' => $courses,
            'login_activity' => $activity->fetchAll(),
        ];
    }
}
