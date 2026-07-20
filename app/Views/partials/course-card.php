<?php
use App\Core\Security;

$thumbnail = $course['thumbnail_image'] ?? '';
$image = $thumbnail ? 'storage/uploads/' . $thumbnail : 'public/assets/img/course-placeholder.svg';
$actions = $actions ?? 'public';

$maxPart = (int) ($course['max_participants'] ?? $course['capacity'] ?? 0);
$currentPart = (int) ($course['participant_count'] ?? 0);
$occupancyPercent = $maxPart > 0 ? min(100, (int) round(($currentPart / $maxPart) * 100)) : 0;
$progressBarColor = 'cm-bg-success';
if ($occupancyPercent >= 90) {
    $progressBarColor = 'cm-bg-danger';
} elseif ($occupancyPercent >= 70) {
    $progressBarColor = 'cm-bg-warning';
}

$academyCode = $course['academy_code'] ?? '';
$academyClass = 'cm-academy-general';
if ($academyCode === 'ADGEA') {
    $academyClass = 'cm-academy-adgea';
} elseif ($academyCode === 'IESGA') {
    $academyClass = 'cm-academy-iesga';
}

$statusColors = [
    'active' => 'cm-status-active',
    'published' => 'cm-status-active',
    'draft' => 'cm-status-draft',
    'completed' => 'cm-status-completed',
    'archived' => 'cm-status-archived',
];
$courseStatus = $course['course_status'] ?? $course['status'] ?? 'draft';
$statusClass = $statusColors[$courseStatus] ?? 'cm-status-draft';
?>

<div class="cm-card" data-course-id="<?= (int) $course['id'] ?>">
    <!-- Thumbnail Image & Badges -->
    <div class="cm-card-banner">
        <img class="cm-card-img" src="<?= Security::e($image) ?>" alt="" loading="lazy">
        <?php if (!empty($academyCode)): ?>
            <span class="cm-card-badge <?= $academyClass ?>"><?= Security::e($academyCode) ?></span>
        <?php endif; ?>
        
        <?php if ($actions === 'admin'): ?>
            <!-- Action Dropdown for Admin -->
            <div class="cm-card-actions">
                <button class="cm-card-trigger" type="button" aria-label="Course Actions" data-cm-dropdown>
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="5" r="1"/><circle cx="12" cy="12" r="1"/><circle cx="12" cy="19" r="1"/></svg>
                </button>
                <div class="cm-dropdown-menu">
                    <button class="cm-dropdown-item" type="button" data-cm-view="<?= (int) $course['id'] ?>">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        View Details
                    </button>
                    <a class="cm-dropdown-item" href="index.php?page=course-room&course_id=<?= (int) $course['id'] ?>">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
                        Open Room
                    </a>
                    <button class="cm-dropdown-item" type="button" data-cm-edit="<?= (int) $course['id'] ?>"
                            data-course='<?= Security::e(json_encode([
                                'id' => $course['id'],
                                'title' => $course['title'],
                                'category' => $course['category'],
                                'academy_id' => $course['academy_id'] ?? '',
                                'description' => $course['description'] ?? '',
                                'start_date' => $course['start_date'] ?? '',
                                'end_date' => $course['end_date'] ?? '',
                                'capacity' => $course['capacity'] ?? 25,
                                'max_participants' => $course['max_participants'] ?? 25,
                                'fee' => $course['fee'] ?? 0,
                                'instructor_id' => $course['instructor_id'] ?? '',
                                'status' => $course['status'] ?? 'draft',
                                'course_status' => $course['course_status'] ?? 'draft',
                                'thumbnail_image' => $course['thumbnail_image'] ?? '',
                            ])) ?>'>
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                        Edit Course
                    </button>
                    <div class="cm-dropdown-divider"></div>
                    <button class="cm-dropdown-item cm-dropdown-item-danger" type="button"
                            data-cm-delete="<?= (int) $course['id'] ?>"
                            data-course-title="<?= Security::e($course['title']) ?>">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                        Delete Course
                    </button>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Course Content -->
    <div class="cm-card-content">
        <div class="cm-card-meta-row">
            <span class="cm-card-category"><?= Security::e($course['category'] ?? '-') ?></span>
            <span class="cm-status-pill <?= $statusClass ?>">
                <span class="cm-status-dot"></span>
                <?= Security::e(ucfirst($courseStatus)) ?>
            </span>
        </div>

        <h3 class="cm-card-title" title="<?= Security::e($course['title'] ?? '') ?>">
            <?= Security::e($course['title'] ?? '') ?>
        </h3>
        
        <?php if (!empty($course['academy_name'])): ?>
            <div class="cm-card-academy-title"><?= Security::e($course['academy_name']) ?></div>
        <?php endif; ?>

        <p class="cm-card-desc">
            <?= Security::e(Security::excerpt($course['description'] ?? '', 85)) ?><?= strlen((string) ($course['description'] ?? '')) > 85 ? '...' : '' ?>
        </p>

        <div class="cm-card-info">
            <div class="cm-info-item">
                <strong>Instructor:</strong>
                <span><?= Security::e($course['instructor_name'] ?? 'To be assigned') ?></span>
            </div>
            <div class="cm-info-item">
                <strong>Duration:</strong>
                <span><?= Security::e(($course['start_date'] ?? '-') . ' to ' . ($course['end_date'] ?? '-')) ?></span>
            </div>
        </div>

        <!-- Occupancy Progress -->
        <div class="cm-card-progress-section">
            <div class="cm-progress-info">
                <span><strong>Enrollment:</strong> <?= $currentPart ?> / <?= $maxPart ?></span>
                <?php if ($occupancyPercent >= 90 && $actions === 'admin'): ?>
                    <span class="cm-progress-alert">Full</span>
                <?php endif; ?>
            </div>
            <div class="cm-progress-track">
                <div class="cm-progress-bar <?= $progressBarColor ?>" style="width: <?= $occupancyPercent ?>%"></div>
            </div>
        </div>

        <!-- Context Actions for Non-Admin Views -->
        <?php if ($actions !== 'admin'): ?>
            <div class="cm-card-footer-actions">
                <?php if ($actions === 'instructor'): ?>
                    <a class="cm-btn-footer cm-btn-footer-primary" href="index.php?page=course-room&course_id=<?= (int) $course['id'] ?>">Open Room</a>
                    <a class="cm-btn-footer cm-btn-footer-secondary" href="index.php?page=dashboard&edit=<?= (int) $course['id'] ?>">Edit</a>
                    <form method="post" action="index.php?page=instructor-unassign-course" onsubmit="return confirm('Stop teaching this course? This will unassign you.')" style="margin:0;">
                        <input type="hidden" name="_csrf" value="<?= Security::e(Security::csrfToken()) ?>">
                        <input type="hidden" name="course_id" value="<?= (int) $course['id'] ?>">
                        <button class="cm-btn-footer cm-btn-footer-danger" type="submit">Unassign</button>
                    </form>
                <?php elseif ($actions === 'trainee'): ?>
                    <?php if (($course['status'] ?? '') !== 'pending'): ?>
                        <a class="cm-btn-footer cm-btn-footer-primary cm-w-full" href="index.php?page=course-room&course_id=<?= (int) $course['course_id'] ?>">Open Room</a>
                    <?php endif; ?>
                <?php else: ?>
                    <a class="cm-btn-footer cm-btn-footer-primary cm-w-full" href="index.php?page=course&id=<?= (int) $course['id'] ?>">Register / Details</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
