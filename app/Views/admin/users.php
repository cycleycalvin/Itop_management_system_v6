<?php use App\Core\Security; use App\Core\View; ?>
<?php View::partial('partials/role-nav'); ?>

<!-- ═══ User Management — Professional Admin UI ═══ -->
<div class="um-container" id="umContainer">

    <!-- ── Breadcrumb ── -->
    <nav class="um-breadcrumb" aria-label="breadcrumb">
        <a href="index.php?page=admin-dashboard">Dashboard</a>
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
        <span>User Management</span>
    </nav>

    <!-- ════════════════════════════════════════════════════
         VIEW 1: USER LIST (default)
         ════════════════════════════════════════════════════ -->
    <div class="um-view um-view-active" id="umViewList">

        <!-- Header Row -->
        <div class="um-header">
            <div class="um-header-left">
                <h1 class="um-title">User Management</h1>
                <p class="um-subtitle">Manage administrators, instructors, and trainees</p>
            </div>
            <button class="um-btn um-btn-primary" id="umBtnCreateUser" type="button">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Create User
            </button>
        </div>

        <!-- Stat Cards -->
        <div class="um-stats">
            <div class="um-stat-card">
                <div class="um-stat-icon um-stat-icon-all">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                </div>
                <div class="um-stat-info">
                    <span class="um-stat-value"><?= (int) $totalAll ?></span>
                    <span class="um-stat-label">Total Users</span>
                </div>
            </div>
            <div class="um-stat-card">
                <div class="um-stat-icon um-stat-icon-admin">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                </div>
                <div class="um-stat-info">
                    <span class="um-stat-value"><?= (int) $totalAdmin ?></span>
                    <span class="um-stat-label">Admins</span>
                </div>
            </div>
            <div class="um-stat-card">
                <div class="um-stat-icon um-stat-icon-instructor">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
                </div>
                <div class="um-stat-info">
                    <span class="um-stat-value"><?= (int) $totalInstructor ?></span>
                    <span class="um-stat-label">Instructors</span>
                </div>
            </div>
            <div class="um-stat-card">
                <div class="um-stat-icon um-stat-icon-trainee">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c0 2 4 3 6 3s6-1 6-3v-5"/></svg>
                </div>
                <div class="um-stat-info">
                    <span class="um-stat-value"><?= (int) $totalTrainee ?></span>
                    <span class="um-stat-label">Trainees</span>
                </div>
            </div>
        </div>

        <!-- Role Filter Tabs + Search/Filter -->
        <div class="um-toolbar">
            <div class="um-tabs" role="tablist">
                <?php
                $tabItems = [
                    '' => ['label' => 'All Users', 'count' => $totalAll],
                    'admin' => ['label' => 'Admins', 'count' => $totalAdmin],
                    'instructor' => ['label' => 'Instructors', 'count' => $totalInstructor],
                    'trainee' => ['label' => 'Trainees', 'count' => $totalTrainee],
                ];
                foreach ($tabItems as $slug => $tab): ?>
                    <a class="um-tab <?= $role === $slug ? 'um-tab-active' : '' ?>"
                       href="index.php?page=admin-users&role=<?= Security::e($slug) ?>&q=<?= urlencode((string) $q) ?>&status=<?= urlencode((string) $status) ?>"
                       role="tab" aria-selected="<?= $role === $slug ? 'true' : 'false' ?>">
                        <?= Security::e($tab['label']) ?>
                        <span class="um-tab-badge"><?= (int) $tab['count'] ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
            <form class="um-filters" method="get">
                <input type="hidden" name="page" value="admin-users">
                <input type="hidden" name="role" value="<?= Security::e($role) ?>">
                <div class="um-search-box">
                    <svg class="um-search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    <input class="um-search-input" name="q" value="<?= Security::e((string) $q) ?>" placeholder="Search by name, email, or phone…" autocomplete="off">
                </div>
                <select class="um-select" name="status">
                    <option value="">All Status</option>
                    <?php foreach (['pending', 'active', 'inactive', 'suspended'] as $state): ?>
                        <option value="<?= $state ?>" <?= $status === $state ? 'selected' : '' ?>><?= ucfirst($state) ?></option>
                    <?php endforeach; ?>
                </select>
                <button class="um-btn um-btn-secondary" type="submit">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                    Filter
                </button>
            </form>
        </div>

        <!-- User Table -->
        <div class="um-table-wrapper">
            <?php if (empty($users)): ?>
                <div class="um-empty-state">
                    <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/></svg>
                    <h3>No users found</h3>
                    <p>Try adjusting your search or filter criteria, or create a new user.</p>
                    <button class="um-btn um-btn-primary" onclick="document.getElementById('umBtnCreateUser').click()">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        Create User
                    </button>
                </div>
            <?php else: ?>
                <table class="um-table">
                    <thead>
                        <tr>
                            <th class="um-th-user">User</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Last Login</th>
                            <th class="um-th-actions">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user):
                            $initials = '';
                            $words = explode(' ', trim($user['name']));
                            $initials = strtoupper(substr($words[0] ?? '', 0, 1) . substr(end($words) ?: '', 0, 1));
                            if (strlen($initials) < 2) $initials = strtoupper(substr($user['name'], 0, 2));

                            $roleSlug = $user['role_slug'] ?? 'trainee';
                            $roleColors = [
                                'admin' => 'um-role-admin',
                                'instructor' => 'um-role-instructor',
                                'trainee' => 'um-role-trainee',
                            ];
                            $roleClass = $roleColors[$roleSlug] ?? 'um-role-trainee';

                            $statusColors = [
                                'active' => 'um-status-active',
                                'pending' => 'um-status-pending',
                                'inactive' => 'um-status-inactive',
                                'suspended' => 'um-status-suspended',
                            ];
                            $statusClass = $statusColors[$user['status']] ?? 'um-status-inactive';
                        ?>
                            <tr class="um-row" data-user-id="<?= (int) $user['id'] ?>">
                                <td>
                                    <div class="um-user-cell">
                                        <div class="um-avatar <?= $roleClass ?>">
                                            <?php if (!empty($user['profile_picture'])): ?>
                                                <img src="storage/uploads/<?= Security::e($user['profile_picture']) ?>" alt="" class="um-avatar-img">
                                            <?php else: ?>
                                                <?= Security::e($initials) ?>
                                            <?php endif; ?>
                                        </div>
                                        <div class="um-user-info">
                                            <span class="um-user-name"><?= Security::e($user['name']) ?></span>
                                            <span class="um-user-email"><?= Security::e($user['email']) ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="um-badge <?= $roleClass ?>"><?= Security::e($user['role_name']) ?></span>
                                </td>
                                <td>
                                    <span class="um-status-pill <?= $statusClass ?>">
                                        <span class="um-status-dot"></span>
                                        <?= Security::e(ucfirst($user['status'])) ?>
                                    </span>
                                </td>
                                <td class="um-cell-muted"><?= Security::e($user['last_login'] ?? '—') ?></td>
                                <td>
                                    <div class="um-actions">
                                        <button class="um-action-trigger" type="button" aria-label="Actions" data-um-dropdown>
                                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="5" r="1"/><circle cx="12" cy="12" r="1"/><circle cx="12" cy="19" r="1"/></svg>
                                        </button>
                                        <div class="um-dropdown-menu">
                                            <button class="um-dropdown-item" type="button" data-um-view="<?= (int) $user['id'] ?>">
                                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                                View Details
                                            </button>
                                            <button class="um-dropdown-item" type="button" data-um-edit="<?= (int) $user['id'] ?>"
                                                    data-user='<?= Security::e(json_encode([
                                                        'id' => $user['id'],
                                                        'name' => $user['name'],
                                                        'email' => $user['email'],
                                                        'phone' => $user['phone'] ?? '',
                                                        'address' => $user['address'] ?? '',
                                                        'role_slug' => $user['role_slug'],
                                                        'status' => $user['status'],
                                                    ])) ?>'>
                                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                                Edit User
                                            </button>
                                            <div class="um-dropdown-divider"></div>
                                            <button class="um-dropdown-item um-dropdown-item-danger" type="button"
                                                    data-um-delete="<?= (int) $user['id'] ?>"
                                                    data-user-name="<?= Security::e($user['name']) ?>">
                                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                                Delete User
                                            </button>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <div class="um-pagination">
                <span class="um-page-info">
                    Showing <?= (($pageNo - 1) * 10) + 1 ?>–<?= min($pageNo * 10, $totalFiltered) ?> of <?= (int) $totalFiltered ?> users
                </span>
                <div class="um-page-controls">
                    <a class="um-page-btn <?= $pageNo <= 1 ? 'um-page-btn-disabled' : '' ?>"
                       href="index.php?page=admin-users&p=<?= max(1, $pageNo - 1) ?>&role=<?= Security::e($role) ?>&q=<?= urlencode((string) $q) ?>&status=<?= urlencode((string) $status) ?>">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
                    </a>
                    <?php
                    $startPage = max(1, $pageNo - 2);
                    $endPage = min($totalPages, $pageNo + 2);
                    if ($startPage > 1): ?>
                        <a class="um-page-num" href="index.php?page=admin-users&p=1&role=<?= Security::e($role) ?>&q=<?= urlencode((string) $q) ?>&status=<?= urlencode((string) $status) ?>">1</a>
                        <?php if ($startPage > 2): ?><span class="um-page-ellipsis">…</span><?php endif; ?>
                    <?php endif;
                    for ($i = $startPage; $i <= $endPage; $i++): ?>
                        <a class="um-page-num <?= $i === $pageNo ? 'um-page-num-active' : '' ?>"
                           href="index.php?page=admin-users&p=<?= $i ?>&role=<?= Security::e($role) ?>&q=<?= urlencode((string) $q) ?>&status=<?= urlencode((string) $status) ?>"><?= $i ?></a>
                    <?php endfor;
                    if ($endPage < $totalPages): ?>
                        <?php if ($endPage < $totalPages - 1): ?><span class="um-page-ellipsis">…</span><?php endif; ?>
                        <a class="um-page-num" href="index.php?page=admin-users&p=<?= $totalPages ?>&role=<?= Security::e($role) ?>&q=<?= urlencode((string) $q) ?>&status=<?= urlencode((string) $status) ?>"><?= $totalPages ?></a>
                    <?php endif; ?>
                    <a class="um-page-btn <?= $pageNo >= $totalPages ? 'um-page-btn-disabled' : '' ?>"
                       href="index.php?page=admin-users&p=<?= min($totalPages, $pageNo + 1) ?>&role=<?= Security::e($role) ?>&q=<?= urlencode((string) $q) ?>&status=<?= urlencode((string) $status) ?>">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
                    </a>
                </div>
            </div>
        <?php endif; ?>

    </div><!-- /umViewList -->


    <!-- ════════════════════════════════════════════════════
         VIEW 2: CREATE USER
         ════════════════════════════════════════════════════ -->
    <div class="um-view" id="umViewCreate">
        <div class="um-view-header">
            <button class="um-back-btn" type="button" id="umBtnBackFromCreate">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                Back to User List
            </button>
            <h2 class="um-view-title">Create New User</h2>
            <p class="um-view-subtitle">Fill in the details below to create a new user account</p>
        </div>

        <div class="um-form-card">
            <form method="post" action="index.php?page=save-user" id="umCreateForm">
                <input type="hidden" name="_csrf" value="<?= Security::e(Security::csrfToken()) ?>">
                <input type="hidden" name="id" value="0">

                <div class="um-form-grid">
                    <div class="um-form-group um-form-full">
                        <label class="um-label" for="createName">Full Name <span class="um-required">*</span></label>
                        <input class="um-input" id="createName" name="name" placeholder="Enter full name" required>
                    </div>
                    <div class="um-form-group">
                        <label class="um-label" for="createEmail">Email Address <span class="um-required">*</span></label>
                        <input class="um-input" id="createEmail" type="email" name="email" placeholder="user@example.com" required>
                    </div>
                    <div class="um-form-group">
                        <label class="um-label" for="createPhone">Phone Number</label>
                        <input class="um-input" id="createPhone" name="phone" placeholder="+60 12-345 6789">
                    </div>
                    <div class="um-form-group um-form-full">
                        <label class="um-label" for="createAddress">Address</label>
                        <textarea class="um-input um-textarea" id="createAddress" name="address" rows="2" placeholder="Enter address"></textarea>
                    </div>
                    <div class="um-form-group">
                        <label class="um-label" for="createRole">Role <span class="um-required">*</span></label>
                        <select class="um-input um-select-input" id="createRole" name="role_slug">
                            <?php foreach ($roles as $item): ?>
                                <option value="<?= Security::e($item['slug']) ?>" <?= $role === $item['slug'] ? 'selected' : '' ?>><?= Security::e($item['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="um-form-group">
                        <label class="um-label" for="createStatus">Status</label>
                        <select class="um-input um-select-input" id="createStatus" name="status">
                            <?php foreach (['active', 'pending', 'inactive', 'suspended'] as $state): ?>
                                <option value="<?= $state ?>"><?= ucfirst($state) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="um-form-group um-form-full">
                        <label class="um-label" for="createPassword">Password <span class="um-required">*</span></label>
                        <input class="um-input" id="createPassword" type="password" name="password" placeholder="Minimum 8 characters" required minlength="8">
                        <span class="um-hint">Password must be at least 8 characters long</span>
                    </div>
                </div>

                <div class="um-form-actions">
                    <button class="um-btn um-btn-ghost" type="button" id="umCancelCreate">Cancel</button>
                    <button class="um-btn um-btn-primary" type="submit">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        Create Account
                    </button>
                </div>
            </form>
        </div>
    </div><!-- /umViewCreate -->

</div><!-- /umContainer -->


<!-- ════════════════════════════════════════════════════
     VIEW USER DETAIL — Slide-in Panel
     ════════════════════════════════════════════════════ -->
<div class="um-panel-overlay" id="umPanelOverlay" data-no-motion></div>
<div class="um-detail-panel" id="umDetailPanel" data-no-motion>
    <div class="um-panel-header">
        <h3 class="um-panel-title">User Details</h3>
        <button class="um-panel-close" id="umPanelClose" type="button" aria-label="Close">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
    </div>
    <div class="um-panel-body" id="umPanelBody">
        <div class="um-panel-loading" id="umPanelLoading">
            <div class="um-spinner"></div>
            <span>Loading user details…</span>
        </div>
        <div class="um-panel-content" id="umPanelContent" style="display:none;">
            <!-- Populated by JavaScript -->
        </div>
    </div>
</div>


<!-- ════════════════════════════════════════════════════
     EDIT USER — Modal
     ════════════════════════════════════════════════════ -->
<div class="um-modal-overlay" id="umEditOverlay" data-no-motion>
    <div class="um-modal" id="umEditModal">
        <div class="um-modal-header">
            <h3 class="um-modal-title">Edit User</h3>
            <button class="um-modal-close" id="umEditClose" type="button" aria-label="Close">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <form method="post" action="index.php?page=save-user" id="umEditForm">
            <input type="hidden" name="_csrf" value="<?= Security::e(Security::csrfToken()) ?>">
            <input type="hidden" name="id" id="editUserId" value="0">
            <div class="um-modal-body">
                <div class="um-form-grid">
                    <div class="um-form-group um-form-full">
                        <label class="um-label" for="editName">Full Name <span class="um-required">*</span></label>
                        <input class="um-input" id="editName" name="name" required>
                    </div>
                    <div class="um-form-group">
                        <label class="um-label" for="editEmail">Email Address <span class="um-required">*</span></label>
                        <input class="um-input" id="editEmail" type="email" name="email" required>
                    </div>
                    <div class="um-form-group">
                        <label class="um-label" for="editPhone">Phone Number</label>
                        <input class="um-input" id="editPhone" name="phone">
                    </div>
                    <div class="um-form-group um-form-full">
                        <label class="um-label" for="editAddress">Address</label>
                        <textarea class="um-input um-textarea" id="editAddress" name="address" rows="2"></textarea>
                    </div>
                    <div class="um-form-group">
                        <label class="um-label" for="editRole">Role <span class="um-required">*</span></label>
                        <select class="um-input um-select-input" id="editRole" name="role_slug">
                            <?php foreach ($roles as $item): ?>
                                <option value="<?= Security::e($item['slug']) ?>"><?= Security::e($item['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="um-form-group">
                        <label class="um-label" for="editStatus">Status</label>
                        <select class="um-input um-select-input" id="editStatus" name="status">
                            <?php foreach (['active', 'pending', 'inactive', 'suspended'] as $state): ?>
                                <option value="<?= $state ?>"><?= ucfirst($state) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="um-form-group um-form-full">
                        <label class="um-label" for="editPassword">New Password</label>
                        <input class="um-input" id="editPassword" type="password" name="password" placeholder="Leave blank to keep current password">
                        <span class="um-hint">Only fill this if you want to change the password</span>
                    </div>
                </div>
            </div>
            <div class="um-modal-footer">
                <button class="um-btn um-btn-ghost" type="button" id="umEditCancel">Cancel</button>
                <button class="um-btn um-btn-primary" type="submit">Save Changes</button>
            </div>
        </form>
    </div>
</div>


<!-- ════════════════════════════════════════════════════
     DELETE CONFIRMATION — Modal
     ════════════════════════════════════════════════════ -->
<div class="um-modal-overlay" id="umDeleteOverlay" data-no-motion>
    <div class="um-modal um-modal-sm">
        <div class="um-modal-header um-modal-header-danger">
            <div class="um-delete-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
            </div>
            <h3 class="um-modal-title">Delete User</h3>
        </div>
        <div class="um-modal-body um-text-center">
            <p>Are you sure you want to delete <strong id="umDeleteName"></strong>?</p>
            <p class="um-text-muted">This action cannot be undone. All user data will be permanently removed.</p>
        </div>
        <form method="post" action="index.php?page=delete-user" id="umDeleteForm">
            <input type="hidden" name="_csrf" value="<?= Security::e(Security::csrfToken()) ?>">
            <input type="hidden" name="id" id="deleteUserId" value="0">
            <div class="um-modal-footer um-modal-footer-center">
                <button class="um-btn um-btn-ghost" type="button" id="umDeleteCancel">Cancel</button>
                <button class="um-btn um-btn-danger" type="submit">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                    Delete Permanently
                </button>
            </div>
        </form>
    </div>
</div>
