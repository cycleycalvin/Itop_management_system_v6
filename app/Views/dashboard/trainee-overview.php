<?php
use App\Core\Auth;
use App\Core\Security;
use App\Core\View;

$enrolled = count($enrolments);
$totalProgress = 0;
foreach ($enrolments as $row) {
    $totalProgress += (int) ($row['progress_percent'] ?? 0);
}
$avgProgress = $enrolled ? (int) round($totalProgress / $enrolled) : 0;
$certCount = count($certificates);
?>
<?php View::partial('partials/role-nav'); ?>

<span class="section-label">Trainee Overview</span>
<h1 class="section-title">Welcome back, <?= Security::e(Auth::user()['name']) ?></h1>

<!-- ── Stat Cards Row ────────────────────────────── -->
<div class="overview-stats">
    <div class="overview-stat-card animate-in">
        <span class="overview-stat-label">Enrolled courses</span>
        <strong class="overview-stat-value"><?= $enrolled ?></strong>
    </div>
    <div class="overview-stat-card accent-green animate-in">
        <span class="overview-stat-label">Learning progress</span>
        <strong class="overview-stat-value"><?= $avgProgress ?>%</strong>
    </div>
    <div class="overview-stat-card animate-in">
        <span class="overview-stat-label">Certificates</span>
        <strong class="overview-stat-value"><?= $certCount ?></strong>
    </div>
    <div class="overview-stat-card accent-orange animate-in">
        <span class="overview-stat-label">Notifications</span>
        <strong class="overview-stat-value"><?= (int) $notificationCount ?></strong>
    </div>
    <div class="overview-stat-card animate-in">
        <span class="overview-stat-label">Courses active</span>
        <strong class="overview-stat-value"><?php
            $active = 0;
            foreach ($enrolments as $r) { if (($r['status'] ?? '') === 'active') $active++; }
            echo $active;
        ?></strong>
    </div>
</div>

<!-- ── Two-Column Content ────────────────────────── -->
<div class="overview-columns">
    <!-- Left Column -->
    <div class="overview-main">
        <!-- Current Courses -->
        <div class="overview-panel animate-in">
            <span class="section-label">Learning Path</span>
            <h2 class="overview-panel-title">Current course</h2>

            <?php if ($enrolments): ?>
                <div class="overview-course-list">
                    <?php foreach ($enrolments as $row): ?>
                        <div class="overview-course-row">
                            <span class="overview-course-name"><?= Security::e($row['title']) ?></span>
                            <span class="overview-course-progress <?= (int) ($row['progress_percent'] ?? 0) === 100 ? 'completed' : ((int) ($row['progress_percent'] ?? 0) === 0 ? 'not-started' : '') ?>">
                                <?php
                                    $pct = (int) ($row['progress_percent'] ?? 0);
                                    echo $pct === 0 ? 'Not started' : $pct . '%';
                                ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="text-muted small mb-0">No courses enrolled yet. <a href="index.php?page=courses">Browse courses →</a></p>
            <?php endif; ?>

            <div class="lms-btn-group" style="margin-top:1.15rem">
                <a class="lms-btn lms-btn-primary" href="index.php?page=trainee-dashboard">Open LMS</a>
                <a class="lms-btn" href="index.php?page=trainee-evaluations">Submit Evaluation</a>
                <a class="lms-btn" href="index.php?page=trainee-certificates">View Certificates</a>
            </div>
        </div>

        <!-- Trainee Schedule / Upcoming -->
        <div class="overview-panel animate-in">
            <span class="section-label">Upcoming</span>
            <h2 class="overview-panel-title">Trainee schedule</h2>

            <?php
                $scheduleItems = [];
                foreach ($enrolments as $row) {
                    if (!empty($row['start_date']) && strtotime($row['start_date']) >= strtotime('today')) {
                        $scheduleItems[] = [
                            'date' => $row['start_date'],
                            'title' => $row['title'],
                            'meta' => 'Course starts',
                        ];
                    }
                    if (!empty($row['end_date']) && strtotime($row['end_date']) >= strtotime('today')) {
                        $scheduleItems[] = [
                            'date' => $row['end_date'],
                            'title' => $row['title'] . ' — Ends',
                            'meta' => 'Course deadline',
                        ];
                    }
                }
                foreach (array_slice($announcements, 0, 3) as $ann) {
                    $scheduleItems[] = [
                        'date' => $ann['created_at'] ?? date('Y-m-d'),
                        'title' => $ann['title'],
                        'meta' => 'Announcement',
                    ];
                }
                usort($scheduleItems, fn($a, $b) => strtotime($a['date']) - strtotime($b['date']));
                $scheduleItems = array_slice($scheduleItems, 0, 5);
            ?>

            <?php if ($scheduleItems): ?>
                <?php foreach ($scheduleItems as $item): ?>
                    <div class="schedule-row">
                        <div class="schedule-date"><?= date('j M', strtotime($item['date'])) ?></div>
                        <div class="schedule-detail">
                            <div class="schedule-title"><?= Security::e($item['title']) ?></div>
                            <div class="schedule-meta"><?= Security::e($item['meta']) ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-muted small mb-0">No upcoming events right now.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Right Column: Profile Details -->
    <div class="overview-aside">
        <div class="overview-panel animate-in">
            <span class="section-label" style="color:#c0392b">Profile</span>
            <h2 class="overview-panel-title">Trainee details</h2>

            <div class="overview-detail-list">
                <div class="overview-detail-row">
                    <span class="overview-detail-label">Name</span>
                    <span class="overview-detail-value"><?= Security::e($user['name'] ?? Auth::user()['name']) ?></span>
                </div>
                <div class="overview-detail-row">
                    <span class="overview-detail-label">Status</span>
                    <span class="overview-detail-value">
                        <?php $status = $user['status'] ?? 'active'; ?>
                        <span class="completion-item-badge <?= $status === 'active' ? '' : 'in-progress' ?>"><?= ucfirst(Security::e($status)) ?></span>
                    </span>
                </div>
                <div class="overview-detail-row">
                    <span class="overview-detail-label">Email</span>
                    <span class="overview-detail-value"><?= Security::e($user['email'] ?? Auth::user()['email']) ?></span>
                </div>
                <?php if (!empty($profile['phone'])): ?>
                    <div class="overview-detail-row">
                        <span class="overview-detail-label">Phone</span>
                        <span class="overview-detail-value"><?= Security::e($profile['phone']) ?></span>
                    </div>
                <?php endif; ?>
                <?php if (!empty($profile['education'])): ?>
                    <div class="overview-detail-row">
                        <span class="overview-detail-label">Education</span>
                        <span class="overview-detail-value"><?= Security::e(mb_substr($profile['education'], 0, 50)) ?></span>
                    </div>
                <?php endif; ?>
            </div>

            <a class="lms-btn w-100 mt-3" href="index.php?page=trainee-profile" style="justify-content:center">Edit Profile →</a>
        </div>

        <!-- Quick Links -->
        <div class="overview-panel animate-in">
            <span class="section-label">Quick Actions</span>
            <h2 class="overview-panel-title">Navigate</h2>
            <div class="overview-quick-links">
                <a class="overview-quick-link" href="index.php?page=trainee-dashboard">
                    <span class="overview-quick-icon">📚</span> My Learning
                </a>
                <a class="overview-quick-link" href="index.php?page=trainee-evaluations">
                    <span class="overview-quick-icon">📝</span> Evaluations
                </a>
                <a class="overview-quick-link" href="index.php?page=trainee-certificates">
                    <span class="overview-quick-icon">🏆</span> Certificates
                </a>
                <a class="overview-quick-link" href="index.php?page=messages">
                    <span class="overview-quick-icon">💬</span> Messages
                </a>
            </div>
        </div>
    </div>
</div>
