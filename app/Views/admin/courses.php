<?php use App\Core\Security; use App\Core\View; ?>
<?php View::partial('partials/role-nav'); ?>

<!-- ═══ Course Management — Professional Admin UI ═══ -->
<div class="cm-container" id="cmContainer">

    <!-- ── Breadcrumb ── -->
    <nav class="cm-breadcrumb" aria-label="breadcrumb">
        <a href="index.php?page=admin-dashboard">Dashboard</a>
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
        <span>Course Management</span>
    </nav>

    <!-- ════════════════════════════════════════════════════
         VIEW 1: COURSE LIST (default)
         ════════════════════════════════════════════════════ -->
    <div class="cm-view cm-view-active" id="cmViewList">

        <!-- Header Row -->
        <div class="cm-header">
            <div class="cm-header-left">
                <h1 class="cm-title">Course Management</h1>
                <p class="cm-subtitle">Manage academies, course offerings, assignments, and capacity</p>
            </div>
            <button class="cm-btn cm-btn-primary" id="cmBtnCreateCourse" type="button">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Create Course
            </button>
        </div>

        <!-- Stat Cards -->
        <div class="cm-stats">
            <div class="cm-stat-card">
                <div class="cm-stat-icon cm-stat-icon-all">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20M4 19.5A2.5 2.5 0 0 0 6.5 22H20M4 19.5V3A2.5 2.5 0 0 1 6.5 .5H20v16.5H6.5a2.5 2.5 0 0 0-2.5 2.5z"/></svg>
                </div>
                <div class="cm-stat-info">
                    <span class="cm-stat-value"><?= (int) $totalAll ?></span>
                    <span class="cm-stat-label">Total Courses</span>
                </div>
            </div>
            <div class="cm-stat-card">
                <div class="cm-stat-icon cm-stat-icon-active">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                </div>
                <div class="cm-stat-info">
                    <span class="cm-stat-value"><?= (int) $totalActive ?></span>
                    <span class="cm-stat-label">Active</span>
                </div>
            </div>
            <div class="cm-stat-card">
                <div class="cm-stat-icon cm-stat-icon-draft">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                </div>
                <div class="cm-stat-info">
                    <span class="cm-stat-value"><?= (int) $totalDraft ?></span>
                    <span class="cm-stat-label">Drafts</span>
                </div>
            </div>
            <div class="cm-stat-card">
                <div class="cm-stat-icon cm-stat-icon-archived">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 8v13H3V8M1 3h22v5H1z"/><line x1="10" y1="12" x2="14" y2="12"/></svg>
                </div>
                <div class="cm-stat-info">
                    <span class="cm-stat-value"><?= (int) $totalArchived ?></span>
                    <span class="cm-stat-label">Archived</span>
                </div>
            </div>
        </div>

        <!-- Academy Tabs & Filters -->
        <?php
        $academyFilter = strtoupper(Security::cleanString($_GET['academy'] ?? ''));
        $allAcademies = [];
        try {
            $db = \App\Core\Model::getDb();
            $allAcademies = $db->query('SELECT * FROM academies ORDER BY FIELD(code, "ADGEA", "IESGA")')->fetchAll();
        } catch (\Exception $e) {}
        ?>
        <div class="cm-toolbar">
            <div class="cm-tabs" role="tablist">
                <a class="cm-tab <?= $academyFilter === '' ? 'cm-tab-active' : '' ?>"
                   href="index.php?page=admin-courses&q=<?= urlencode((string) $q) ?>&status=<?= urlencode((string) $status) ?>&category=<?= urlencode((string) $category) ?>"
                   role="tab" aria-selected="<?= $academyFilter === '' ? 'true' : 'false' ?>">
                    All Courses
                    <span class="cm-tab-badge"><?= (int) $totalAll ?></span>
                </a>
                <a class="cm-tab <?= $academyFilter === 'ADGEA' ? 'cm-tab-active' : '' ?>"
                   href="index.php?page=admin-courses&academy=ADGEA&q=<?= urlencode((string) $q) ?>&status=<?= urlencode((string) $status) ?>&category=<?= urlencode((string) $category) ?>"
                   role="tab" aria-selected="<?= $academyFilter === 'ADGEA' ? 'true' : 'false' ?>">
                    ADGEA
                    <span class="cm-tab-badge"><?= (int) $totalAdgea ?></span>
                </a>
                <a class="cm-tab <?= $academyFilter === 'IESGA' ? 'cm-tab-active' : '' ?>"
                   href="index.php?page=admin-courses&academy=IESGA&q=<?= urlencode((string) $q) ?>&status=<?= urlencode((string) $status) ?>&category=<?= urlencode((string) $category) ?>"
                   role="tab" aria-selected="<?= $academyFilter === 'IESGA' ? 'true' : 'false' ?>">
                    IESGA
                    <span class="cm-tab-badge"><?= (int) $totalIesga ?></span>
                </a>
            </div>

            <form class="cm-filters" method="get">
                <input type="hidden" name="page" value="admin-courses">
                <?php if ($academyFilter): ?>
                    <input type="hidden" name="academy" value="<?= Security::e($academyFilter) ?>">
                <?php endif; ?>
                
                <div class="cm-search-box">
                    <svg class="cm-search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    <input class="cm-search-input" name="q" value="<?= Security::e((string) $q) ?>" placeholder="Search by course name or category…" autocomplete="off">
                </div>

                <select class="cm-select" name="category">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= Security::e($cat['category']) ?>" <?= $category === $cat['category'] ? 'selected' : '' ?>><?= Security::e($cat['category']) ?></option>
                    <?php endforeach; ?>
                </select>

                <select class="cm-select" name="status">
                    <option value="">All Status</option>
                    <?php foreach (['draft','published','active','completed','archived'] as $state): ?>
                        <option value="<?= $state ?>" <?= $status === $state ? 'selected' : '' ?>><?= ucfirst($state) ?></option>
                    <?php endforeach; ?>
                </select>

                <button class="cm-btn cm-btn-secondary" type="submit">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                    Filter
                </button>
            </form>
        </div>

        <!-- Course Cards Grid -->
        <?php
        $filteredCourses = $courses;
        if ($academyFilter) {
            $filteredCourses = array_filter($courses, fn($c) => strtoupper($c['academy_code'] ?? '') === $academyFilter);
        }
        ?>

        <?php if (empty($filteredCourses)): ?>
            <div class="cm-empty-state">
                <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20M4 19.5A2.5 2.5 0 0 0 6.5 22H20M4 19.5V3A2.5 2.5 0 0 1 6.5 .5H20v16.5H6.5a2.5 2.5 0 0 0-2.5 2.5z"/></svg>
                <h3>No courses found</h3>
                <p>Try adjusting your search filters or add a new course.</p>
                <button class="cm-btn cm-btn-primary" onclick="document.getElementById('cmBtnCreateCourse').click()">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    Create Course
                </button>
            </div>
        <?php else: ?>
            <div class="cm-grid">
                <?php foreach ($filteredCourses as $course): ?>
                    <?php View::partial('partials/course-card', ['course' => $course, 'actions' => 'admin']); ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div><!-- /cmViewList -->


    <!-- ════════════════════════════════════════════════════
         VIEW 2: CREATE COURSE
         ════════════════════════════════════════════════════ -->
    <div class="cm-view" id="cmViewCreate">
        <div class="cm-view-header">
            <button class="cm-back-btn" type="button" id="cmBtnBackFromCreate">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                Back to Course List
            </button>
            <h2 class="cm-view-title">Create New Course</h2>
            <p class="cm-view-subtitle">Fill in the details below to add a new course offering</p>
        </div>

        <div class="cm-form-card">
            <form method="post" action="index.php?page=save-course" id="cmCreateForm" enctype="multipart/form-data">
                <input type="hidden" name="_csrf" value="<?= Security::e(Security::csrfToken()) ?>">
                <input type="hidden" name="id" value="0">
                <input type="hidden" name="existing_thumbnail" value="">

                <div class="cm-form-grid">
                    <div class="cm-form-group cm-form-full">
                        <label class="cm-label" for="createTitle">Course Title <span class="cm-required">*</span></label>
                        <input class="cm-input" id="createTitle" name="title" placeholder="Enter course title" required>
                    </div>
                    <div class="cm-form-group">
                        <label class="cm-label" for="createCategory">Category <span class="cm-required">*</span></label>
                        <input class="cm-input" id="createCategory" name="category" placeholder="E.g. Engineering, Technical" required>
                    </div>
                    <div class="cm-form-group">
                        <label class="cm-label" for="createAcademy">Assign Academy</label>
                        <select class="cm-input cm-select-input" id="createAcademy" name="academy_id">
                            <option value="">No Academy (General)</option>
                            <?php foreach ($allAcademies as $acad): ?>
                                <option value="<?= (int) $acad['id'] ?>"><?= Security::e($acad['code']) ?> — <?= Security::e($acad['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="cm-form-group cm-form-full">
                        <label class="cm-label" for="createDescription">Course Description</label>
                        <textarea class="cm-input cm-textarea" id="createDescription" name="description" rows="4" placeholder="Enter course details, content, syllabus, and objectives..."></textarea>
                    </div>
                    <div class="cm-form-group">
                        <label class="cm-label" for="createStartDate">Start Date</label>
                        <input class="cm-input" id="createStartDate" type="date" name="start_date">
                    </div>
                    <div class="cm-form-group">
                        <label class="cm-label" for="createEndDate">End Date</label>
                        <input class="cm-input" id="createEndDate" type="date" name="end_date">
                    </div>
                    <div class="cm-form-group">
                        <label class="cm-label" for="createCapacity">Total Capacity</label>
                        <input class="cm-input" id="createCapacity" type="number" name="capacity" value="25" min="1">
                    </div>
                    <div class="cm-form-group">
                        <label class="cm-label" for="createMaxPart">Max Participants</label>
                        <input class="cm-input" id="createMaxPart" type="number" name="max_participants" value="25" min="1">
                    </div>
                    <div class="cm-form-group">
                        <label class="cm-label" for="createFee">Course Fee (RM)</label>
                        <input class="cm-input" id="createFee" type="number" step="0.01" name="fee" value="0.00" min="0">
                    </div>
                    <div class="cm-form-group">
                        <label class="cm-label" for="createInstructor">Assign Instructor</label>
                        <select class="cm-input cm-select-input" id="createInstructor" name="instructor_id">
                            <option value="">To be assigned (General)</option>
                            <?php foreach ($instructors as $inst): ?>
                                <option value="<?= (int) $inst['id'] ?>"><?= Security::e($inst['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="cm-form-group">
                        <label class="cm-label" for="createStatus">Flow Status</label>
                        <select class="cm-input cm-select-input" id="createStatus" name="status">
                            <?php foreach (['draft','published','active','completed','archived'] as $state): ?>
                                <option value="<?= $state ?>"><?= ucfirst($state) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="cm-form-group">
                        <label class="cm-label" for="createCourseStatus">Card Display Status</label>
                        <select class="cm-input cm-select-input" id="createCourseStatus" name="course_status">
                            <?php foreach (['draft','published','active','completed','archived'] as $state): ?>
                                <option value="<?= $state ?>"><?= ucfirst($state) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="cm-form-group cm-form-full">
                        <label class="cm-label" for="createThumbnail">Course Thumbnail Image</label>
                        <input class="cm-input" id="createThumbnail" type="file" name="thumbnail" accept=".jpg,.jpeg,.png,.webp">
                        <span class="cm-hint">Recommended dimensions: 800x450 pixels (16:9 ratio). Formats: JPG, PNG, WEBP.</span>
                    </div>
                </div>

                <div class="cm-form-actions">
                    <button class="cm-btn cm-btn-ghost" type="button" id="cmCancelCreate">Cancel</button>
                    <button class="cm-btn cm-btn-primary" type="submit">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        Create Course
                    </button>
                </div>
            </form>
        </div>
    </div><!-- /cmViewCreate -->

</div><!-- /cmContainer -->


<!-- ════════════════════════════════════════════════════
     VIEW COURSE DETAIL — Slide-in Panel
     ════════════════════════════════════════════════════ -->
<div class="cm-panel-overlay" id="cmPanelOverlay" data-no-motion></div>
<div class="cm-detail-panel" id="cmDetailPanel" data-no-motion>
    <div class="cm-panel-header">
        <h3 class="cm-panel-title">Course Details</h3>
        <button class="cm-panel-close" id="cmPanelClose" type="button" aria-label="Close">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
    </div>
    <div class="cm-panel-body" id="cmPanelBody">
        <div class="cm-panel-loading" id="cmPanelLoading">
            <div class="cm-spinner"></div>
            <span>Loading course details…</span>
        </div>
        <div class="cm-panel-content" id="cmPanelContent" style="display:none;">
            <!-- Populated by JavaScript -->
        </div>
    </div>
</div>


<!-- ════════════════════════════════════════════════════
     EDIT COURSE — Modal
     ════════════════════════════════════════════════════ -->
<div class="cm-modal-overlay" id="cmEditOverlay" data-no-motion>
    <div class="cm-modal" id="cmEditModal">
        <div class="cm-modal-header">
            <h3 class="cm-modal-title">Edit Course</h3>
            <button class="cm-modal-close" id="cmEditClose" type="button" aria-label="Close">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <form method="post" action="index.php?page=save-course" id="cmEditForm" enctype="multipart/form-data">
            <input type="hidden" name="_csrf" value="<?= Security::e(Security::csrfToken()) ?>">
            <input type="hidden" name="id" id="editCourseId" value="0">
            <input type="hidden" name="existing_thumbnail" id="editExistingThumbnail" value="">
            <div class="cm-modal-body">
                <div class="cm-form-grid">
                    <div class="cm-form-group cm-form-full">
                        <label class="cm-label" for="editTitle">Course Title <span class="cm-required">*</span></label>
                        <input class="cm-input" id="editTitle" name="title" required>
                    </div>
                    <div class="cm-form-group">
                        <label class="cm-label" for="editCategory">Category <span class="cm-required">*</span></label>
                        <input class="cm-input" id="editCategory" name="category" required>
                    </div>
                    <div class="cm-form-group">
                        <label class="cm-label" for="editAcademy">Assign Academy</label>
                        <select class="cm-input cm-select-input" id="editAcademy" name="academy_id">
                            <option value="">General / No Academy</option>
                            <?php foreach ($allAcademies as $acad): ?>
                                <option value="<?= (int) $acad['id'] ?>"><?= Security::e($acad['code']) ?> — <?= Security::e($acad['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="cm-form-group cm-form-full">
                        <label class="cm-label" for="editDescription">Course Description</label>
                        <textarea class="cm-input cm-textarea" id="editDescription" name="description" rows="4"></textarea>
                    </div>
                    <div class="cm-form-group">
                        <label class="cm-label" for="editStartDate">Start Date</label>
                        <input class="cm-input" id="editStartDate" type="date" name="start_date">
                    </div>
                    <div class="cm-form-group">
                        <label class="cm-label" for="editEndDate">End Date</label>
                        <input class="cm-input" id="editEndDate" type="date" name="end_date">
                    </div>
                    <div class="cm-form-group">
                        <label class="cm-label" for="editCapacity">Total Capacity</label>
                        <input class="cm-input" id="editCapacity" type="number" name="capacity">
                    </div>
                    <div class="cm-form-group">
                        <label class="cm-label" for="editMaxPart">Max Participants</label>
                        <input class="cm-input" id="editMaxPart" type="number" name="max_participants">
                    </div>
                    <div class="cm-form-group">
                        <label class="cm-label" for="editFee">Course Fee (RM)</label>
                        <input class="cm-input" id="editFee" type="number" step="0.01" name="fee">
                    </div>
                    <div class="cm-form-group">
                        <label class="cm-label" for="editInstructor">Assign Instructor</label>
                        <select class="cm-input cm-select-input" id="editInstructor" name="instructor_id">
                            <option value="">To be assigned</option>
                            <?php foreach ($instructors as $inst): ?>
                                <option value="<?= (int) $inst['id'] ?>"><?= Security::e($inst['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="cm-form-group">
                        <label class="cm-label" for="editStatus">Flow Status</label>
                        <select class="cm-input cm-select-input" id="editStatus" name="status">
                            <?php foreach (['draft','published','active','completed','archived'] as $state): ?>
                                <option value="<?= $state ?>"><?= ucfirst($state) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="cm-form-group">
                        <label class="cm-label" for="editCourseStatus">Card Display Status</label>
                        <select class="cm-input cm-select-input" id="editCourseStatus" name="course_status">
                            <?php foreach (['draft','published','active','completed','archived'] as $state): ?>
                                <option value="<?= $state ?>"><?= ucfirst($state) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="cm-form-group cm-form-full">
                        <label class="cm-label" for="editThumbnail">Change Thumbnail Image</label>
                        <input class="cm-input" id="editThumbnail" type="file" name="thumbnail" accept=".jpg,.jpeg,.png,.webp">
                        <span class="cm-hint">Leave blank to keep existing thumbnail image.</span>
                    </div>
                </div>
            </div>
            <div class="cm-modal-footer">
                <button class="cm-btn cm-btn-ghost" type="button" id="cmEditCancel">Cancel</button>
                <button class="cm-btn cm-btn-primary" type="submit">Save Changes</button>
            </div>
        </form>
    </div>
</div>


<!-- ════════════════════════════════════════════════════
     DELETE CONFIRMATION — Modal
     ════════════════════════════════════════════════════ -->
<div class="cm-modal-overlay" id="cmDeleteOverlay" data-no-motion>
    <div class="cm-modal cm-modal-sm">
        <div class="cm-modal-header cm-modal-header-danger">
            <div class="cm-delete-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
            </div>
            <h3 class="cm-modal-title">Delete Course</h3>
        </div>
        <div class="cm-modal-body cm-text-center">
            <p>Are you sure you want to delete <strong id="cmDeleteTitle"></strong>?</p>
            <p class="cm-text-muted">This action cannot be undone. All course materials and enrollments will be permanently removed.</p>
        </div>
        <form method="post" action="index.php?page=delete-course" id="cmDeleteForm">
            <input type="hidden" name="_csrf" value="<?= Security::e(Security::csrfToken()) ?>">
            <input type="hidden" name="id" id="deleteCourseId" value="0">
            <div class="cm-modal-footer cm-modal-footer-center">
                <button class="cm-btn cm-btn-ghost" type="button" id="cmDeleteCancel">Cancel</button>
                <button class="cm-btn cm-btn-danger" type="submit">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                    Delete Permanently
                </button>
            </div>
        </form>
    </div>
</div>
