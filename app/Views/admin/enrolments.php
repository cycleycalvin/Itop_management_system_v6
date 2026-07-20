<?php use App\Core\Security; use App\Core\View; ?>
<?php View::partial('partials/role-nav'); ?>

<!-- ═══ Enrolment Management — Professional Admin UI ═══ -->
<div class="em-container" id="emContainer">

    <!-- ── Breadcrumb ── -->
    <nav class="em-breadcrumb" aria-label="breadcrumb">
        <a href="index.php?page=admin-dashboard">Dashboard</a>
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
        <span>Enrolment Management</span>
    </nav>

    <!-- Header Row -->
    <div class="em-header">
        <div class="em-header-left">
            <h1 class="em-title">Enrolment Management</h1>
            <p class="cm-subtitle">Review registration requests, assign trainees to courses, and manage student lifecycle status</p>
        </div>
    </div>

    <!-- Stat Cards -->
    <div class="em-stats">
        <div class="em-stat-card">
            <div class="em-stat-icon em-stat-icon-all">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </div>
            <div class="em-stat-info">
                <span class="em-stat-value"><?= (int) $totalAll ?></span>
                <span class="em-stat-label">Total Requests</span>
            </div>
        </div>
        <div class="em-stat-card">
            <div class="em-stat-icon em-stat-icon-pending">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            </div>
            <div class="em-stat-info">
                <span class="em-stat-value"><?= (int) $totalPending ?></span>
                <span class="em-stat-label">Pending Review</span>
            </div>
        </div>
        <div class="em-stat-card">
            <div class="em-stat-icon em-stat-icon-active">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            </div>
            <div class="em-stat-info">
                <span class="em-stat-value"><?= (int) $totalActive ?></span>
                <span class="em-stat-label">Active Trainees</span>
            </div>
        </div>
        <div class="em-stat-card">
            <div class="em-stat-icon em-stat-icon-completed">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="7"/><polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"/></svg>
            </div>
            <div class="em-stat-info">
                <span class="em-stat-value"><?= (int) $totalCompleted ?></span>
                <span class="em-stat-label">Completed</span>
            </div>
        </div>
    </div>

    <!-- Toolbar: Tabs + Search Filters -->
    <?php
    $statusFilter = Security::cleanString($_GET['status'] ?? '');
    $q = Security::cleanString($_GET['q'] ?? '');
    ?>
    <div class="em-toolbar">
        <div class="em-tabs" role="tablist">
            <a class="em-tab <?= $statusFilter === '' ? 'em-tab-active' : '' ?>"
               href="index.php?page=admin-enrolments&q=<?= urlencode($q) ?>">
                All Requests
                <span class="em-tab-badge"><?= (int) $totalAll ?></span>
            </a>
            <a class="em-tab <?= $statusFilter === 'pending' ? 'em-tab-active' : '' ?>"
               href="index.php?page=admin-enrolments&status=pending&q=<?= urlencode($q) ?>">
                Pending
                <span class="em-tab-badge"><?= (int) $totalPending ?></span>
            </a>
            <a class="em-tab <?= $statusFilter === 'active' ? 'em-tab-active' : '' ?>"
               href="index.php?page=admin-enrolments&status=active&q=<?= urlencode($q) ?>">
                Active
                <span class="em-tab-badge"><?= (int) $totalActive ?></span>
            </a>
            <a class="em-tab <?= $statusFilter === 'completed' ? 'em-tab-active' : '' ?>"
               href="index.php?page=admin-enrolments&status=completed&q=<?= urlencode($q) ?>">
                Completed
                <span class="em-tab-badge"><?= (int) $totalCompleted ?></span>
            </a>
            <a class="em-tab <?= $statusFilter === 'rejected' ? 'em-tab-active' : '' ?>"
               href="index.php?page=admin-enrolments&status=rejected&q=<?= urlencode($q) ?>">
                Rejected
                <span class="em-tab-badge"><?= (int) $totalRejected ?></span>
            </a>
        </div>

        <form class="em-filters" method="get">
            <input type="hidden" name="page" value="admin-enrolments">
            <?php if ($statusFilter): ?>
                <input type="hidden" name="status" value="<?= Security::e($statusFilter) ?>">
            <?php endif; ?>

            <div class="em-search-box">
                <svg class="em-search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input class="em-search-input" name="q" value="<?= Security::e($q) ?>" placeholder="Search by trainee name, email, or course…" autocomplete="off">
            </div>
            
            <button class="em-btn em-btn-secondary" type="submit">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                Filter
            </button>
        </form>
    </div>

    <!-- Enrolments Table Container -->
    <div class="em-table-container">
        <?php
        // Filter array manually based on criteria
        $filtered = $enrolments;
        if ($statusFilter) {
            $filtered = array_filter($enrolments, fn($r) => ($r['status'] ?? '') === $statusFilter);
        }
        if ($q !== '') {
            $ql = strtolower($q);
            $filtered = array_filter($filtered, function ($r) use ($ql) {
                return strpos(strtolower($r['trainee_name'] ?? ''), $ql) !== false ||
                       strpos(strtolower($r['email'] ?? ''), $ql) !== false ||
                       strpos(strtolower($r['course_title'] ?? ''), $ql) !== false;
            });
        }
        ?>

        <?php if (empty($filtered)): ?>
            <div class="em-empty-state">
                <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                <h3>No requests found</h3>
                <p>No enrolments match the current status filter or search parameters.</p>
            </div>
        <?php else: ?>
            <table class="em-table">
                <thead>
                    <tr>
                        <th>Trainee</th>
                        <th>Course</th>
                        <th>Instructor</th>
                        <th>Status</th>
                        <th>Requested On</th>
                        <th class="em-text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($filtered as $row):
                        $initials = '';
                        $words = explode(' ', $row['trainee_name'] ?? '');
                        foreach ($words as $w) {
                            if (strlen($w) > 0) $initials .= strtoupper($w[0]);
                        }
                        $initials = substr($initials, 0, 2);
                        
                        $statusColors = [
                            'active' => 'em-status-active',
                            'completed' => 'em-status-completed',
                            'pending' => 'em-status-pending',
                            'rejected' => 'em-status-rejected',
                            'withdrawn' => 'em-status-withdrawn',
                        ];
                        $statusClass = $statusColors[$row['status'] ?? 'pending'] ?? 'em-status-pending';
                    ?>
                        <tr>
                            <td>
                                <div class="em-trainee-cell">
                                    <div class="em-avatar" style="background: linear-gradient(135deg, var(--ims-primary), var(--ims-accent));">
                                        <?= Security::e($initials) ?>
                                    </div>
                                    <div class="em-trainee-info">
                                        <strong class="em-trainee-name"><?= Security::e($row['trainee_name']) ?></strong>
                                        <span class="em-trainee-email"><?= Security::e($row['email']) ?></span>
                                    </div>
                                </div>
                            </td>
                            <td class="em-text-semibold"><?= Security::e($row['course_title']) ?></td>
                            <td><?= Security::e($row['instructor_name'] ?? 'Unassigned') ?></td>
                            <td>
                                <span class="em-status-pill <?= $statusClass ?>">
                                    <span class="em-status-dot"></span>
                                    <?= ucfirst(Security::e($row['status'] ?? 'pending')) ?>
                                </span>
                            </td>
                            <td class="em-cell-muted">
                                <?php
                                $d = new DateTime($row['created_at']);
                                echo Security::e($d->format('d M Y H:i'));
                                ?>
                            </td>
                            <td>
                                <div class="em-actions">
                                    <button class="em-action-trigger" type="button" aria-label="Actions Menu" data-em-dropdown>
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="5" r="1"/><circle cx="12" cy="12" r="1"/><circle cx="12" cy="19" r="1"/></svg>
                                    </button>
                                    <div class="em-dropdown-menu">
                                        <button class="em-dropdown-item" type="button" data-em-view="<?= (int) $row['id'] ?>">
                                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                            View Details
                                        </button>
                                        <div class="em-dropdown-divider"></div>
                                        
                                        <?php if (($row['status'] ?? '') === 'pending'): ?>
                                            <button class="em-dropdown-item em-dropdown-item-success" type="button"
                                                    data-em-status-change="<?= (int) $row['id'] ?>" data-new-status="active" data-trainee="<?= Security::e($row['trainee_name']) ?>">
                                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                                                Approve Request
                                            </button>
                                            <button class="em-dropdown-item em-dropdown-item-danger" type="button"
                                                    data-em-status-change="<?= (int) $row['id'] ?>" data-new-status="rejected" data-trainee="<?= Security::e($row['trainee_name']) ?>">
                                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/></svg>
                                                Reject Request
                                            </button>
                                        <?php elseif (($row['status'] ?? '') === 'active'): ?>
                                            <button class="em-dropdown-item em-dropdown-item-primary" type="button"
                                                    data-em-status-change="<?= (int) $row['id'] ?>" data-new-status="completed" data-trainee="<?= Security::e($row['trainee_name']) ?>">
                                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="7"/><polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"/></svg>
                                                Mark Completed
                                            </button>
                                            <button class="em-dropdown-item em-dropdown-item-danger" type="button"
                                                    data-em-status-change="<?= (int) $row['id'] ?>" data-new-status="withdrawn" data-trainee="<?= Security::e($row['trainee_name']) ?>">
                                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                                                Withdraw Student
                                            </button>
                                        <?php elseif (($row['status'] ?? '') === 'rejected'): ?>
                                            <button class="em-dropdown-item em-dropdown-item-success" type="button"
                                                    data-em-status-change="<?= (int) $row['id'] ?>" data-new-status="active" data-trainee="<?= Security::e($row['trainee_name']) ?>">
                                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                                                Re-approve Request
                                            </button>
                                        <?php else: ?>
                                            <span class="em-cell-muted" style="padding-left:.8rem;">No actions</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

</div><!-- /emContainer -->


<!-- ════════════════════════════════════════════════════
     VIEW ENROLMENT DETAIL — Slide-in Panel
     ════════════════════════════════════════════════════ -->
<div class="em-panel-overlay" id="emPanelOverlay" data-no-motion></div>
<div class="em-detail-panel" id="emDetailPanel" data-no-motion>
    <div class="em-panel-header">
        <h3 class="em-panel-title">Registration Details</h3>
        <button class="em-panel-close" id="emPanelClose" type="button" aria-label="Close">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
    </div>
    <div class="em-panel-body" id="emPanelBody">
        <div class="em-panel-loading" id="emPanelLoading">
            <div class="em-spinner"></div>
            <span>Loading registration info…</span>
        </div>
        <div class="em-panel-content" id="emPanelContent" style="display:none;">
            <!-- Populated by JavaScript -->
        </div>
    </div>
</div>


<!-- ════════════════════════════════════════════════════
     STATUS TRANSITION — Confirmation Modal
     ════════════════════════════════════════════════════ -->
<div class="em-modal-overlay" id="emStatusOverlay" data-no-motion>
    <div class="em-modal em-modal-sm">
        <div class="em-modal-header" id="emModalHeaderColor">
            <div class="em-status-icon-wrapper" id="emModalIconWrapper">
                <!-- Set by JavaScript -->
            </div>
            <h3 class="em-modal-title" id="emModalTitle">Change Status</h3>
        </div>
        <div class="em-modal-body em-text-center">
            <p id="emModalBodyText">Are you sure you want to perform this action?</p>
            <p class="em-text-muted">This update will reflect immediately in the trainee's portal.</p>
        </div>
        <form method="post" action="index.php?page=set-enrolment-status" id="emStatusForm">
            <input type="hidden" name="_csrf" value="<?= Security::e(Security::csrfToken()) ?>">
            <input type="hidden" name="id" id="statusEnrolmentId" value="0">
            <input type="hidden" name="status" id="statusNewValue" value="">
            <div class="em-modal-footer em-modal-footer-center">
                <button class="em-btn em-btn-ghost" type="button" id="emStatusCancel">Cancel</button>
                <button class="em-btn" type="submit" id="emStatusSubmitBtn">
                    Confirm Action
                </button>
            </div>
        </form>
    </div>
</div>
