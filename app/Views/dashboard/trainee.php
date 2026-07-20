<?php use App\Core\Security; use App\Core\View; ?>
<?php View::partial('partials/role-nav'); ?>


    <span class="section-label">Learning Management System</span>
    <?php
        $current = $enrolments[0] ?? null;
        $totalProgress = 0;
        $enrolled = count($enrolments);
        foreach ($enrolments as $row) {
            $totalProgress += (int) $row['progress_percent'];
        }
        $avgProgress = $enrolled ? (int) round($totalProgress / $enrolled) : 0;
    ?>
    <h1 class="section-title">Continue learning<?= $current ? ': ' . Security::e($current['title']) : '' ?></h1>

    <div class="lms-layout">
        <!-- ── Main Content ──────────────────────────── -->
        <div>
            <?php if ($current): ?>
                <div class="lms-card">
                    <div class="course-thumb-area">
                        <span style="position:relative;z-index:1">Video Lesson</span>
                    </div>

                    <div class="lms-btn-group">
                        <a class="lms-btn" href="index.php?page=course-room&id=<?= (int) $current['course_id'] ?>">Download PDF Notes</a>
                        <a class="lms-btn" href="index.php?page=course-room&id=<?= (int) $current['course_id'] ?>">Download Slides</a>
                        <a class="lms-btn lms-btn-primary" href="index.php?page=course-room&id=<?= (int) $current['course_id'] ?>">Submit Assignment</a>
                    </div>
                </div>
            <?php else: ?>
                <div class="lms-card">
                    <p class="text-muted mb-0">You are not enrolled in any courses yet. <a href="index.php?page=courses">Browse courses</a></p>
                </div>
            <?php endif; ?>

            <?php if ($pendingEvaluations): ?>
                <div class="lms-card mt-3" style="border-left: 4px solid var(--ims-primary)">
                    <p class="mb-0"><strong>📋 Pending Evaluations:</strong> You have <?= count($pendingEvaluations) ?> completed course evaluation<?= count($pendingEvaluations) === 1 ? '' : 's' ?> pending. <a href="index.php?page=trainee-evaluations" style="font-weight:700;color:var(--ims-primary)">Evaluate now →</a></p>
                </div>
            <?php endif; ?>

            <!-- Other enrolled courses -->
            <?php if (count($enrolments) > 1): ?>
                <div class="lms-card mt-3">
                    <h2 class="lms-card-title">Other Enrolled Courses</h2>
                    <div class="row g-3">
                        <?php foreach (array_slice($enrolments, 1) as $row): ?>
                            <div class="col-md-6">
                                <?php View::partial('partials/course-card', ['course' => $row, 'actions' => 'trainee']); ?>
                                <div class="completion-bar mt-2"><div class="completion-bar-fill" style="width: <?= (int) $row['progress_percent'] ?>%"></div></div>
                                <span class="small text-muted"><?= (int) $row['progress_percent'] ?>% complete</span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- ── Sidebar ───────────────────────────────── -->
        <div>
            <div class="lms-sidebar">
                <h2 class="lms-sidebar-title">Course contents</h2>
                <ul class="module-list">
                    <?php foreach ($enrolments as $index => $row): ?>
                        <li class="module-list-item">
                            <span>Module <?= $index + 1 ?>: <?= Security::e(mb_substr($row['title'], 0, 22)) ?><?= mb_strlen($row['title']) > 22 ? '…' : '' ?></span>
                            <span class="module-value <?= (int) $row['progress_percent'] === 100 ? 'text-success' : '' ?>"><?= (int) $row['progress_percent'] ?>%</span>
                        </li>
                    <?php endforeach; ?>
                </ul>

                <div class="completion-block">
                    <div class="completion-label">Completion percentage</div>
                    <div class="completion-value"><?= $avgProgress ?>%</div>
                    <div class="completion-bar"><div class="completion-bar-fill" style="width: <?= $avgProgress ?>%"></div></div>
                </div>
            </div>

            <!-- Announcements -->
            <div class="lms-sidebar mt-3">
                <span class="lms-sidebar-label">Latest Updates</span>
                <h2 class="lms-sidebar-title">Announcements</h2>
                <?php foreach (array_slice($announcements, 0, 5) as $item): ?>
                    <div class="completion-item">
                        <div class="completion-item-title"><?= Security::e($item['title']) ?></div>
                        <div class="completion-item-meta"><?= Security::e(Security::excerpt($item['body'], 80)) ?></div>
                    </div>
                <?php endforeach; ?>
                <?php if (empty($announcements)): ?>
                    <p class="text-muted mb-0 small">No announcements at this time.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

