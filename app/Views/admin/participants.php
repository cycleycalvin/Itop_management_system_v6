<?php
declare(strict_types=1);
use App\Core\Security;
use App\Core\View;

View::partial('partials/role-nav');

// Dynamic query builder helper for persistent filters
$queryParams = [
    'page' => 'admin-participants',
    'q' => $q,
    'location_id' => $filters['location_id'] ?? '',
    'company_id' => $filters['company_id'] ?? '',
    'institution_id' => $filters['institution_id'] ?? '',
    'profession_id' => $filters['profession_id'] ?? '',
    'status' => $filters['status'] ?? '',
];

$buildUrl = static function(array $extra) use ($queryParams): string {
    return 'index.php?' . http_build_query(array_merge($queryParams, $extra));
};
?>

<div class="pm-container" id="pmContainer">

    <!-- ── Breadcrumb ── -->
    <nav class="pm-breadcrumb" aria-label="breadcrumb">
        <a href="index.php?page=admin-dashboard">Dashboard</a>
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
        <span>Participant Management</span>
    </nav>

    <!-- Header Row -->
    <div class="pm-header">
        <div>
            <h1 class="pm-title">Participant Management</h1>
            <p class="pm-subtitle">Link trainees to master organizations, locations, professions, and monitor progress.</p>
        </div>
        <button class="pm-btn pm-btn-secondary" onclick="exportParticipants()">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-1"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            Export CSV
        </button>
    </div>

    <!-- Summary Stats -->
    <div class="pm-stats">
        <div class="pm-stat-card">
            <div class="pm-stat-icon pm-stat-icon-all">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </div>
            <div class="pm-stat-info">
                <span class="pm-stat-value"><?= (int) $totalTrainees ?></span>
                <span class="pm-stat-label">Total Trainees</span>
            </div>
        </div>
        <div class="pm-stat-card">
            <div class="pm-stat-icon pm-stat-icon-active">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            </div>
            <div class="pm-stat-info">
                <span class="pm-stat-value"><?= (int) $activeTrainees ?></span>
                <span class="pm-stat-label">Active Status</span>
            </div>
        </div>
        <div class="pm-stat-card">
            <div class="pm-stat-icon pm-stat-icon-enrolled">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20M4 19.5A2.5 2.5 0 0 0 6.5 22H20M4 19.5V3A2.5 2.5 0 0 1 6.5 .5H20v16.5H6.5a2.5 2.5 0 0 0-2.5 2.5z"/></svg>
            </div>
            <div class="pm-stat-info">
                <span class="pm-stat-value"><?= (int) $enrolledTrainees ?></span>
                <span class="pm-stat-label">Active Learners</span>
            </div>
        </div>
        <div class="pm-stat-card">
            <div class="pm-stat-icon pm-stat-icon-completed">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            </div>
            <div class="pm-stat-info">
                <span class="pm-stat-value"><?= (int) $completedTrainees ?></span>
                <span class="pm-stat-label">Graduated</span>
            </div>
        </div>
    </div>

    <!-- Toolbar: Academy Tabs & Filters -->
    <div class="pm-toolbar">
        <div class="pm-toolbar-row">
            <div class="pm-tabs" role="tablist">
                <a class="pm-tab <?= $academyCode === '' ? 'pm-tab-active' : '' ?>" href="<?= $buildUrl(['academy' => '']) ?>">
                    All Academies
                    <span class="pm-tab-badge"><?= (int) $totalTrainees ?></span>
                </a>
                <a class="pm-tab <?= $academyCode === 'ADGEA' ? 'pm-tab-active' : '' ?>" href="<?= $buildUrl(['academy' => 'ADGEA']) ?>">
                    ADGEA
                    <span class="pm-tab-badge"><?= (int) $totalAdgea ?></span>
                </a>
                <a class="pm-tab <?= $academyCode === 'IESGA' ? 'pm-tab-active' : '' ?>" href="<?= $buildUrl(['academy' => 'IESGA']) ?>">
                    IESGA
                    <span class="pm-tab-badge"><?= (int) $totalIesga ?></span>
                </a>
            </div>

            <!-- View Toggle Button -->
            <div class="pm-view-toggle">
                <button class="pm-view-btn pm-view-btn-active" id="pmViewBtnTable" title="Table View">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
                </button>
                <button class="pm-view-btn" id="pmViewBtnGrid" title="Card Grid View">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                </button>
            </div>
        </div>

        <!-- Filters Form -->
        <form class="pm-filters" method="get" action="index.php">
            <input type="hidden" name="page" value="admin-participants">
            <?php if ($academyCode): ?>
                <input type="hidden" name="academy" value="<?= Security::e($academyCode) ?>">
            <?php endif; ?>

            <div class="pm-search-box">
                <svg class="pm-search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input class="pm-search-input" name="q" value="<?= Security::e($q) ?>" placeholder="Search name, email, IC number, or phone…">
            </div>

            <select class="pm-select" name="location_id">
                <option value="">All Locations</option>
                <?php foreach ($locations as $loc): ?>
                    <option value="<?= (int) $loc['id'] ?>" <?= (int) ($filters['location_id'] ?? 0) === (int) $loc['id'] ? 'selected' : '' ?>><?= Security::e($loc['name']) ?></option>
                <?php endforeach; ?>
            </select>

            <select class="pm-select" name="company_id">
                <option value="">All Organizations</option>
                <?php foreach ($companies as $comp): ?>
                    <option value="<?= (int) $comp['id'] ?>" <?= (int) ($filters['company_id'] ?? 0) === (int) $comp['id'] ? 'selected' : '' ?>><?= Security::e($comp['name']) ?></option>
                <?php endforeach; ?>
            </select>

            <select class="pm-select" name="institution_id">
                <option value="">All Institutions</option>
                <?php foreach ($institutions as $inst): ?>
                    <option value="<?= (int) $inst['id'] ?>" <?= (int) ($filters['institution_id'] ?? 0) === (int) $inst['id'] ? 'selected' : '' ?>><?= Security::e($inst['name']) ?></option>
                <?php endforeach; ?>
            </select>

            <select class="pm-select" name="profession_id">
                <option value="">All Professions</option>
                <?php foreach ($professions as $prof): ?>
                    <option value="<?= (int) $prof['id'] ?>" <?= (int) ($filters['profession_id'] ?? 0) === (int) $prof['id'] ? 'selected' : '' ?>><?= Security::e($prof['name']) ?></option>
                <?php endforeach; ?>
            </select>

            <select class="pm-select" name="status">
                <option value="">All Statuses</option>
                <?php foreach (['pending', 'active', 'inactive', 'suspended'] as $st): ?>
                    <option value="<?= $st ?>" <?= ($filters['status'] ?? '') === $st ? 'selected' : '' ?>><?= Security::e(ucfirst($st)) ?></option>
                <?php endforeach; ?>
            </select>

            <button class="pm-btn pm-btn-secondary" type="submit">Filter</button>
            <?php if ($q || array_filter($filters)): ?>
                <a href="index.php?page=admin-participants" class="pm-btn pm-btn-ghost">Clear</a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Empty State -->
    <?php if (empty($users)): ?>
    <div class="cm-empty-state">
        <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        <h3>No trainees found</h3>
        <p>Try adjusting your search criteria or filter options.</p>
    </div>
    <?php else: ?>

    <!-- VIEW 1: TABLE VIEW (Default) -->
    <div class="pm-view-section pm-view-section-active" id="pmViewTable">
        <div class="pm-table-container">
            <table class="pm-table" id="participantsTable">
                <thead>
                    <tr>
                        <th>Trainee Details</th>
                        <th>IC Number</th>
                        <th>Organization</th>
                        <th>Location</th>
                        <th>Profession</th>
                        <th>Profile Status</th>
                        <th>Profile Complete</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                    <tr onclick="openTraineeDetail(<?= (int) $user['id'] ?>)">
                        <td>
                            <div class="pm-table-avatar-cell">
                                <div class="pm-avatar-small"><?= strtoupper(substr($user['name'] ?? 'T', 0, 1)) ?></div>
                                <div>
                                    <strong style="display:block;"><?= Security::e($user['name']) ?></strong>
                                    <span class="text-muted small"><?= Security::e($user['email']) ?></span>
                                </div>
                            </div>
                        </td>
                        <td class="text-muted small"><?= Security::e($user['identity_number'] ?? '—') ?></td>
                        <td>
                            <?php if (!empty($user['company_name'])): ?>
                                <strong class="small"><?= Security::e($user['company_name']) ?></strong>
                            <?php elseif (!empty($user['institution_company'])): ?>
                                <span class="pm-fallback-text" title="Unlinked free-text data"><?= Security::e($user['institution_company']) ?> ⚠️</span>
                            <?php else: ?>
                                <span class="text-muted small">—</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-muted small"><?= Security::e($user['location_name'] ?? '—') ?></td>
                        <td class="text-muted small"><?= Security::e($user['profession_name'] ?? '—') ?></td>
                        <td>
                            <span class="badge <?= $user['status'] === 'active' ? 'bg-success' : ($user['status'] === 'pending' ? 'bg-warning text-dark' : 'bg-secondary') ?>">
                                <?= Security::e(ucfirst($user['status'])) ?>
                            </span>
                        </td>
                        <td>
                            <?php $comp = (int) $user['profile_completion']; ?>
                            <div class="d-flex align-items-center gap-2">
                                <div class="pm-completion-bar" style="width: 60px;">
                                    <div class="pm-completion-fill <?= $comp >= 80 ? 'pm-fill-high' : ($comp >= 40 ? 'pm-fill-mid' : 'pm-fill-low') ?>" style="width: <?= $comp ?>%"></div>
                                </div>
                                <span class="small font-weight-bold"><?= $comp ?>%</span>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- VIEW 2: CARD VIEW -->
    <div class="pm-view-section" id="pmViewGrid">
        <div class="pm-grid">
            <?php foreach ($users as $user): ?>
            <div class="pm-card" onclick="openTraineeDetail(<?= (int) $user['id'] ?>)">
                <div class="pm-card-avatar-wrapper">
                    <div class="pm-avatar-large"><?= strtoupper(substr($user['name'] ?? 'T', 0, 1)) ?></div>
                    <div>
                        <h3 class="pm-card-title"><?= Security::e($user['name']) ?></h3>
                        <p class="pm-card-email"><?= Security::e($user['email']) ?></p>
                    </div>
                </div>
                <div class="pm-card-body">
                    <div class="pm-card-row">
                        <span class="pm-card-key">IC Number:</span>
                        <span class="pm-card-val"><?= Security::e($user['identity_number'] ?? '—') ?></span>
                    </div>
                    <div class="pm-card-row">
                        <span class="pm-card-key">Company:</span>
                        <span class="pm-card-val">
                            <?php if (!empty($user['company_name'])): ?>
                                <?= Security::e($user['company_name']) ?>
                            <?php elseif (!empty($user['institution_company'])): ?>
                                <span class="pm-fallback-text" title="Unlinked free-text"><?= Security::e($user['institution_company']) ?> ⚠️</span>
                            <?php else: ?>
                                —
                            <?php endif; ?>
                        </span>
                    </div>
                    <div class="pm-card-row">
                        <span class="pm-card-key">Location:</span>
                        <span class="pm-card-val"><?= Security::e($user['location_name'] ?? '—') ?></span>
                    </div>
                    <div class="pm-card-row">
                        <span class="pm-card-key">Profession:</span>
                        <span class="pm-card-val"><?= Security::e($user['profession_name'] ?? '—') ?></span>
                    </div>
                    <div class="pm-card-row">
                        <span class="pm-card-key">Status:</span>
                        <span>
                            <span class="badge <?= $user['status'] === 'active' ? 'bg-success' : ($user['status'] === 'pending' ? 'bg-warning text-dark' : 'bg-secondary') ?>">
                                <?= Security::e(ucfirst($user['status'])) ?>
                            </span>
                        </span>
                    </div>
                </div>

                <!-- Completeness -->
                <?php $comp = (int) $user['profile_completion']; ?>
                <div class="pm-completion-wrapper">
                    <div class="pm-completion-label-row">
                        <span>Profile Completion</span>
                        <span><?= $comp ?>%</span>
                    </div>
                    <div class="pm-completion-bar">
                        <div class="pm-completion-fill <?= $comp >= 80 ? 'pm-fill-high' : ($comp >= 40 ? 'pm-fill-mid' : 'pm-fill-low') ?>" style="width: <?= $comp ?>%"></div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
    <nav class="mt-4 d-flex justify-content-center">
        <ul class="pagination pagination-sm">
            <?php if ($pageNo > 1): ?>
            <li class="page-item"><a class="page-link" href="<?= $buildUrl(['p' => $pageNo - 1]) ?>">← Prev</a></li>
            <?php endif; ?>
            <?php for ($i = max(1, $pageNo - 2); $i <= min($totalPages, $pageNo + 2); $i++): ?>
            <li class="page-item <?= $i === $pageNo ? 'active' : '' ?>"><a class="page-link" href="<?= $buildUrl(['p' => $i]) ?>"><?= $i ?></a></li>
            <?php endfor; ?>
            <?php if ($pageNo < $totalPages): ?>
            <li class="page-item"><a class="page-link" href="<?= $buildUrl(['p' => $pageNo + 1]) ?>">Next →</a></li>
            <?php endif; ?>
        </ul>
    </nav>
    <?php endif; ?>

    <?php endif; ?>
</div>

<!-- ════════════════════════════════════════════════════
     TRAINEE DETAIL VIEW — Slide-in Drawer Modal
     ════════════════════════════════════════════════════ -->
<div class="pm-panel-overlay" id="pmPanelOverlay"></div>
<div class="pm-detail-panel" id="pmDetailPanel">
    <div class="pm-panel-header">
        <h3 class="pm-panel-title">Trainee Detailed Profile</h3>
        <button class="pm-panel-close" id="pmPanelClose" type="button" aria-label="Close">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
    </div>
    <div class="pm-panel-body" id="pmPanelBody">
        <!-- Loading Spinner -->
        <div class="pm-panel-loading" id="pmPanelLoading">
            <div class="pm-spinner" id="pmPanelSpinner"></div>
            <span id="pmPanelLoadingText">Fetching trainee records…</span>
        </div>
        
        <!-- Enriched Content Panel -->
        <div class="pm-panel-content" id="pmPanelContent" style="display:none;">
            <!-- Profile Card -->
            <div class="pm-drawer-profile-card">
                <div class="pm-drawer-avatar" id="drAvatar">T</div>
                <div>
                    <h4 class="pm-drawer-name" id="drName">Trainee Name</h4>
                    <p class="pm-drawer-email" id="drEmail">trainee@email.com</p>
                </div>
            </div>

            <!-- Profile completion progress bar -->
            <div class="pm-completion-wrapper">
                <div class="pm-completion-label-row">
                    <span>Profile Completeness</span>
                    <span id="drCompletionText">70%</span>
                </div>
                <div class="pm-completion-bar">
                    <div class="pm-completion-fill pm-fill-high" id="drCompletionFill" style="width: 70%"></div>
                </div>
            </div>

            <!-- Personal Info Details -->
            <div class="cm-detail-section">
                <h4 class="pm-drawer-section-title">Personal Records</h4>
                <div class="cm-detail-row">
                    <span class="cm-detail-key">IC Number:</span>
                    <span class="cm-detail-value" id="drIC">—</span>
                </div>
                <div class="cm-detail-row">
                    <span class="cm-detail-key">Phone:</span>
                    <span class="cm-detail-value" id="drPhone">—</span>
                </div>
                <div class="cm-detail-row">
                    <span class="cm-detail-key">Gender:</span>
                    <span class="cm-detail-value" id="drGender">—</span>
                </div>
                <div class="cm-detail-row">
                    <span class="cm-detail-key">DOB:</span>
                    <span class="cm-detail-value" id="drDOB">—</span>
                </div>
                <div class="cm-detail-row">
                    <span class="cm-detail-key">Address:</span>
                    <span class="cm-detail-value" id="drAddress">—</span>
                </div>
            </div>

            <!-- Quick Edit Master Links Form -->
            <div class="cm-detail-section">
                <h4 class="pm-drawer-section-title">Master Data Linkage</h4>
                <div class="alert alert-info py-2 px-3 small mb-3">
                    Assign valid master entries below. Fallback free-text was: <strong id="drFallbackText">—</strong>
                </div>
                <form method="post" action="index.php?page=admin-participant-update" id="pmUpdateForm">
                    <input type="hidden" name="_csrf" value="<?= Security::e(Security::csrfToken()) ?>">
                    <input type="hidden" name="id" id="traineeIdField" value="0">

                    <div class="mb-2">
                        <label class="small text-muted font-weight-bold mb-1">Company / Organization:</label>
                        <select name="company_id" id="editCompanyId" class="pm-select w-100">
                            <option value="">Unassigned</option>
                            <?php foreach ($companies as $comp): ?>
                                <option value="<?= (int) $comp['id'] ?>"><?= Security::e($comp['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-2">
                        <label class="small text-muted font-weight-bold mb-1">Institution:</label>
                        <select name="institution_id" id="editInstitutionId" class="pm-select w-100">
                            <option value="">Unassigned</option>
                            <?php foreach ($institutions as $inst): ?>
                                <option value="<?= (int) $inst['id'] ?>"><?= Security::e($inst['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-2">
                        <label class="small text-muted font-weight-bold mb-1">Base Location:</label>
                        <select name="location_id" id="editLocationId" class="pm-select w-100">
                            <option value="">Unassigned</option>
                            <?php foreach ($locations as $loc): ?>
                                <option value="<?= (int) $loc['id'] ?>"><?= Security::e($loc['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="small text-muted font-weight-bold mb-1">Profession:</label>
                        <select name="profession_id" id="editProfessionId" class="pm-select w-100">
                            <option value="">Unassigned</option>
                            <?php foreach ($professions as $prof): ?>
                                <option value="<?= (int) $prof['id'] ?>"><?= Security::e($prof['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <button type="submit" class="pm-btn pm-btn-primary w-100">Save Link Changes</button>
                </form>
            </div>

            <!-- Course Journey -->
            <div class="cm-detail-section">
                <h4 class="pm-drawer-section-title">Course Registrations</h4>
                <div class="pm-drawer-list" id="drCoursesList">
                    <!-- Dynamic -->
                </div>
            </div>

            <!-- Certificates -->
            <div class="cm-detail-section">
                <h4 class="pm-drawer-section-title">Issued Certificates</h4>
                <div class="pm-drawer-list" id="drCertificatesList">
                    <!-- Dynamic -->
                </div>
            </div>

            <!-- Uploaded Verification Documents -->
            <div class="cm-detail-section mt-4">
                <h4 class="pm-drawer-section-title d-flex justify-content-between align-items-center">
                    <span>Uploaded Verification Documents</span>
                </h4>
                <div class="pm-drawer-list" id="drDocumentsList">
                    <!-- Dynamic -->
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Global Error Reporter for diagnostic purposes
window.addEventListener('error', function(e) {
    console.error("Global JS Error Captured: ", e);
    const errDiv = document.createElement('div');
    errDiv.className = 'alert alert-danger m-3';
    errDiv.style.position = 'fixed';
    errDiv.style.top = '10px';
    errDiv.style.left = '10px';
    errDiv.style.right = '10px';
    errDiv.style.zIndex = '99999';
    errDiv.style.boxShadow = '0 4px 12px rgba(0,0,0,0.15)';
    errDiv.innerHTML = `<strong>JavaScript Error:</strong> ${e.message}<br><small style="font-family: monospace;">at ${e.filename || 'unknown'}:${e.lineno || 0}:${e.colno || 0}</small>`;
    document.body.appendChild(errDiv);
});

// Safe localStorage Wrapper to prevent SecurityError blocks
const safeLocalStorage = {
    getItem(key) {
        try {
            return localStorage.getItem(key);
        } catch (e) {
            console.warn('localStorage getItem blocked:', e);
            return null;
        }
    },
    setItem(key, value) {
        try {
            localStorage.setItem(key, value);
        } catch (e) {
            console.warn('localStorage setItem blocked:', e);
        }
    }
};

// Toggle View Logic (Persisted in localStorage)
const viewTable = document.getElementById('pmViewTable');
const viewGrid = document.getElementById('pmViewGrid');
const btnTable = document.getElementById('pmViewBtnTable');
const btnGrid = document.getElementById('pmViewBtnGrid');

function applyViewPreference() {
    try {
        const pref = safeLocalStorage.getItem('pm_view_pref') || 'table';
        if (pref === 'grid') {
            if (viewTable) viewTable.classList.remove('pm-view-section-active');
            if (viewGrid) viewGrid.classList.add('pm-view-section-active');
            if (btnTable) btnTable.classList.remove('pm-view-btn-active');
            if (btnGrid) btnGrid.classList.add('pm-view-btn-active');
        } else {
            if (viewGrid) viewGrid.classList.remove('pm-view-section-active');
            if (viewTable) viewTable.classList.add('pm-view-section-active');
            if (btnGrid) btnGrid.classList.remove('pm-view-btn-active');
            if (btnTable) btnTable.classList.add('pm-view-btn-active');
        }
    } catch (e) {
        console.error('Error applying view preference:', e);
    }
}

if (btnTable) {
    btnTable.addEventListener('click', () => {
        safeLocalStorage.setItem('pm_view_pref', 'table');
        applyViewPreference();
    });
}
if (btnGrid) {
    btnGrid.addEventListener('click', () => {
        safeLocalStorage.setItem('pm_view_pref', 'grid');
        applyViewPreference();
    });
}

// Initial view application
applyViewPreference();

// Drawer Modal Controls
const panelOverlay = document.getElementById('pmPanelOverlay');
const detailPanel = document.getElementById('pmDetailPanel');
const panelLoading = document.getElementById('pmPanelLoading');
const panelContent = document.getElementById('pmPanelContent');
const panelClose = document.getElementById('pmPanelClose');

// Relocate panel overlay and drawer panel to document.body to escape the parent container's stacking context.
// This ensures they render globally above all other elements (topbar, sidenav, footer).
try {
    if (panelOverlay) document.body.appendChild(panelOverlay);
    if (detailPanel) document.body.appendChild(detailPanel);
} catch (e) {
    console.error('Error relocating drawer modal in DOM:', e);
}

let activeAbortController = null;
let fetchTimerInterval = null;

function closeTraineeDetail() {
    if (detailPanel) detailPanel.classList.remove('pm-panel-open');
    if (panelOverlay) panelOverlay.classList.remove('pm-panel-overlay-active');
    
    // Cancel any active abort controller
    if (activeAbortController) {
        activeAbortController.abort();
        activeAbortController = null;
    }
    // Clear visual loader timer
    if (fetchTimerInterval) {
        clearInterval(fetchTimerInterval);
        fetchTimerInterval = null;
    }
}

if (panelClose) panelClose.addEventListener('click', closeTraineeDetail);
if (panelOverlay) panelOverlay.addEventListener('click', closeTraineeDetail);

// Add global listener for Escape key to close the panel
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        closeTraineeDetail();
    }
});

function openTraineeDetail(userId) {
    console.log("openTraineeDetail initialized for userId:", userId);
    
    // Clear any existing loading timers and cancel any active request
    if (fetchTimerInterval) {
        clearInterval(fetchTimerInterval);
        fetchTimerInterval = null;
    }
    if (activeAbortController) {
        activeAbortController.abort();
        activeAbortController = null;
    }

    const spinner = document.getElementById('pmPanelSpinner');
    const loadingText = document.getElementById('pmPanelLoadingText');
    
    try {
        if (!detailPanel || !panelOverlay) {
            console.error("Required drawer panel elements are missing from the DOM.");
            return;
        }
        
        // Show drawer and spinner
        panelOverlay.classList.add('pm-panel-overlay-active');
        detailPanel.classList.add('pm-panel-open');
        panelLoading.style.display = 'flex';
        panelContent.style.display = 'none';

        // Reset spinner & loading text
        if (spinner) spinner.style.display = 'block';
        if (loadingText) loadingText.textContent = 'Fetching trainee records…';

        // Start visual load timer
        let secondsElapsed = 0;
        fetchTimerInterval = setInterval(() => {
            secondsElapsed++;
            if (loadingText) {
                loadingText.textContent = `Fetching trainee records… (${secondsElapsed}s)`;
            }
        }, 1000);

        // Instantiate AbortController for fetch timeout
        activeAbortController = new AbortController();
        const timeoutId = setTimeout(() => {
            if (activeAbortController) {
                activeAbortController.abort();
            }
        }, 10000); // 10 seconds timeout

        // Fetch trainee data (using robust query-based URL relative to current location)
        const fetchUrl = '?page=admin-participant-detail&id=' + encodeURIComponent(userId);
        console.log("Initiating fetch request to:", fetchUrl);

        fetch(fetchUrl, { signal: activeAbortController.signal })
            .then(res => {
                clearTimeout(timeoutId);
                console.log("Fetch response received. HTTP status:", res.status, res.statusText);
                if (!res.ok) {
                    throw new Error(`HTTP error ${res.status}: ${res.statusText}`);
                }
                return res.json();
            })
            .then(data => {
                // Clear active controller reference
                activeAbortController = null;
                console.log("Parsed JSON data response:", data);
                
                // Clear the loading timer on success
                if (fetchTimerInterval) {
                    clearInterval(fetchTimerInterval);
                    fetchTimerInterval = null;
                }

                if (data.status !== 'success') {
                    throw new Error(data.message || 'Failed to retrieve trainee info.');
                }

                const trainee = data.trainee;
                if (!trainee) {
                    throw new Error('Trainee records are empty/missing in response.');
                }
                
                // Helper function to safely set element text
                const setValText = (id, val) => {
                    const el = document.getElementById(id);
                    if (el) el.textContent = val;
                };

                // Helper function to safely set input value
                const setInputVal = (id, val) => {
                    const el = document.getElementById(id);
                    if (el) el.value = val;
                };

                // Populate basic details
                setInputVal('traineeIdField', trainee.id);
                
                const avatar = document.getElementById('drAvatar');
                if (avatar) {
                    avatar.textContent = trainee.name ? trainee.name.substring(0, 1).toUpperCase() : 'T';
                }

                setValText('drName', trainee.name || 'Trainee Name');
                setValText('drEmail', trainee.email || 'trainee@email.com');
                setValText('drIC', trainee.identity_number || '—');
                setValText('drPhone', trainee.phone || '—');
                setValText('drGender', trainee.gender || '—');
                setValText('drDOB', trainee.date_of_birth || '—');
                setValText('drAddress', trainee.address || '—');
                setValText('drFallbackText', trainee.institution_company || '—');

                // Completion bar
                const comp = trainee.profile_completion || 0;
                const compText = document.getElementById('drCompletionText');
                const compFill = document.getElementById('drCompletionFill');
                if (compText) compText.textContent = comp + '%';
                if (compFill) {
                    compFill.style.width = comp + '%';
                    compFill.className = 'pm-completion-fill';
                    if (comp >= 80) compFill.classList.add('pm-fill-high');
                    else if (comp >= 40) compFill.classList.add('pm-fill-mid');
                    else compFill.classList.add('pm-fill-low');
                }

                // Set Dropdowns values
                setInputVal('editCompanyId', trainee.company_id || '');
                setInputVal('editInstitutionId', trainee.institution_id || '');
                setInputVal('editLocationId', trainee.location_id || '');
                setInputVal('editProfessionId', trainee.profession_id || '');

                // Render Courses Journey
                const coursesList = document.getElementById('drCoursesList');
                if (coursesList) {
                    coursesList.innerHTML = '';
                    if (data.courses && data.courses.length > 0) {
                        data.courses.forEach(c => {
                            const row = document.createElement('div');
                            row.className = 'pm-drawer-item';
                            
                            const left = document.createElement('div');
                            const title = document.createElement('h5');
                            title.className = 'pm-drawer-item-title';
                            title.textContent = c.course_title || 'Untitled Course';
                            const sub = document.createElement('p');
                            sub.className = 'pm-drawer-item-sub';
                            sub.textContent = (c.academy_code ? `Academy: ${c.academy_code} | ` : '') + `Progress: ${c.progress_percent || 0}%`;
                            
                            left.appendChild(title);
                            left.appendChild(sub);
                            row.appendChild(left);

                            const right = document.createElement('span');
                            const statusVal = c.enrolment_status || 'pending';
                            const badgeClass = statusVal === 'completed' ? 'bg-success' : (statusVal === 'active' ? 'bg-info text-white' : 'bg-secondary');
                            right.className = `badge ${badgeClass}`;
                            right.textContent = statusVal.toUpperCase();
                            row.appendChild(right);

                            coursesList.appendChild(row);
                        });
                    } else {
                        coursesList.innerHTML = '<p class="text-muted small italic">No courses registered yet.</p>';
                    }
                }

                // Render Certificates List
                const certsList = document.getElementById('drCertificatesList');
                if (certsList) {
                    certsList.innerHTML = '';
                    if (data.certificates && data.certificates.length > 0) {
                        data.certificates.forEach(c => {
                            const row = document.createElement('div');
                            row.className = 'pm-drawer-item';
                            
                            const left = document.createElement('div');
                            const title = document.createElement('h5');
                            title.className = 'pm-drawer-item-title';
                            title.textContent = c.course_title || 'Untitled Course';
                            const sub = document.createElement('p');
                            sub.className = 'pm-drawer-item-sub';
                            sub.textContent = `No: ${c.certificate_no || '—'} | Issued: ${c.issued_at || '—'}`;
                            
                            left.appendChild(title);
                            left.appendChild(sub);
                            row.appendChild(left);

                            if (c.pdf_path) {
                                const link = document.createElement('a');
                                link.href = 'storage/certificates/' + c.pdf_path;
                                link.target = '_blank';
                                link.className = 'pm-btn pm-btn-secondary py-1 px-2 small';
                                link.innerHTML = '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>';
                                row.appendChild(link);
                            }

                            certsList.appendChild(row);
                        });
                    } else {
                        certsList.innerHTML = '<p class="text-muted small italic">No certificates issued yet.</p>';
                    }
                }

                // Render Uploaded Verification Documents List
                const docsList = document.getElementById('drDocumentsList');
                if (docsList) {
                    docsList.innerHTML = '';
                    if (data.documents && data.documents.length > 0) {
                        data.documents.forEach(d => {
                            const row = document.createElement('div');
                            row.className = 'pm-drawer-item d-flex align-items-center justify-content-between gap-2 p-2.5 mb-2 border rounded-3 bg-surface';
                            
                            const left = document.createElement('div');
                            left.style.minWidth = '0';

                            const title = document.createElement('h5');
                            title.className = 'pm-drawer-item-title text-truncate font-weight-bold mb-0.5';
                            title.style.fontSize = '0.85rem';
                            title.textContent = d.document_type || 'Supporting File';
                            
                            const sub = document.createElement('p');
                            sub.className = 'pm-drawer-item-sub text-truncate mb-0';
                            sub.style.fontSize = '0.75rem';
                            sub.textContent = `${d.file_name} (${d.status || 'Pending'})`;
                            
                            left.appendChild(title);
                            left.appendChild(sub);
                            row.appendChild(left);

                            const right = document.createElement('div');
                            right.className = 'd-flex align-items-center gap-1 flex-shrink-0';
                            
                            const badge = document.createElement('span');
                            badge.className = (d.status === 'Verified') ? 'badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-0.5' : 'badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-2 py-0.5';
                            badge.style.fontSize = '0.7rem';
                            badge.textContent = (d.status === 'Verified') ? 'Verified' : 'Pending Audit';
                            right.appendChild(badge);

                            if (d.file_path) {
                                const link = document.createElement('a');
                                link.href = 'storage/uploads/' + d.file_path;
                                link.download = true;
                                link.className = 'btn btn-sm btn-outline-primary py-0.5 px-2';
                                link.style.fontSize = '0.75rem';
                                link.textContent = 'Download';
                                right.appendChild(link);
                            }
                            row.appendChild(right);

                            docsList.appendChild(row);
                        });

                        const auditLink = document.createElement('a');
                        auditLink.href = 'index.php?page=admin-documentation&trainee_id=' + trainee.id;
                        auditLink.className = 'btn btn-sm btn-outline-secondary w-100 mt-2';
                        auditLink.style.fontSize = '0.8rem';
                        auditLink.textContent = 'Audit & Verify in Documentation Hub →';
                        docsList.appendChild(auditLink);
                    } else {
                        docsList.innerHTML = '<p class="text-muted small italic mb-1">No verification documents uploaded yet.</p><a href="index.php?page=admin-documentation&trainee_id=' + trainee.id + '" class="btn btn-sm btn-outline-secondary w-100 mt-1" style="font-size:0.775rem">Open Trainee Documentation Hub →</a>';
                    }
                }

                // Swap Loading to Content
                panelLoading.style.display = 'none';
                panelContent.style.display = 'flex';
                console.log("Drawer content populated and rendered successfully.");
            })
            .catch(err => {
                showDetailError(err);
            });
    } catch (e) {
        showDetailError(e);
    }

    function showDetailError(err) {
        console.error("openTraineeDetail Error Caught:", err);
        activeAbortController = null;
        if (fetchTimerInterval) {
            clearInterval(fetchTimerInterval);
            fetchTimerInterval = null;
        }
        if (spinner) spinner.style.display = 'none';
        if (loadingText) {
            loadingText.innerHTML = `
                <div class="text-danger mb-3" style="font-size: 0.92rem; line-height: 1.4;">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="mb-2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg><br>
                    <strong>Failed to load profile details:</strong><br>
                    <span style="font-family: monospace; font-size: 0.8rem; background: rgba(220,53,69,0.06); padding: 4px 8px; border-radius: 4px; display: inline-block; margin-top: 5px; word-break: break-all;">${err.message || err}</span>
                </div>
                <button type="button" class="pm-btn pm-btn-secondary px-3 py-1 small" onclick="closeTraineeDetail()">Close Panel</button>
            `;
        }
    }
}

// Export CSV Function
function exportParticipants() {
    const table = document.getElementById('participantsTable');
    if (!table) return;
    let csv = [];
    
    // Header
    const headers = ["Name", "Email", "IC Number", "Organization", "Location", "Profession", "Status", "Profile Completion"];
    csv.push(headers.map(h => '"' + h.replace(/"/g, '""') + '"').join(','));

    // Rows
    const rows = table.querySelectorAll('tbody tr');
    rows.forEach(tr => {
        const cells = tr.querySelectorAll('td');
        if (cells.length < 7) return;

        // Name & Email extraction
        const nameCell = cells[0].querySelector('strong');
        const emailCell = cells[0].querySelector('.text-muted');
        const name = nameCell ? nameCell.textContent.trim() : '';
        const email = emailCell ? emailCell.textContent.trim() : '';

        const ic = cells[1].textContent.trim();
        
        // Organization fallback check
        const orgCell = cells[2];
        const fallbackText = orgCell.querySelector('.pm-fallback-text');
        const org = fallbackText ? fallbackText.textContent.replace('⚠️', '').trim() : orgCell.textContent.trim();

        const loc = cells[3].textContent.trim();
        const prof = cells[4].textContent.trim();
        const status = cells[5].textContent.trim();
        
        const completionCell = cells[6].querySelector('span');
        const completion = completionCell ? completionCell.textContent.trim() : '0%';

        const rowData = [name, email, ic, org, loc, prof, status, completion];
        csv.push(rowData.map(r => '"' + r.replace(/"/g, '""') + '"').join(','));
    });

    const blob = new Blob([csv.join('\n')], { type: 'text/csv' });
    const a = document.createElement('a');
    a.href = URL.createObjectURL(blob);
    a.download = 'participants_enriched_export.csv';
    a.click();
}
</script>
