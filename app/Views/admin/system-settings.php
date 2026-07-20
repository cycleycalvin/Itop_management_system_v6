<?php use App\Core\Security; ?>

<div class="d-flex justify-content-between align-items-md-center flex-column flex-md-row mb-4 gap-2">
    <div>
        <span class="section-label">Settings</span>
        <h1 class="section-title mb-0">System Settings & Logs</h1>
    </div>
    <div>
        <a href="index.php?page=dashboard" class="btn btn-outline-secondary btn-sm">← Back to Dashboard</a>
    </div>
</div>

<?php if (!empty($_SESSION['flash_success'])): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <?= Security::e($_SESSION['flash_success']) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php unset($_SESSION['flash_success']); endif; ?>

<?php if (!empty($_SESSION['flash_error'])): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <?= Security::e($_SESSION['flash_error']) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php unset($_SESSION['flash_error']); endif; ?>

<div class="row g-4">
    <!-- Server Status Column -->
    <div class="col-lg-5">
        <div class="overview-panel mb-4 animate-in">
            <h3 class="overview-panel-title">Server Information</h3>
            <div class="overview-detail-list">
                <div class="overview-detail-row">
                    <span class="overview-detail-label">PHP Version</span>
                    <span class="overview-detail-value"><?= phpversion() ?></span>
                </div>
                <div class="overview-detail-row">
                    <span class="overview-detail-label">Server Software</span>
                    <span class="overview-detail-value"><?= Security::e($_SERVER['SERVER_SOFTWARE'] ?? 'Apache') ?></span>
                </div>
                <div class="overview-detail-row">
                    <span class="overview-detail-label">Database Server</span>
                    <span class="overview-detail-value">MySQL/MariaDB via PDO</span>
                </div>
                <div class="overview-detail-row">
                    <span class="overview-detail-label">Timezone</span>
                    <span class="overview-detail-value"><?= Security::e(date_default_timezone_get()) ?></span>
                </div>
                <div class="overview-detail-row">
                    <span class="overview-detail-label">Max File Upload Size</span>
                    <span class="overview-detail-value"><?= ini_get('upload_max_filesize') ?></span>
                </div>
            </div>
        </div>

        <div class="overview-panel mb-4 animate-in">
            <h3 class="overview-panel-title">Storage Directory Checks</h3>
            <div class="overview-detail-list">
                <div class="overview-detail-row">
                    <span class="overview-detail-label">Uploads Path</span>
                    <span class="overview-detail-value">
                        <?php if ($uploadWritable): ?>
                            <span class="badge bg-success-subtle text-success">Writable</span>
                        <?php else: ?>
                            <span class="badge bg-danger-subtle text-danger">Not Writable</span>
                        <?php endif; ?>
                    </span>
                </div>
                <div class="overview-detail-row">
                    <span class="overview-detail-label">Submissions Path</span>
                    <span class="overview-detail-value">
                        <?php if ($submissionWritable): ?>
                            <span class="badge bg-success-subtle text-success">Writable</span>
                        <?php else: ?>
                            <span class="badge bg-danger-subtle text-danger">Not Writable</span>
                        <?php endif; ?>
                    </span>
                </div>
                <div class="overview-detail-row">
                    <span class="overview-detail-label">Certificates Path</span>
                    <span class="overview-detail-value">
                        <?php if ($certWritable): ?>
                            <span class="badge bg-success-subtle text-success">Writable</span>
                        <?php else: ?>
                            <span class="badge bg-danger-subtle text-danger">Not Writable</span>
                        <?php endif; ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- Maintenance Mode -->
        <div class="overview-panel mb-4 animate-in">
            <h3 class="overview-panel-title">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-2"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg>
                Maintenance Mode
            </h3>
            <form method="post" action="index.php?page=admin-save-system-settings">
                <input type="hidden" name="_csrf" value="<?= Security::e(Security::csrfToken()) ?>">
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" role="switch" id="maintenanceModeSwitch" name="maintenance_mode" <?= $maintenanceMode ? 'checked' : '' ?>>
                    <label class="form-check-label fw-semibold" for="maintenanceModeSwitch">
                        <?= $maintenanceMode ? '<span class="text-danger">Maintenance Mode is ON</span>' : 'Maintenance Mode is OFF' ?>
                    </label>
                </div>
                <p class="text-muted small mb-3">When enabled, only administrators can access the system. All other users will see a maintenance page.</p>
                <button type="submit" class="btn btn-sm btn-primary w-100">Save Settings</button>
            </form>
        </div>

        <!-- Database Backup & Restore -->
        <div class="overview-panel mb-4 animate-in">
            <h3 class="overview-panel-title">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-2"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"/><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/></svg>
                Database Management
            </h3>
            <div class="d-grid gap-2 mb-3">
                <a href="index.php?page=admin-backup-database" class="btn btn-outline-primary btn-sm">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-1"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                    Download Full Backup (.sql)
                </a>
            </div>
            <hr>
            <form method="post" action="index.php?page=admin-restore-database" enctype="multipart/form-data" onsubmit="return confirm('⚠️ WARNING: This will REPLACE your entire database with the uploaded file. Are you absolutely sure?');">
                <input type="hidden" name="_csrf" value="<?= Security::e(Security::csrfToken()) ?>">
                <label class="form-label small fw-semibold text-danger">Restore Database from Backup</label>
                <input type="file" class="form-control form-control-sm mb-2" name="sql_file" accept=".sql" required>
                <button type="submit" class="btn btn-sm btn-outline-danger w-100">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-1"><polyline points="16 16 12 12 8 16"/><line x1="12" y1="12" x2="12" y2="21"/><path d="M20.39 18.39A5 5 0 0 0 18 9h-1.26A8 8 0 1 0 3 16.3"/></svg>
                    Upload & Restore
                </button>
            </form>
        </div>
    </div>

    <!-- System Logs Column -->
    <div class="col-lg-7">
        <div class="chart-panel animate-in">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3 class="chart-panel-title mb-0">System Activity Logs</h3>
                <span class="badge bg-primary-subtle text-primary"><?= !empty($search) ? 'Search Results' : 'Last 50 Entries' ?></span>
            </div>
            <!-- Search Form -->
            <form class="d-flex gap-2 mb-3" method="get">
                <input type="hidden" name="page" value="admin-system-settings">
                <input class="form-control form-control-sm" name="search" value="<?= Security::e($search ?? '') ?>" placeholder="Search logs by user, action, or details...">
                <button class="btn btn-sm btn-primary text-nowrap" type="submit">Search</button>
                <?php if (!empty($search)): ?>
                    <a href="index.php?page=admin-system-settings" class="btn btn-sm btn-outline-secondary text-nowrap">Clear</a>
                <?php endif; ?>
            </form>
            <div class="table-responsive" style="max-height: 520px; overflow-y: auto;">
                <table class="table table-hover align-middle mb-0" id="logsTable">
                    <thead>
                        <tr>
                            <th>Time</th>
                            <th>User</th>
                            <th>Action</th>
                            <th>Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($logs as $log): ?>
                        <tr>
                            <td class="text-muted small" style="white-space: nowrap;">
                                <?= date('M j, g:i a', strtotime($log['created_at'])) ?>
                            </td>
                            <td>
                                <div class="d-flex flex-column">
                                    <span class="fw-semibold small text-dark"><?= Security::e($log['user_name'] ?? 'System') ?></span>
                                    <span class="text-muted" style="font-size: 0.72rem;"><?= Security::e($log['user_email'] ?? '') ?></span>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-secondary-subtle text-secondary small">
                                    <?= Security::e($log['action']) ?>
                                </span>
                            </td>
                            <td class="text-muted small" style="max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="<?= Security::e($log['details'] ?? '') ?>">
                                <?= Security::e($log['details'] ?? '—') ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($logs)): ?>
                        <tr>
                            <td colspan="4" class="text-center py-4 text-muted">No activity logs recorded.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
