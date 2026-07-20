<?php
use App\Core\Security;

$thumbnail = $course['thumbnail_image'] ?? '';
$image = $thumbnail ? 'storage/uploads/' . $thumbnail : 'public/assets/img/course-placeholder.svg';
$actions = $actions ?? 'public';
?>
<div class="card h-100 course-card">
    <img class="course-thumb" src="<?= Security::e($image) ?>" alt="">
    <div class="card-body d-flex flex-column">
        <div class="d-flex justify-content-between gap-2 mb-2">
            <span class="badge text-bg-info"><?= Security::e($course['category'] ?? '-') ?></span>
            <span class="badge text-bg-light border"><?= Security::e($course['course_status'] ?? $course['status'] ?? 'draft') ?></span>
        </div>
        <h2 class="h5"><?= Security::e($course['title'] ?? '') ?></h2>
        <div class="small text-primary fw-semibold mb-2"><?= Security::e($course['academy_code'] ?? '') ?> <?= !empty($course['academy_name']) ? '· ' . Security::e($course['academy_name']) : '' ?></div>
        <p class="small text-muted mb-3"><?= Security::e(Security::excerpt($course['description'] ?? '', 110)) ?><?= strlen((string) ($course['description'] ?? '')) > 110 ? '...' : '' ?></p>
        <div class="small mb-1"><strong>Instructor:</strong> <?= Security::e($course['instructor_name'] ?? 'To be assigned') ?></div>
        <div class="small mb-1"><strong>Duration:</strong> <?= Security::e(($course['start_date'] ?? '-') . ' to ' . ($course['end_date'] ?? '-')) ?></div>
        <?php
        $maxPart = (int) ($course['max_participants'] ?? $course['capacity'] ?? 0);
        $currentPart = (int) ($course['participant_count'] ?? 0);
        $occupancyPercent = $maxPart > 0 ? min(100, (int) round(($currentPart / $maxPart) * 100)) : 0;
        $progressBarColor = 'bg-success';
        if ($occupancyPercent >= 90) {
            $progressBarColor = 'bg-danger';
        } elseif ($occupancyPercent >= 70) {
            $progressBarColor = 'bg-warning';
        }
        ?>
        <div class="small mb-1 d-flex justify-content-between align-items-center">
            <span><strong>Participants:</strong> <?= $currentPart ?> / <?= $maxPart ?></span>
            <?php if ($occupancyPercent >= 90 && $actions === 'admin'): ?>
                <span class="badge bg-danger animate-pulse small" style="font-size: 0.7rem;">At Capacity</span>
            <?php endif; ?>
        </div>
        <div class="progress mb-3" style="height: 6px; border-radius: 3px; background-color: #f1f3f5;">
            <div class="progress-bar <?= $progressBarColor ?>" role="progressbar" style="width: <?= $occupancyPercent ?>%" aria-valuenow="<?= $occupancyPercent ?>" aria-valuemin="0" aria-valuemax="100"></div>
        </div>
        <div class="mt-auto d-flex flex-wrap gap-2">
            <?php if ($actions === 'admin'): ?>
                <a class="btn btn-sm btn-outline-primary" href="index.php?page=course-room&course_id=<?= (int) $course['id'] ?>">View</a>
                <a class="btn btn-sm btn-outline-secondary" href="index.php?page=admin-courses&edit=<?= (int) $course['id'] ?>">Edit</a>
                <form method="post" action="index.php?page=delete-course" onsubmit="return confirm('Delete this course?')">
                    <input type="hidden" name="_csrf" value="<?= Security::e(Security::csrfToken()) ?>">
                    <input type="hidden" name="id" value="<?= (int) $course['id'] ?>">
                    <button class="btn btn-sm btn-outline-danger">Delete</button>
                </form>
            <?php elseif ($actions === 'instructor'): ?>
                <a class="btn btn-sm btn-outline-primary" href="index.php?page=course-room&course_id=<?= (int) $course['id'] ?>">Open Room</a>
                <a class="btn btn-sm btn-outline-secondary" href="index.php?page=dashboard&edit=<?= (int) $course['id'] ?>">Edit Details</a>
                <form method="post" action="index.php?page=instructor-unassign-course" onsubmit="return confirm('Stop teaching this course? This will unassign you.')">
                    <input type="hidden" name="_csrf" value="<?= Security::e(Security::csrfToken()) ?>">
                    <input type="hidden" name="course_id" value="<?= (int) $course['id'] ?>">
                    <button class="btn btn-sm btn-outline-danger">Stop Teaching</button>
                </form>
            <?php elseif ($actions === 'trainee'): ?>
                <?php if (($course['status'] ?? '') !== 'pending'): ?>
                    <a class="btn btn-sm btn-outline-primary" href="index.php?page=course-room&course_id=<?= (int) $course['course_id'] ?>">Open</a>
                <?php endif; ?>
            <?php else: ?>
                <a class="btn btn-sm btn-primary" href="index.php?page=course&id=<?= (int) $course['id'] ?>">Register / View Details</a>
            <?php endif; ?>
        </div>
    </div>
</div>
