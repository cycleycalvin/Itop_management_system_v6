<?php use App\Core\Auth; use App\Core\Security; use App\Core\View; ?>
<?php View::partial('partials/role-nav'); ?>

<span class="section-label">Instructor Dashboard</span>
<h1 class="section-title">Welcome back, <?= Security::e(Auth::user()['name']) ?></h1>

<!-- ── Stat Cards ────────────────────────────────── -->
<div class="overview-stats">
    <div class="overview-stat-card animate-in">
        <span class="overview-stat-label">Assigned Courses</span>
        <strong class="overview-stat-value"><?= count($courses) ?></strong>
    </div>
    <div class="overview-stat-card accent-orange animate-in">
        <span class="overview-stat-label">Pending Grading</span>
        <strong class="overview-stat-value"><?= count(array_filter($submissions, fn($s) => ($s['status'] ?? '') === 'submitted')) ?></strong>
    </div>
    <div class="overview-stat-card accent-green animate-in">
        <span class="overview-stat-label">Total Submissions</span>
        <strong class="overview-stat-value"><?= count($submissions) ?></strong>
    </div>
    <div class="overview-stat-card animate-in">
        <span class="overview-stat-label">Total Trainees</span>
        <strong class="overview-stat-value"><?php
            $traineeCount = 0;
            foreach ($courses as $c) { $traineeCount += (int) ($c['participant_count'] ?? 0); }
            echo $traineeCount;
        ?></strong>
    </div>
</div>

<!-- ── Two-Column Content ────────────────────────── -->
<div class="overview-columns">
    <!-- Left: Assigned Courses -->
    <div class="overview-main">
        <div class="overview-panel animate-in">
            <span class="section-label">My Courses</span>
            <h2 class="overview-panel-title">Assigned Courses</h2>
            <div class="row g-3">
                <?php foreach ($courses as $course): ?>
                    <div class="col-md-6"><?php View::partial('partials/course-card', ['course' => $course, 'actions' => 'instructor']); ?></div>
                <?php endforeach; ?>
                <?php if (!$courses): ?>
                    <div class="col-12">
                        <div class="empty-state">
                            <div class="empty-state-icon">📚</div>
                            <div class="empty-state-title">No assigned courses</div>
                            <p class="text-muted small">Select an available course from the "Teach a Course" panel on the right.</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Right: Actions & Creation Panel -->
    <div class="overview-aside">
        <?php if (!empty($editing)): ?>
            <!-- Edit Course Panel -->
            <div class="overview-panel animate-in border-warning" style="border-left: 4px solid var(--ims-warning)">
                <span class="section-label">Edit Course Details</span>
                <h2 class="overview-panel-title">Edit: <?= Security::e($editing['title']) ?></h2>
                <?php
                $allAcademies = [];
                try {
                    $db = \App\Core\Model::getDb();
                    $allAcademies = $db->query('SELECT * FROM academies ORDER BY code')->fetchAll();
                } catch (\Exception $e) {}
                ?>
                <form method="post" action="index.php?page=save-course" enctype="multipart/form-data">
                    <input type="hidden" name="_csrf" value="<?= Security::e(Security::csrfToken()) ?>">
                    <input type="hidden" name="id" value="<?= (int) $editing['id'] ?>">
                    <input type="hidden" name="existing_thumbnail" value="<?= Security::e($editing['thumbnail_image'] ?? '') ?>">
                    
                    <div class="mb-2">
                        <label class="form-label small text-muted mb-1">Course Title</label>
                        <input class="form-control" name="title" value="<?= Security::e($editing['title']) ?>" required>
                    </div>
                    
                    <div class="mb-2">
                        <label class="form-label small text-muted mb-1">Category</label>
                        <input class="form-control" name="category" value="<?= Security::e($editing['category']) ?>" required>
                    </div>
                    
                    <div class="mb-2">
                        <label class="form-label small text-muted mb-1">Academy</label>
                        <select class="form-select" name="academy_id" required>
                            <?php foreach ($allAcademies as $acad): ?>
                                <option value="<?= (int) $acad['id'] ?>" <?= (int) ($editing['academy_id'] ?? 0) === (int) $acad['id'] ? 'selected' : '' ?>><?= Security::e($acad['code']) ?> — <?= Security::e($acad['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-2">
                        <label class="form-label small text-muted mb-1">Description</label>
                        <textarea class="form-control" name="description" rows="3"><?= Security::e($editing['description']) ?></textarea>
                    </div>
                    
                    <label class="form-label small text-muted mb-1">Change Thumbnail Image</label>
                    <input class="form-control mb-2" type="file" name="thumbnail" accept=".jpg,.jpeg,.png,.webp">
                    
                    <div class="row g-2 mb-2">
                        <div class="col">
                            <label class="form-label small text-muted mb-1">Start Date</label>
                            <input class="form-control" type="date" name="start_date" value="<?= Security::e($editing['start_date'] ?? '') ?>">
                        </div>
                        <div class="col">
                            <label class="form-label small text-muted mb-1">End Date</label>
                            <input class="form-control" type="date" name="end_date" value="<?= Security::e($editing['end_date'] ?? '') ?>">
                        </div>
                    </div>
                    
                    <div class="row g-2 mb-3">
                        <div class="col">
                            <label class="form-label small text-muted mb-1">Capacity</label>
                            <input class="form-control" type="number" name="capacity" value="<?= (int) ($editing['capacity'] ?? 25) ?>" required>
                        </div>
                        <div class="col">
                            <label class="form-label small text-muted mb-1">Fee (RM)</label>
                            <input class="form-control" type="number" step="0.01" name="fee" value="<?= Security::e((string) ($editing['fee'] ?? 0)) ?>" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label small text-muted mb-1">Status</label>
                        <select class="form-select" name="status">
                            <option value="draft" <?= ($editing['status'] ?? '') === 'draft' ? 'selected' : '' ?>>Draft</option>
                            <option value="published" <?= ($editing['status'] ?? '') === 'published' ? 'selected' : '' ?>>Published</option>
                            <option value="active" <?= ($editing['status'] ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
                        </select>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button class="btn btn-primary flex-fill btn-sm">Save Changes</button>
                        <a href="index.php?page=dashboard" class="btn btn-outline-secondary btn-sm">Cancel</a>
                    </div>
                </form>
            </div>
        <?php else: ?>
            <!-- Claim/Teach Course Panel -->
            <div class="overview-panel animate-in">
                <span class="section-label">Course Assignment</span>
                <h2 class="overview-panel-title">Teach a Course</h2>
                <?php
                $unassignedCourses = [];
                try {
                    $db = \App\Core\Model::getDb();
                    $unassignedCourses = $db->query('SELECT * FROM courses WHERE instructor_id IS NULL AND status IN ("published", "active") ORDER BY title')->fetchAll();
                } catch (\Exception $e) {}
                ?>
                <?php if (!empty($unassignedCourses)): ?>
                    <form method="post" action="index.php?page=instructor-claim-course">
                        <input type="hidden" name="_csrf" value="<?= Security::e(Security::csrfToken()) ?>">
                        <div class="mb-3">
                            <label class="form-label small text-muted mb-2">Select from Admin-provided courses to assign yourself as the instructor:</label>
                            <select class="form-select" name="course_id" required>
                                <option value="">Choose available course...</option>
                                <?php foreach ($unassignedCourses as $c): ?>
                                    <option value="<?= (int) $c['id'] ?>"><?= Security::e($c['title']) ?> (<?= Security::e($c['category']) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button class="btn btn-primary w-100 btn-sm">Teach This Course</button>
                    </form>
                <?php else: ?>
                    <div class="alert alert-light py-2 px-3 small mb-0 border">
                        💡 All public courses are currently assigned. Contact administration if you need to create or assign new courses.
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- Grading Panel -->
        <div class="overview-panel animate-in mt-3">
            <span class="section-label" style="color:#c0392b">Grading</span>
            <h2 class="overview-panel-title">Pending / Recent</h2>
            <?php foreach (array_slice($submissions, 0, 5) as $sub): ?>
                <div class="completion-item">
                    <div class="completion-item-title"><?= Security::e($sub['trainee_name']) ?></div>
                    <div class="completion-item-meta">
                        <?= Security::e($sub['assignment_title']) ?>
                        <span class="completion-item-badge <?= ($sub['status'] ?? '') === 'submitted' ? 'in-progress' : '' ?>"><?= Security::e($sub['status']) ?></span>
                    </div>
                </div>
            <?php endforeach; ?>
            <?php if (empty($submissions)): ?>
                <p class="text-muted small mb-0">No pending submissions.</p>
            <?php endif; ?>
        </div>

        <!-- Navigation Panel -->
        <div class="overview-panel animate-in mt-3">
            <span class="section-label">Quick Actions</span>
            <h2 class="overview-panel-title">Navigate</h2>
            <div class="overview-quick-links">
                <a class="overview-quick-link" href="index.php?page=instructor-enrolments">
                    <span class="overview-quick-icon">📋</span> Enrolments
                </a>
                <a class="overview-quick-link" href="index.php?page=announcements-manage">
                    <span class="overview-quick-icon">📢</span> Announcements
                </a>
                <a class="overview-quick-link" href="index.php?page=messages">
                    <span class="overview-quick-icon">💬</span> Messages
                </a>
                <a class="overview-quick-link" href="index.php?page=reports">
                    <span class="overview-quick-icon">📊</span> Reports
                </a>
            </div>
        </div>
    </div>
</div>
