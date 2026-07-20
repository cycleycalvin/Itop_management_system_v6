<?php use App\Core\Auth; use App\Core\Security; use App\Core\View; ?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<?php
if (!function_exists('hasChartData')) {
    function hasChartData(array $data): bool {
        if (empty($data)) return false;
        $sum = 0;
        foreach ($data as $item) {
            $sum += (float) ($item['value'] ?? 0);
        }
        return $sum > 0;
    }
}
?>

<?php if (!empty($pendingUsersCount) && $pendingUsersCount > 0): ?>
<div class="alert alert-warning alert-dismissible fade show d-flex align-items-center gap-3 mb-3 shadow-sm border-0" role="alert" style="background: linear-gradient(135deg, #fff3cd 0%, #ffeeba 100%); border-left: 4px solid #ffc107 !important;">
    <div class="d-flex align-items-center gap-2">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#856404" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        <div>
            <strong class="text-dark"><?= $pendingUsersCount ?> pending registration<?= $pendingUsersCount > 1 ? 's' : '' ?></strong>
            <span class="text-muted ms-1">awaiting your approval.</span>
        </div>
    </div>
    <a href="index.php?page=admin-users" class="btn btn-sm btn-warning fw-semibold ms-auto text-nowrap">Review Now →</a>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>

<div class="d-flex justify-content-between align-items-md-center flex-column flex-md-row mb-4 gap-2" style="position: relative; z-index: 10;">
    <div>
        <span class="section-label">Administrator Dashboard</span>
        <h1 class="section-title mb-0">Welcome back, <?= Security::e(Auth::user()['name']) ?></h1>
    </div>
    <div class="d-flex gap-2">
        <a href="index.php?page=admin-analytics" class="btn btn-outline-primary btn-sm">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-1"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
            Full Analytics
        </a>
        <div class="dropdown">
            <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" id="customizeDashboardDropdown" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-1"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                Customize
            </button>
            <ul class="dropdown-menu dropdown-menu-end p-3 shadow-lg" aria-labelledby="customizeDashboardDropdown" style="width: 300px; max-height: 400px; overflow-y: auto;">
                <li><h6 class="dropdown-header px-0 mb-1">Show/Hide Chart Panels</h6></li>
                <li><hr class="dropdown-divider"></li>
                <div id="chart-visibility-toggles"></div>
                <li><hr class="dropdown-divider"></li>
                <li><button class="btn btn-outline-primary btn-sm w-100 mb-2" onclick="openCreateCustomChartModal()">+ Create Custom Analysis</button></li>
                <li><button class="btn btn-primary btn-sm w-100" onclick="resetDashboardLayout()">Reset Dashboard</button></li>
            </ul>
        </div>
    </div>
</div>

<!-- ── Stat Cards ────────────────────────────────── -->
<div class="overview-stats mb-4">
    <?php
    $statLinks = [
        'total_participants' => 'index.php?page=admin-participants',
        'total_courses'      => 'index.php?page=admin-courses',
        'total_companies'    => 'index.php?page=admin-master-data&table=companies',
        'total_institutions' => 'index.php?page=admin-master-data&table=institutions',
        'certificates_issued'=> 'index.php?page=admin-certificates',
        'total_revenue'      => 'index.php?page=reports',
    ];
    $statIcons = [
        'total_participants' => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>',
        'total_courses'      => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>',
        'total_companies'    => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>',
        'total_institutions' => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>',
        'certificates_issued'=> '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="7"/><polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"/></svg>',
        'total_revenue'      => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>',
    ];
    $statAccents = [
        'total_participants'  => '',
        'total_courses'       => 'accent-green',
        'total_companies'     => '',
        'total_institutions'  => '',
        'certificates_issued' => 'accent-green',
        'total_revenue'       => 'accent-orange',
    ];

    foreach ($stats as $label => $value):
        $accent = $statAccents[$label] ?? '';
        $link = $statLinks[$label] ?? '#';
        $icon = $statIcons[$label] ?? '';
    ?>
        <a href="<?= $link ?>" class="overview-stat-card <?= $accent ?> animate-in" title="View details">
            <div class="d-flex justify-content-between align-items-start">
                <span class="overview-stat-label"><?= Security::e(ucwords(str_replace('_', ' ', $label))) ?></span>
                <span class="overview-stat-icon" style="color: var(--ims-muted); opacity:.4;"><?= $icon ?></span>
            </div>
            <div class="d-flex align-items-center justify-content-between mt-1">
                <strong class="overview-stat-value"><?= $label === 'total_revenue' ? 'RM ' . number_format((float) $value) : number_format((int) $value) ?></strong>
                <?php if (isset($trends[$label])): 
                    $tVal = $trends[$label];
                    if ($tVal > 0): ?>
                        <span class="trend-badge text-success small fw-bold d-flex align-items-center gap-1">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="18 15 12 9 6 15"/></svg>
                            +<?= number_format($tVal, 1) ?>%
                        </span>
                    <?php elseif ($tVal < 0): ?>
                        <span class="trend-badge text-danger small fw-bold d-flex align-items-center gap-1">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
                            <?= number_format($tVal, 1) ?>%
                        </span>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </a>
    <?php endforeach; ?>
</div>

<!-- ── Chart Grid ────────────────────────────────── -->
<div class="row g-4 mb-4" id="charts-grid-container">
    
    <!-- Participants by Academy -->
    <?php if (hasChartData($analytics['programme'])): ?>
    <div class="col-lg-6 chart-container-wrapper" data-chart-id="programme">
        <div class="chart-panel animate-in h-100 d-flex flex-column">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="chart-panel-title mb-0">Participants by Academy</span>
                <div class="d-flex gap-2 align-items-center">
                    <select class="form-select form-select-sm chart-type-select" style="width: auto;" data-chart-id="programme">
                        <option value="bar">Bar</option>
                        <option value="line">Line</option>
                        <option value="pie">Pie</option>
                        <option value="doughnut">Doughnut</option>
                    </select>
                    <a href="index.php?page=admin-analytics-detail&chart=programme" class="btn btn-sm btn-outline-primary px-2" title="View Details">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                    </a>
                    <button class="btn btn-sm btn-outline-secondary px-2" title="Edit Data" onclick="openAnalyticsEditor('programme', 'Participants by Academy')">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    </button>
                    <button class="btn btn-sm btn-outline-secondary border-0 px-2" title="Hide Panel" onclick="hideChart('programme')">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </button>
                </div>
            </div>
            <div class="flex-grow-1" style="position: relative; min-height: 280px;">
                <canvas class="admin-chart-canvas" id="chart-programme" data-values='<?= json_encode(array_column($analytics['programme'], 'value')) ?>' data-labels='<?= json_encode(array_column($analytics['programme'], 'label')) ?>'></canvas>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Monthly Registration Trend -->
    <?php if (hasChartData($analytics['monthly'])): ?>
    <div class="col-lg-6 chart-container-wrapper" data-chart-id="monthly">
        <div class="chart-panel animate-in h-100 d-flex flex-column">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="chart-panel-title mb-0">Monthly Registration Trend</span>
                <div class="d-flex gap-2 align-items-center">
                    <select class="form-select form-select-sm chart-type-select" style="width: auto;" data-chart-id="monthly">
                        <option value="line">Line</option>
                        <option value="bar">Bar</option>
                        <option value="pie">Pie</option>
                        <option value="doughnut">Doughnut</option>
                    </select>
                    <a href="index.php?page=admin-analytics-detail&chart=monthly" class="btn btn-sm btn-outline-primary px-2" title="View Details">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                    </a>
                    <button class="btn btn-sm btn-outline-secondary border-0 px-2" title="Hide Panel" onclick="hideChart('monthly')">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </button>
                </div>
            </div>
            <div class="flex-grow-1" style="position: relative; min-height: 280px;">
                <canvas class="admin-chart-canvas" id="chart-monthly" data-values='<?= json_encode(array_reverse(array_column($analytics['monthly'], 'value'))) ?>' data-labels='<?= json_encode(array_reverse(array_column($analytics['monthly'], 'label'))) ?>'></canvas>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Participants by Year -->
    <?php if (hasChartData($analytics['years'])): ?>
    <div class="col-lg-4 chart-container-wrapper" data-chart-id="years">
        <div class="chart-panel animate-in h-100 d-flex flex-column">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="chart-panel-title mb-0">Participants by Year</span>
                <div class="d-flex gap-2 align-items-center">
                    <select class="form-select form-select-sm chart-type-select" style="width: auto;" data-chart-id="years">
                        <option value="bar">Bar</option><option value="line">Line</option><option value="pie">Pie</option><option value="doughnut">Doughnut</option>
                    </select>
                    <a href="index.php?page=admin-analytics-detail&chart=years" class="btn btn-sm btn-outline-primary px-2" title="View Details">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                    </a>
                    <button class="btn btn-sm btn-outline-secondary px-2" title="Edit Data" onclick="openAnalyticsEditor('years', 'Participants by Year')">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    </button>
                    <button class="btn btn-sm btn-outline-secondary border-0 px-2" title="Hide" onclick="hideChart('years')">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </button>
                </div>
            </div>
            <div class="flex-grow-1" style="position: relative; min-height: 240px;">
                <canvas class="admin-chart-canvas" id="chart-years" data-values='<?= json_encode(array_column($analytics['years'], 'value')) ?>' data-labels='<?= json_encode(array_column($analytics['years'], 'label')) ?>'></canvas>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Course Completion Rate -->
    <?php if (hasChartData($analytics['completion'])): ?>
    <div class="col-lg-4 chart-container-wrapper" data-chart-id="completion">
        <div class="chart-panel animate-in h-100 d-flex flex-column">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="chart-panel-title mb-0">Completion Rate</span>
                <div class="d-flex gap-2 align-items-center">
                    <select class="form-select form-select-sm chart-type-select" style="width: auto;" data-chart-id="completion">
                        <option value="doughnut">Doughnut</option><option value="pie">Pie</option><option value="bar">Bar</option><option value="line">Line</option>
                    </select>
                    <button class="btn btn-sm btn-outline-secondary px-2" title="Edit Data" onclick="openAnalyticsEditor('completion', 'Completion Rate')">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    </button>
                    <button class="btn btn-sm btn-outline-secondary border-0 px-2" title="Hide" onclick="hideChart('completion')">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </button>
                </div>
            </div>
            <div class="flex-grow-1" style="position: relative; min-height: 240px;">
                <canvas class="admin-chart-canvas" id="chart-completion" data-values='<?= json_encode(array_column($analytics['completion'], 'value')) ?>' data-labels='<?= json_encode(array_column($analytics['completion'], 'label')) ?>'></canvas>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Certificate Issuance -->
    <?php if (hasChartData($analytics['certificates'])): ?>
    <div class="col-lg-4 chart-container-wrapper" data-chart-id="certificates">
        <div class="chart-panel animate-in h-100 d-flex flex-column">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="chart-panel-title mb-0">Certificate Issuance</span>
                <div class="d-flex gap-2 align-items-center">
                    <select class="form-select form-select-sm chart-type-select" style="width: auto;" data-chart-id="certificates">
                        <option value="line">Line</option><option value="bar">Bar</option><option value="pie">Pie</option><option value="doughnut">Doughnut</option>
                    </select>
                    <a href="index.php?page=admin-analytics-detail&chart=certificates" class="btn btn-sm btn-outline-primary px-2" title="View Details">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                    </a>
                    <button class="btn btn-sm btn-outline-secondary border-0 px-2" title="Hide" onclick="hideChart('certificates')">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </button>
                </div>
            </div>
            <div class="flex-grow-1" style="position: relative; min-height: 240px;">
                <canvas class="admin-chart-canvas" id="chart-certificates" data-values='<?= json_encode(array_reverse(array_column($analytics['certificates'], 'value'))) ?>' data-labels='<?= json_encode(array_reverse(array_column($analytics['certificates'], 'label'))) ?>'></canvas>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Participants by Course -->
    <?php if (hasChartData($analytics['course_participants'])): ?>
    <div class="col-lg-6 chart-container-wrapper" data-chart-id="course_participants">
        <div class="chart-panel animate-in h-100 d-flex flex-column">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="chart-panel-title mb-0">Participants by Course</span>
                <div class="d-flex gap-2 align-items-center">
                    <select class="form-select form-select-sm chart-type-select" style="width: auto;" data-chart-id="course_participants">
                        <option value="bar">Bar</option><option value="line">Line</option><option value="pie">Pie</option><option value="doughnut">Doughnut</option>
                    </select>
                    <a href="index.php?page=admin-analytics-detail&chart=course_participants" class="btn btn-sm btn-outline-primary px-2" title="View Details">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                    </a>
                    <button class="btn btn-sm btn-outline-secondary px-2" title="Edit Data" onclick="openAnalyticsEditor('course_participants', 'Participants by Course')">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    </button>
                    <button class="btn btn-sm btn-outline-secondary border-0 px-2" title="Hide" onclick="hideChart('course_participants')">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </button>
                </div>
            </div>
            <div class="flex-grow-1" style="position: relative; min-height: 280px;">
                <canvas class="admin-chart-canvas" id="chart-course_participants" data-values='<?= json_encode(array_column($analytics['course_participants'], 'value')) ?>' data-labels='<?= json_encode(array_column($analytics['course_participants'], 'label')) ?>'></canvas>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Participants by Category -->
    <?php if (hasChartData($analytics['categories'])): ?>
    <div class="col-lg-6 chart-container-wrapper" data-chart-id="categories">
        <div class="chart-panel animate-in h-100 d-flex flex-column">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="chart-panel-title mb-0">Participants by Category</span>
                <div class="d-flex gap-2 align-items-center">
                    <select class="form-select form-select-sm chart-type-select" style="width: auto;" data-chart-id="categories">
                        <option value="bar">Bar</option><option value="line">Line</option><option value="pie">Pie</option><option value="doughnut">Doughnut</option>
                    </select>
                    <a href="index.php?page=admin-analytics-detail&chart=categories" class="btn btn-sm btn-outline-primary px-2" title="View Details">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                    </a>
                    <button class="btn btn-sm btn-outline-secondary px-2" title="Edit Data" onclick="openAnalyticsEditor('categories', 'Participants by Category')">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    </button>
                    <button class="btn btn-sm btn-outline-secondary border-0 px-2" title="Hide" onclick="hideChart('categories')">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </button>
                </div>
            </div>
            <div class="flex-grow-1" style="position: relative; min-height: 280px;">
                <canvas class="admin-chart-canvas" id="chart-categories" data-values='<?= json_encode(array_column($analytics['categories'], 'value')) ?>' data-labels='<?= json_encode(array_column($analytics['categories'], 'label')) ?>'></canvas>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Custom Analysis Charts -->
    <?php foreach ($analytics['custom_charts'] as $cc): 
        $hasData = hasChartData($cc['data']); 
    ?>
    <div class="col-lg-6 chart-container-wrapper" data-chart-id="custom_<?= $cc['id'] ?>">
        <div class="chart-panel animate-in h-100 d-flex flex-column">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="chart-panel-title mb-0"><?= Security::e($cc['title']) ?></span>
                <div class="d-flex gap-2 align-items-center">
                    <select class="form-select form-select-sm chart-type-select" style="width: auto;" data-chart-id="custom_<?= $cc['id'] ?>">
                        <option value="bar" <?= $cc['chart_type'] === 'bar' ? 'selected' : '' ?>>Bar</option>
                        <option value="line" <?= $cc['chart_type'] === 'line' ? 'selected' : '' ?>>Line</option>
                        <option value="pie" <?= $cc['chart_type'] === 'pie' ? 'selected' : '' ?>>Pie</option>
                        <option value="doughnut" <?= $cc['chart_type'] === 'doughnut' ? 'selected' : '' ?>>Doughnut</option>
                    </select>
                    <?php if ($cc['data_source'] === 'custom_manual'): ?>
                    <button class="btn btn-sm btn-outline-secondary px-2" title="Edit Data" onclick="openAnalyticsEditor('custom_manual_<?= $cc['id'] ?>', '<?= Security::e($cc['title']) ?>')">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    </button>
                    <?php endif; ?>
                    <button class="btn btn-sm btn-outline-danger border-0 px-2" title="Delete Chart" onclick="deleteCustomChart(<?= $cc['id'] ?>)">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                    </button>
                    <button class="btn btn-sm btn-outline-secondary border-0 px-2" title="Hide Panel" onclick="hideChart('custom_<?= $cc['id'] ?>')">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </button>
                </div>
            </div>
            <div class="flex-grow-1" style="position: relative; min-height: 280px;">
                <?php if ($hasData): ?>
                <canvas class="admin-chart-canvas" id="chart-custom_<?= $cc['id'] ?>" data-values='<?= json_encode(array_column($cc['data'], 'value')) ?>' data-labels='<?= json_encode(array_column($cc['data'], 'label')) ?>'></canvas>
                <?php else: ?>
                <div class="h-100 d-flex flex-column align-items-center justify-content-center border border-dashed rounded p-4 text-center text-muted" style="min-height: 280px;">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="mb-2"><path d="M21.21 15.89A10 10 0 1 1 8 2.83"/><path d="M22 12A10 10 0 0 0 12 2v10z"/></svg>
                    <span class="small fw-semibold d-block">No custom data registered yet.</span>
                    <?php if ($cc['data_source'] === 'custom_manual'): ?>
                    <button class="btn btn-sm btn-outline-primary mt-2" onclick="openAnalyticsEditor('custom_manual_<?= $cc['id'] ?>', '<?= Security::e($cc['title']) ?>')">Add Initial Entry</button>
                    <?php else: ?>
                    <span class="small text-muted">Awaiting master records...</span>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>

</div>

<!-- ── Activity Feed ─────────────────────────────── -->
<span class="section-label">Recent Activity</span>
<h2 class="section-title" style="font-size:1.3rem">Latest Operations</h2>

<div class="row g-4 mb-4">
    <div class="col-md-6 col-xl-3">
        <div class="overview-panel animate-in">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="section-label mb-0">Registrations</span>
                <a href="index.php?page=admin-users" class="small text-decoration-none">View All →</a>
            </div>
            <h3 class="overview-panel-title" style="font-size:1rem">Latest Participants</h3>
            <?php foreach ($activity['registrations'] as $item): ?>
                <div class="completion-item">
                    <div class="completion-item-title"><?= Security::e($item['name']) ?></div>
                    <div class="completion-item-meta"><?= Security::e($item['email']) ?> · <?= date('M j', strtotime($item['created_at'])) ?></div>
                </div>
            <?php endforeach; ?>
            <?php if (empty($activity['registrations'])): ?><p class="text-muted small mb-0">No recent registrations.</p><?php endif; ?>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="overview-panel animate-in">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="section-label mb-0">Enrolments</span>
                <a href="index.php?page=admin-enrolments" class="small text-decoration-none">View All →</a>
            </div>
            <h3 class="overview-panel-title" style="font-size:1rem">Recent Enrolments</h3>
            <?php foreach ($activity['enrolments'] as $item): ?>
                <div class="completion-item">
                    <div class="completion-item-title"><?= Security::e($item['trainee_name']) ?></div>
                    <div class="completion-item-meta"><?= Security::e($item['course_title']) ?> <span class="completion-item-badge <?= $item['status'] !== 'active' ? 'in-progress' : '' ?>"><?= Security::e($item['status']) ?></span></div>
                </div>
            <?php endforeach; ?>
            <?php if (empty($activity['enrolments'])): ?><p class="text-muted small mb-0">No recent enrolments.</p><?php endif; ?>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="overview-panel animate-in">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="section-label mb-0">Certificates</span>
                <a href="index.php?page=admin-certificates" class="small text-decoration-none">View All →</a>
            </div>
            <h3 class="overview-panel-title" style="font-size:1rem">Recently Issued</h3>
            <?php foreach ($activity['certificates'] as $item): ?>
                <div class="completion-item">
                    <div class="completion-item-title"><?= Security::e($item['trainee_name']) ?></div>
                    <div class="completion-item-meta"><?= Security::e($item['certificate_number'] ?? $item['certificate_no']) ?></div>
                </div>
            <?php endforeach; ?>
            <?php if (empty($activity['certificates'])): ?><p class="text-muted small mb-0">No recent certificates.</p><?php endif; ?>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="overview-panel animate-in">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="section-label mb-0">Evaluations</span>
                <a href="index.php?page=admin-evaluations" class="small text-decoration-none">View All →</a>
            </div>
            <h3 class="overview-panel-title" style="font-size:1rem">Pending Reviews</h3>
            <?php foreach ($activity['evaluations'] as $item): ?>
                <div class="completion-item">
                    <div class="completion-item-title"><?= Security::e($item['trainee_name']) ?></div>
                    <div class="completion-item-meta"><?= Security::e($item['course_title']) ?></div>
                </div>
            <?php endforeach; ?>
            <?php if (empty($activity['evaluations'])): ?><p class="text-muted small mb-0">No pending evaluations.</p><?php endif; ?>
        </div>
    </div>
</div>

<!-- ── Detail/Edit Modal ───────────────── -->
<div class="modal fade" id="analyticsEditModal" tabindex="-1" aria-labelledby="analyticsEditModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content shadow-lg border-0">
            <div class="modal-header bg-light border-bottom">
                <h5 class="modal-title fw-bold" id="analyticsEditModalLabel">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-2"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
                    Detailed Data — <span id="modalChartTitle"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="max-height: 500px; overflow-y: auto;">
                <div id="modalChartEditorBody"></div>
            </div>
            <div class="modal-footer bg-light border-top">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary btn-sm" onclick="saveAnalyticsChanges()">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<!-- ── Create Custom Chart Modal ───────────────── -->
<div class="modal fade" id="createCustomChartModal" tabindex="-1" aria-labelledby="createCustomChartModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg border-0">
            <div class="modal-header bg-light border-bottom">
                <h5 class="modal-title fw-bold" id="createCustomChartModalLabel">
                    Create Custom Analysis Chart
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="createCustomChartForm" onsubmit="submitCustomChartForm(event)">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Chart Title</label>
                        <input type="text" class="form-control" id="customChartTitle" placeholder="e.g. Participants by Location" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Chart Visualization Type</label>
                        <select class="form-select" id="customChartType" required>
                            <option value="bar">Bar Chart</option>
                            <option value="line">Line Chart</option>
                            <option value="pie">Pie Chart</option>
                            <option value="doughnut">Doughnut Chart</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Data Source</label>
                        <select class="form-select" id="customChartSource" required>
                            <option value="academy">Participants by Academy (Live DB)</option>
                            <option value="category">Participants by Category (Live DB)</option>
                            <option value="company">Participants by Company (Live DB)</option>
                            <option value="profession">Participants by Profession (Live DB)</option>
                            <option value="custom_manual">Custom Manual Dataset (Self-managed)</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm">Create Chart</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ── Chart JS Script ────────────── -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    const canvases = document.querySelectorAll('.admin-chart-canvas');
    const chartsMap = {};

    const palette = [
        '#054d9e', '#18a999', '#ea580c', '#16a34a', '#8b5cf6',
        '#0891b2', '#dc2626', '#334155', '#0d9488', '#c2410c',
        '#6366f1', '#059669'
    ];

    function createChart(id, type, labels, values) {
        const canvas = document.getElementById(`chart-${id}`);
        if (!canvas) return null;
        const ctx = canvas.getContext('2d');

        return new Chart(ctx, {
            type: type,
            data: {
                labels: labels,
                datasets: [{
                    label: 'Records',
                    data: values,
                    backgroundColor: type === 'line' ? 'rgba(5, 77, 158, 0.08)' : palette.slice(0, values.length),
                    borderColor: type === 'line' ? '#054d9e' : palette.slice(0, values.length),
                    borderWidth: type === 'line' ? 2.5 : 1,
                    fill: type === 'line',
                    tension: 0.4,
                    pointBackgroundColor: '#054d9e',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: type === 'line' ? 4 : 0,
                    pointHoverRadius: 6,
                    borderRadius: type === 'bar' ? 6 : 0,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    intersect: false,
                    mode: 'index'
                },
                plugins: {
                    legend: {
                        display: (type === 'pie' || type === 'doughnut'),
                        position: 'bottom',
                        labels: {
                            padding: 16,
                            usePointStyle: true,
                            pointStyle: 'circle',
                            font: { family: "'Inter', sans-serif", size: 12 }
                        }
                    },
                    tooltip: {
                        padding: 12,
                        cornerRadius: 8,
                        titleFont: { family: "'Inter', sans-serif", weight: '700' },
                        bodyFont: { family: "'Inter', sans-serif" },
                        backgroundColor: 'rgba(24, 34, 48, .92)',
                        borderColor: 'rgba(255,255,255,.1)',
                        borderWidth: 1
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        display: !(type === 'pie' || type === 'doughnut'),
                        grid: { color: 'rgba(0,0,0,.04)', drawBorder: false },
                        ticks: { font: { family: "'Inter', sans-serif", size: 11 }, color: '#64748b' }
                    },
                    x: {
                        display: !(type === 'pie' || type === 'doughnut'),
                        grid: { display: false },
                        ticks: {
                            maxRotation: 45, minRotation: 0, autoSkip: true, maxTicksLimit: 15,
                            font: { family: "'Inter', sans-serif", size: 11 }, color: '#64748b'
                        }
                    }
                },
                onClick: function(evt, elements) {
                    if (elements.length > 0) {
                        window.location.href = `index.php?page=admin-analytics-detail&chart=${id}&index=${elements[0].index}`;
                    }
                }
            }
        });
    }

    canvases.forEach(canvas => {
        const id = canvas.id.replace('chart-', '');
        const labels = JSON.parse(canvas.dataset.labels || '[]');
        const values = JSON.parse(canvas.dataset.values || '[]').map(Number);
        const savedType = localStorage.getItem(`chart_type_${id}`) ||
                          document.querySelector(`.chart-type-select[data-chart-id="${id}"]`)?.value || 'bar';

        const select = document.querySelector(`.chart-type-select[data-chart-id="${id}"]`);
        if (select) select.value = savedType;

        chartsMap[id] = createChart(id, savedType, labels, values);
    });

    // Visibility management
    const chartWrappers = document.querySelectorAll('.chart-container-wrapper');
    const visibilityTogglesContainer = document.getElementById('chart-visibility-toggles');

    chartWrappers.forEach(wrapper => {
        const id = wrapper.dataset.chartId;
        const title = wrapper.querySelector('.chart-panel-title').textContent.trim();
        const savedVisibility = localStorage.getItem(`chart_visible_${id}`);
        if (savedVisibility === 'false') wrapper.style.display = 'none';

        const isChecked = savedVisibility !== 'false';
        const li = document.createElement('li');
        li.className = 'px-3 py-1';
        li.innerHTML = `<div class="form-check"><input class="form-check-input visibility-checkbox" type="checkbox" id="toggle-${id}" data-chart-id="${id}" ${isChecked ? 'checked' : ''}><label class="form-check-label small" for="toggle-${id}">${title}</label></div>`;
        visibilityTogglesContainer.appendChild(li);
    });

    // Chart type change
    document.querySelectorAll('.chart-type-select').forEach(select => {
        select.addEventListener('change', (e) => {
            const id = e.target.dataset.chartId;
            const type = e.target.value;
            localStorage.setItem(`chart_type_${id}`, type);
            if (chartsMap[id]) {
                const labels = chartsMap[id].data.labels;
                const data = chartsMap[id].data.datasets[0].data;
                chartsMap[id].destroy();
                chartsMap[id] = createChart(id, type, labels, data);
            }
        });
    });

    // Visibility toggle
    document.querySelectorAll('.visibility-checkbox').forEach(chk => {
        chk.addEventListener('change', (e) => {
            const id = e.target.dataset.chartId;
            const isVisible = e.target.checked;
            localStorage.setItem(`chart_visible_${id}`, isVisible ? 'true' : 'false');
            const wrapper = document.querySelector(`.chart-container-wrapper[data-chart-id="${id}"]`);
            if (wrapper) wrapper.style.display = isVisible ? 'block' : 'none';
        });
    });
});

function hideChart(id) {
    localStorage.setItem(`chart_visible_${id}`, 'false');
    const wrapper = document.querySelector(`.chart-container-wrapper[data-chart-id="${id}"]`);
    if (wrapper) wrapper.style.display = 'none';
    const chk = document.getElementById(`toggle-${id}`);
    if (chk) chk.checked = false;
}

function resetDashboardLayout() {
    const keys = ['programme', 'monthly', 'years', 'completion', 'certificates', 'course_participants', 'categories', 'companies', 'professions', 'popularity'];
    keys.forEach(k => { localStorage.removeItem(`chart_visible_${k}`); localStorage.removeItem(`chart_type_${k}`); });
    window.location.reload();
}

let currentChartKey = '';
let activeLookups = {};
let newRowsCount = 0;

function openAnalyticsEditor(chartKey, title) {
    currentChartKey = chartKey;
    newRowsCount = 0;
    const modalEl = document.getElementById('analyticsEditModal');
    if (modalEl && modalEl.parentNode !== document.body) {
        document.body.appendChild(modalEl);
    }
    let modal = bootstrap.Modal.getInstance(modalEl);
    if (!modal) {
        modal = new bootstrap.Modal(modalEl);
    }
    document.getElementById('modalChartTitle').textContent = title;
    const body = document.getElementById('modalChartEditorBody');
    body.innerHTML = `<div class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div><p class="text-muted small mt-2">Fetching dataset details...</p></div>`;
    modal.show();

    fetch(`index.php?page=admin-fetch-analytics-details&chart_key=${chartKey}`)
        .then(response => response.json())
        .then(res => {
            if (res.status === 'success') {
                activeLookups = res.lookups || {};
                let html = '<form id="analyticsEditorForm">';
                html += '<div class="alert alert-info py-2 px-3 small mb-3">Edit underlying data metrics. Database counts are read-only.</div>';
                html += '<div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead class="table-light"><tr><th>Data Label</th><th style="width: 150px;">Value</th></tr></thead><tbody id="analyticsEditorTableBody">';
                res.data.forEach((row, idx) => {
                    const isSystem = (row.source_table === 'enrolments' || row.source_table === 'certificates');
                    html += `<tr><td><span class="fw-semibold text-dark">${escapeHtml(row.label)}</span>${isSystem ? '<span class="badge bg-secondary-subtle text-secondary ms-2 small">Live</span>' : ''}</td><td>${isSystem ? `<input type="text" class="form-control form-control-sm text-center bg-light" value="${row.value}" readonly disabled>` : `<input type="hidden" name="updates[${idx}][table]" value="${row.source_table}"><input type="hidden" name="updates[${idx}][id]" value="${row.id}"><input type="number" class="form-control form-control-sm text-center" name="updates[${idx}][value]" value="${row.value}" required min="0">`}</td></tr>`;
                });
                html += '</tbody></table></div>';
                
                // Add "Add New Entry" button for editable tables
                if (['programme', 'years', 'categories', 'companies', 'professions', 'course_participants', 'popularity'].includes(chartKey)) {
                    html += `<div class="mt-3 text-end"><button type="button" class="btn btn-sm btn-outline-primary" onclick="addNewEditorRow()">+ Add New Entry</button></div>`;
                }
                
                html += '</form>';
                body.innerHTML = html;
            } else {
                body.innerHTML = `<div class="alert alert-danger">Failed: ${res.message || 'error'}</div>`;
            }
        })
        .catch(() => { body.innerHTML = `<div class="alert alert-danger">Network error.</div>`; });
}

function addNewEditorRow() {
    const tbody = document.getElementById('analyticsEditorTableBody');
    if (!tbody) return;

    let tr = document.createElement('tr');
    tr.className = 'table-info-subtle';
    let labelCell = document.createElement('td');
    let valueCell = document.createElement('td');

    const idx = newRowsCount++;

    if (currentChartKey === 'years') {
        labelCell.innerHTML = `<input type="number" class="form-control form-control-sm" name="new_rows[${idx}][label]" placeholder="Year (e.g. 2026)" required min="2000"><input type="hidden" name="new_rows[${idx}][table]" value="yearly_reports">`;
        valueCell.innerHTML = `<input type="number" class="form-control form-control-sm text-center" name="new_rows[${idx}][value]" placeholder="Value" required min="0">`;
    } else if (['programme', 'course_participants', 'popularity'].includes(currentChartKey)) {
        let optionsHtml = (activeLookups.academies || []).map(a => `<option value="${a.id}">${escapeHtml(a.code)} — ${escapeHtml(a.name)}</option>`).join('');
        labelCell.innerHTML = `
            <div class="row g-2">
                <div class="col-sm-6">
                    <select class="form-select form-select-sm" name="new_rows[${idx}][academy_id]" required>
                        <option value="">Select Academy</option>
                        ${optionsHtml}
                    </select>
                </div>
                <div class="col-sm-6">
                    <input type="text" class="form-control form-control-sm" name="new_rows[${idx}][course_name]" placeholder="Course Name" required>
                </div>
            </div>
            <input type="hidden" name="new_rows[${idx}][table]" value="training_statistics">
        `;
        valueCell.innerHTML = `<input type="number" class="form-control form-control-sm text-center" name="new_rows[${idx}][value]" placeholder="Value" required min="0">`;
    } else if (currentChartKey === 'categories') {
        let optionsHtml = (activeLookups.categories || []).map(c => `<option value="${c.id}">${escapeHtml(c.name)}</option>`).join('');
        labelCell.innerHTML = `
            <select class="form-select form-select-sm" name="new_rows[${idx}][category_id]" required>
                <option value="">Select Category</option>
                ${optionsHtml}
            </select>
            <input type="hidden" name="new_rows[${idx}][table]" value="participant_statistics">
            <input type="hidden" name="new_rows[${idx}][type]" value="category">
        `;
        valueCell.innerHTML = `<input type="number" class="form-control form-control-sm text-center" name="new_rows[${idx}][value]" placeholder="Value" required min="0">`;
    } else if (currentChartKey === 'companies') {
        let optionsHtml = (activeLookups.companies || []).map(c => `<option value="${c.id}">${escapeHtml(c.name)}</option>`).join('');
        labelCell.innerHTML = `
            <select class="form-select form-select-sm" name="new_rows[${idx}][company_id]" required>
                <option value="">Select Company</option>
                ${optionsHtml}
            </select>
            <input type="hidden" name="new_rows[${idx}][table]" value="participant_statistics">
            <input type="hidden" name="new_rows[${idx}][type]" value="company">
        `;
        valueCell.innerHTML = `<input type="number" class="form-control form-control-sm text-center" name="new_rows[${idx}][value]" placeholder="Value" required min="0">`;
    } else if (currentChartKey === 'professions') {
        let optionsHtml = (activeLookups.professions || []).map(p => `<option value="${p.id}">${escapeHtml(p.name)}</option>`).join('');
        labelCell.innerHTML = `
            <select class="form-select form-select-sm" name="new_rows[${idx}][profession_id]" required>
                <option value="">Select Profession</option>
                ${optionsHtml}
            </select>
            <input type="hidden" name="new_rows[${idx}][table]" value="participant_statistics">
            <input type="hidden" name="new_rows[${idx}][type]" value="profession">
        `;
        valueCell.innerHTML = `<input type="number" class="form-control form-control-sm text-center" name="new_rows[${idx}][value]" placeholder="Value" required min="0">`;
    } else if (currentChartKey.startsWith('custom_manual_')) {
        const customId = currentChartKey.replace('custom_manual_', '');
        labelCell.innerHTML = `<input type="text" class="form-control form-control-sm" name="new_rows[${idx}][label]" placeholder="Label/Name" required><input type="hidden" name="new_rows[${idx}][table]" value="custom_analytics_data"><input type="hidden" name="new_rows[${idx}][custom_analytic_id]" value="${customId}">`;
        valueCell.innerHTML = `<input type="number" class="form-control form-control-sm text-center" name="new_rows[${idx}][value]" placeholder="Value" required min="0">`;
    }

    tr.appendChild(labelCell);
    tr.appendChild(valueCell);
    tbody.appendChild(tr);
}

function escapeHtml(str) { return String(str).replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;"); }

function saveAnalyticsChanges() {
    const form = document.getElementById('analyticsEditorForm');
    if (!form) return;

    // Validate inputs
    if (!form.reportValidity()) {
        return;
    }
    
    // Extract updates
    const updatesInputs = form.querySelectorAll('input[name^="updates["]');
    const updatesGroups = {};
    updatesInputs.forEach(input => {
        const match = input.name.match(/updates\[(\d+)\]\[(\w+)\]/);
        if (match) {
            if (!updatesGroups[match[1]]) updatesGroups[match[1]] = {};
            updatesGroups[match[1]][match[2]] = input.value;
        }
    });
    const updates = Object.values(updatesGroups);

    // Extract new rows
    const newRowsInputs = form.querySelectorAll('[name^="new_rows["]');
    const newRowsGroups = {};
    newRowsInputs.forEach(input => {
        const match = input.name.match(/new_rows\[(\d+)\]\[(\w+)\]/);
        if (match) {
            if (!newRowsGroups[match[1]]) newRowsGroups[match[1]] = {};
            newRowsGroups[match[1]][match[2]] = input.value;
        }
    });
    const newRows = Object.values(newRowsGroups);

    if (updates.length === 0 && newRows.length === 0) {
        bootstrap.Modal.getInstance(document.getElementById('analyticsEditModal')).hide();
        return;
    }

    const csrf = '<?= Security::csrfToken() ?>';
    fetch('index.php?page=admin-save-analytics-details', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrf },
        body: JSON.stringify({ updates: updates, new_rows: newRows, _csrf: csrf })
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(err => { throw new Error(err.message || 'Server error'); });
        }
        return response.json();
    })
    .then(res => {
        if (res.status === 'success') {
            bootstrap.Modal.getInstance(document.getElementById('analyticsEditModal')).hide();
            window.location.reload();
        } else {
            alert('Error: ' + (res.message || 'unknown'));
        }
    })
    .catch((err) => { 
        alert('Network or Database Error: ' + err.message); 
    });
}

function openCreateCustomChartModal() {
    const modalEl = document.getElementById('createCustomChartModal');
    if (modalEl && modalEl.parentNode !== document.body) {
        document.body.appendChild(modalEl);
    }
    let modal = bootstrap.Modal.getInstance(modalEl);
    if (!modal) {
        modal = new bootstrap.Modal(modalEl);
    }
    document.getElementById('createCustomChartForm').reset();
    modal.show();
}

function submitCustomChartForm(e) {
    e.preventDefault();
    const title = document.getElementById('customChartTitle').value;
    const chartType = document.getElementById('customChartType').value;
    const dataSource = document.getElementById('customChartSource').value;
    const csrf = '<?= Security::csrfToken() ?>';

    fetch('index.php?page=admin-create-custom-chart', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrf },
        body: JSON.stringify({ title, chart_type: chartType, data_source: dataSource, _csrf: csrf })
    })
    .then(response => {
        if (!response.ok) throw new Error('Failed to create chart');
        return response.json();
    })
    .then(res => {
        if (res.status === 'success') {
            bootstrap.Modal.getInstance(document.getElementById('createCustomChartModal')).hide();
            window.location.reload();
        } else {
            alert('Error: ' + res.message);
        }
    })
    .catch(err => alert(err.message));
}

function deleteCustomChart(id) {
    if (!confirm('Are you sure you want to delete this custom chart? This action cannot be undone.')) return;
    const csrf = '<?= Security::csrfToken() ?>';

    fetch('index.php?page=admin-delete-custom-chart', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrf },
        body: JSON.stringify({ id, _csrf: csrf })
    })
    .then(response => {
        if (!response.ok) throw new Error('Failed to delete chart');
        return response.json();
    })
    .then(res => {
        if (res.status === 'success') {
            window.location.reload();
        } else {
            alert('Error: ' + res.message);
        }
    })
    .catch(err => alert(err.message));
}
</script>
