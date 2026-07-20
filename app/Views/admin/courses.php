<?php use App\Core\Security; use App\Core\View; ?>
<?php View::partial('partials/role-nav'); ?>

<span class="section-label">Course Management</span>
<h1 class="section-title">Courses</h1>

<!-- ── Academy Tabs ──────────────────────────────── -->
<?php
$academyFilter = strtoupper(Security::cleanString($_GET['academy'] ?? ''));
$allAcademies = [];
try {
    $db = \App\Core\Model::getDb();
    $allAcademies = $db->query('SELECT * FROM academies ORDER BY FIELD(code, "ADGEA", "IESGA")')->fetchAll();
} catch (\Exception $e) {}
?>
<div class="status-tabs mb-3">
    <a class="status-tab <?= $academyFilter === '' ? 'active' : '' ?>" href="index.php?page=admin-courses">All Courses</a>
    <?php foreach ($allAcademies as $acad): ?>
        <a class="status-tab <?= $academyFilter === $acad['code'] ? 'active' : '' ?>" href="index.php?page=admin-courses&academy=<?= Security::e($acad['code']) ?>"><?= Security::e($acad['code']) ?></a>
    <?php endforeach; ?>
</div>

<!-- ── Filters ───────────────────────────────────── -->
<div class="overview-panel mb-4 animate-in">
    <form class="row g-2" method="get">
        <input type="hidden" name="page" value="admin-courses">
        <?php if ($academyFilter): ?><input type="hidden" name="academy" value="<?= Security::e($academyFilter) ?>"><?php endif; ?>
        <div class="col-sm"><input class="form-control" name="q" value="<?= Security::e($q ?? '') ?>" placeholder="Search courses"></div>
        <div class="col-sm">
            <select class="form-select" name="category"><option value="">All categories</option><?php foreach ($categories as $cat): ?><option><?= Security::e($cat['category']) ?></option><?php endforeach; ?></select>
        </div>
        <div class="col-sm">
            <select class="form-select" name="status"><option value="">All status</option><option>draft</option><option>published</option><option>active</option><option>completed</option><option>archived</option></select>
        </div>
        <div class="col-sm-auto"><button class="btn btn-primary w-100">Filter</button></div>
    </form>
</div>

<div class="row g-4">
    <!-- Create/Edit Panel -->
    <div class="col-lg-4">
        <div class="overview-panel animate-in">
            <span class="section-label"><?= $editing ? 'Edit' : 'Create' ?></span>
            <h2 class="overview-panel-title"><?= $editing ? 'Edit Course' : 'Create Course' ?></h2>
            <form method="post" action="index.php?page=save-course" enctype="multipart/form-data">
                <input type="hidden" name="_csrf" value="<?= Security::e(Security::csrfToken()) ?>">
                <input type="hidden" name="id" value="<?= (int) ($editing['id'] ?? 0) ?>">
                <input type="hidden" name="existing_thumbnail" value="<?= Security::e($editing['thumbnail_image'] ?? '') ?>">
                <input class="form-control mb-2" name="title" value="<?= Security::e($editing['title'] ?? '') ?>" placeholder="Course title" required>
                <input class="form-control mb-2" name="category" value="<?= Security::e($editing['category'] ?? '') ?>" placeholder="Category" required>
                <select class="form-select mb-2" name="academy_id">
                    <option value="">Assign to Academy</option>
                    <?php foreach ($allAcademies as $acad): ?>
                        <option value="<?= (int) $acad['id'] ?>" <?= (int) ($editing['academy_id'] ?? 0) === (int) $acad['id'] ? 'selected' : '' ?>><?= Security::e($acad['code']) ?> — <?= Security::e($acad['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <textarea class="form-control mb-2" name="description" placeholder="Description" rows="4"><?= Security::e($editing['description'] ?? '') ?></textarea>
                <label class="form-label small text-muted">Thumbnail Image</label>
                <input class="form-control mb-2" type="file" name="thumbnail" accept=".jpg,.jpeg,.png,.webp">
                <div class="row g-2"><div class="col"><input class="form-control mb-2" type="date" name="start_date" value="<?= Security::e($editing['start_date'] ?? '') ?>"></div><div class="col"><input class="form-control mb-2" type="date" name="end_date" value="<?= Security::e($editing['end_date'] ?? '') ?>"></div></div>
                <div class="row g-2"><div class="col"><input class="form-control mb-2" type="number" name="capacity" placeholder="Capacity" value="<?= (int) ($editing['capacity'] ?? 25) ?>"></div><div class="col"><input class="form-control mb-2" type="number" name="max_participants" placeholder="Max participants" value="<?= (int) ($editing['max_participants'] ?? $editing['capacity'] ?? 25) ?>"></div></div>
                <input class="form-control mb-2" type="number" step="0.01" name="fee" placeholder="Fee" value="<?= Security::e((string) ($editing['fee'] ?? 0)) ?>">
                <select class="form-select mb-2" name="instructor_id"><option value="">Assign instructor</option><?php foreach ($instructors as $i): ?><option value="<?= (int) $i['id'] ?>" <?= (int) ($editing['instructor_id'] ?? 0) === (int) $i['id'] ? 'selected' : '' ?>><?= Security::e($i['name']) ?></option><?php endforeach; ?></select>
                <select class="form-select mb-2" name="status"><?php foreach (['draft','published','active','completed','archived'] as $state): ?><option value="<?= $state ?>" <?= ($editing['status'] ?? 'draft') === $state ? 'selected' : '' ?>><?= ucfirst($state) ?></option><?php endforeach; ?></select>
                <select class="form-select mb-3" name="course_status"><?php foreach (['draft','published','active','completed','archived'] as $state): ?><option value="<?= $state ?>" <?= ($editing['course_status'] ?? $editing['status'] ?? 'draft') === $state ? 'selected' : '' ?>>Card status: <?= ucfirst($state) ?></option><?php endforeach; ?></select>
                <button class="btn btn-primary w-100">Save Course</button>
            </form>
        </div>
    </div>

    <!-- Course Grid -->
    <div class="col-lg-8">
        <?php
        // Filter courses by academy if tab selected
        $filteredCourses = $courses;
        if ($academyFilter) {
            $filteredCourses = array_filter($courses, fn($c) => strtoupper($c['academy_code'] ?? '') === $academyFilter);
        }
        ?>
        <?php if ($filteredCourses): ?>
            <div class="row g-3">
                <?php foreach ($filteredCourses as $course): ?>
                    <div class="col-md-6 animate-in"><?php View::partial('partials/course-card', ['course' => $course, 'actions' => 'admin']); ?></div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="overview-panel">
                <div class="empty-state">
                    <div class="empty-state-icon">📚</div>
                    <div class="empty-state-title">No courses found</div>
                    <p class="text-muted small">Create a new course or adjust your filters.</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
