<?php
use App\Core\Security;
use App\Core\View;

$initials = strtoupper(substr($user['name'] ?? 'A', 0, 1));
$employeeId = $user['employee_id'] ?? ('ADM-' . date('Y') . '-' . str_pad((string)$user['id'], 3, '0', STR_PAD_LEFT));
$position = $user['position'] ?? 'Senior System Administrator';
$department = $user['department'] ?? 'IT & Technical Services';
$officeLocation = $user['office_location'] ?? 'CENTEXS Kuching Campus';
$themePref = $user['theme_preference'] ?? 'light';
$landingPref = $user['default_landing_page'] ?? 'dashboard';
$passwordChangedAt = !empty($user['password_changed_at']) ? date('M d, Y, h:i A', strtotime($user['password_changed_at'])) : 'Not recorded (Default)';
$lastLoginText = !empty($user['last_login']) ? date('M d, Y, h:i A', strtotime($user['last_login'])) : 'First active session';

$notifPrefs = !empty($user['notification_preferences']) ? json_decode($user['notification_preferences'], true) : [];
$emailAlerts = $notifPrefs['email_alerts'] ?? true;
$securityAlerts = $notifPrefs['security_alerts'] ?? true;
$systemAnnouncements = $notifPrefs['system_announcements'] ?? true;
?>

<div class="admin-profile-container animate-in">

    <!-- Flash Notifications -->
    <?php if (!empty($_GET['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show mb-4 shadow-sm" role="alert">
            <div class="d-flex align-items-center gap-2">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                <span><?= Security::e($_GET['success']) ?></span>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (!empty($_GET['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show mb-4 shadow-sm" role="alert">
            <div class="d-flex align-items-center gap-2">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                <span><?= Security::e($_GET['error']) ?></span>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- ══ Centralized Profile Header Card ══ -->
    <div class="admin-profile-header-card mb-4 p-4 d-flex align-items-center flex-wrap flex-md-nowrap gap-4">
        
        <!-- Left: Framed Avatar Window -->
        <div class="admin-profile-avatar-wrapper flex-shrink-0" onclick="document.getElementById('adminAvatarFileInput').click()" title="Click to change profile picture" style="width: 120px; height: 120px; min-width: 120px; min-height: 120px; max-width: 120px; max-height: 120px; border-radius: 20px; border: 4px solid #ffffff; outline: 2px solid #cbd5e1; box-shadow: 0 8px 20px rgba(0,0,0,0.12); position: relative; overflow: hidden; cursor: pointer; flex-shrink: 0; margin: 0; background: #ffffff;">
            <?php if (!empty($user['profile_picture']) && file_exists(UPLOAD_PATH . '/' . $user['profile_picture'])): ?>
                <img src="storage/uploads/<?= Security::e($user['profile_picture']) ?>" alt="Profile Picture" class="admin-avatar-img" style="width: 100%; height: 100%; object-fit: cover; object-position: center; display: block; border-radius: 14px;">
            <?php else: ?>
                <div class="admin-avatar-initials" style="width: 100%; height: 100%; display: grid; place-items: center; color: #ffffff; font-size: 2.8rem; font-weight: 800; background: linear-gradient(135deg, #054d9e 0%, #18a999 100%); border-radius: 14px;"><?= $initials ?></div>
            <?php endif; ?>
            <div class="admin-avatar-overlay">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>
                <span>Change</span>
            </div>
        </div>
        
        <form id="adminAvatarForm" action="index.php?page=save-profile-picture" method="post" enctype="multipart/form-data" class="d-none">
            <input type="hidden" name="_csrf" value="<?= Security::e(Security::csrfToken()) ?>">
            <input type="file" name="profile_picture" id="adminAvatarFileInput" accept="image/*" onchange="document.getElementById('adminAvatarForm').submit()">
        </form>

        <!-- Right: Administrator Information (Perfectly Centralized Vertically) -->
        <div class="admin-profile-info flex-grow-1 d-flex flex-column justify-content-center">
            <div class="d-flex align-items-center flex-wrap gap-2 mb-1.5">
                <h1 class="h3 font-weight-bold mb-0 text-dark"><?= Security::e($user['name']) ?></h1>
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1 fw-semibold d-inline-flex align-items-center gap-1">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                    Administrator
                </span>
                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1 fw-semibold d-inline-flex align-items-center gap-1">
                    <span class="status-pulse-dot"></span> Active
                </span>
            </div>

            <div class="d-flex align-items-center flex-wrap gap-3 text-muted small mb-2.5">
                <span class="d-inline-flex align-items-center gap-1">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                    <?= Security::e($user['email']) ?>
                </span>
                <span class="d-inline-flex align-items-center gap-1">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="16" rx="2"/><line x1="7" y1="8" x2="17" y2="8"/><line x1="7" y1="12" x2="13" y2="12"/></svg>
                    ID: <strong><?= Security::e($employeeId) ?></strong>
                </span>
                <span class="d-inline-flex align-items-center gap-1">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    Last Login: <strong><?= Security::e($lastLoginText) ?></strong>
                </span>
            </div>

            <div class="d-flex align-items-center gap-2 flex-wrap">
                <button class="btn btn-primary btn-sm px-3 d-inline-flex align-items-center gap-1.5 shadow-xs" data-bs-toggle="modal" data-bs-target="#editProfileModal">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    Edit Profile
                </button>
                <a href="#tab-security" class="btn btn-outline-secondary btn-sm px-3 d-inline-flex align-items-center gap-1.5" onclick="bootstrap.Tab.getOrCreateInstance(document.querySelector('#security-tab')).show()">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    Security
                </a>
            </div>
        </div>

    </div>

    <!-- ══ Quick Statistics Row ══ -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-4 col-xl">
            <div class="admin-stat-card">
                <div class="stat-icon bg-primary-subtle text-primary">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                </div>
                <div>
                    <div class="stat-value"><?= (int)($stats['total_courses'] ?? 0) ?></div>
                    <div class="stat-label">Courses Managed</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl">
            <div class="admin-stat-card">
                <div class="stat-icon bg-success-subtle text-success">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                </div>
                <div>
                    <div class="stat-value"><?= (int)($stats['total_participants'] ?? 0) ?></div>
                    <div class="stat-label">Total Participants</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl">
            <div class="admin-stat-card">
                <div class="stat-icon bg-warning-subtle text-warning">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="7"/><polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"/></svg>
                </div>
                <div>
                    <div class="stat-value"><?= (int)($stats['total_certificates'] ?? 0) ?></div>
                    <div class="stat-label">Certificates Issued</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-6 col-xl">
            <div class="admin-stat-card">
                <div class="stat-icon bg-info-subtle text-info">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
                </div>
                <div>
                    <div class="stat-value"><?= (int)($stats['active_courses'] ?? 0) ?></div>
                    <div class="stat-label">Active Sessions</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl">
            <div class="admin-stat-card">
                <div class="stat-icon bg-danger-subtle text-danger">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><rect x="8" y="2" width="8" height="4" rx="1" ry="1"/><path d="M9 14l2 2 4-4"/></svg>
                </div>
                <div>
                    <div class="stat-value"><?= (int)($stats['pending_enrolments'] ?? 0) ?></div>
                    <div class="stat-label">Pending Enrolments</div>
                </div>
            </div>
        </div>
    </div>

    <!-- ══ Profile Navigation Tabs ══ -->
    <ul class="nav nav-tabs admin-profile-tabs mb-4" id="profileTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active d-inline-flex align-items-center gap-2" id="overview-tab" data-bs-toggle="tab" data-bs-target="#tab-overview" type="button" role="tab">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                Personal Details
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link d-inline-flex align-items-center gap-2" id="security-tab" data-bs-toggle="tab" data-bs-target="#tab-security" type="button" role="tab">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                Account Settings & Security
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link d-inline-flex align-items-center gap-2" id="preferences-tab" data-bs-toggle="tab" data-bs-target="#tab-preferences" type="button" role="tab">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                Preferences
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link d-inline-flex align-items-center gap-2" id="activity-tab" data-bs-toggle="tab" data-bs-target="#tab-activity" type="button" role="tab">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                Activity & Audit Log
            </button>
        </li>
    </ul>

    <!-- ══ Tab Contents ══ -->
    <div class="tab-content" id="profileTabContent">

        <!-- TAB 1: PERSONAL INFORMATION -->
        <div class="tab-pane fade show active" id="tab-overview" role="tabpanel">
            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="panel h-100">
                        <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-3">
                            <div>
                                <h2 class="h5 mb-1">Personal Information</h2>
                                <p class="text-muted small mb-0">Administrator identification and organization details.</p>
                            </div>
                            <button class="btn btn-outline-primary btn-sm d-inline-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#editProfileModal">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                Edit Information
                            </button>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="info-field-card p-3 rounded-3 border bg-surface h-100 d-flex align-items-start gap-3">
                                    <div class="field-icon-box bg-primary-subtle text-primary rounded-2 p-2.5 flex-shrink-0">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                    </div>
                                    <div class="flex-grow-1 overflow-hidden">
                                        <span class="text-uppercase text-muted fw-bold d-block mb-1" style="font-size: 0.725rem; letter-spacing: 0.05em;">Full Name</span>
                                        <span class="text-dark fw-semibold text-truncate d-block fs-6"><?= Security::e($user['name']) ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-field-card p-3 rounded-3 border bg-surface h-100 d-flex align-items-start gap-3">
                                    <div class="field-icon-box bg-info-subtle text-info rounded-2 p-2.5 flex-shrink-0">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                                    </div>
                                    <div class="flex-grow-1 overflow-hidden">
                                        <span class="text-uppercase text-muted fw-bold d-block mb-1" style="font-size: 0.725rem; letter-spacing: 0.05em;">Username / Account</span>
                                        <span class="text-dark fw-semibold text-truncate d-block fs-6"><?= Security::e($user['email']) ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-field-card p-3 rounded-3 border bg-surface h-100 d-flex align-items-start gap-3">
                                    <div class="field-icon-box bg-primary-subtle text-primary rounded-2 p-2.5 flex-shrink-0">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                                    </div>
                                    <div class="flex-grow-1 overflow-hidden">
                                        <span class="text-uppercase text-muted fw-bold d-block mb-1" style="font-size: 0.725rem; letter-spacing: 0.05em;">Email Address</span>
                                        <span class="text-dark fw-semibold text-truncate d-block fs-6"><?= Security::e($user['email']) ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-field-card p-3 rounded-3 border bg-surface h-100 d-flex align-items-start gap-3">
                                    <div class="field-icon-box bg-success-subtle text-success rounded-2 p-2.5 flex-shrink-0">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                                    </div>
                                    <div class="flex-grow-1 overflow-hidden">
                                        <span class="text-uppercase text-muted fw-bold d-block mb-1" style="font-size: 0.725rem; letter-spacing: 0.05em;">Phone Number</span>
                                        <span class="text-dark fw-semibold text-truncate d-block fs-6"><?= Security::e($user['phone'] ?? '+60 82-200 001') ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-field-card p-3 rounded-3 border bg-surface h-100 d-flex align-items-start gap-3">
                                    <div class="field-icon-box bg-warning-subtle text-warning rounded-2 p-2.5 flex-shrink-0">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                                    </div>
                                    <div class="flex-grow-1 overflow-hidden">
                                        <span class="text-uppercase text-muted fw-bold d-block mb-1" style="font-size: 0.725rem; letter-spacing: 0.05em;">Position / Role Title</span>
                                        <span class="text-primary fw-semibold text-truncate d-block fs-6"><?= Security::e($position) ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-field-card p-3 rounded-3 border bg-surface h-100 d-flex align-items-start gap-3">
                                    <div class="field-icon-box bg-secondary-subtle text-secondary rounded-2 p-2.5 flex-shrink-0">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="4" y="2" width="16" height="20" rx="2" ry="2"/><line x1="9" y1="6" x2="9.01" y2="6"/><line x1="15" y1="6" x2="15.01" y2="6"/><line x1="9" y1="10" x2="9.01" y2="10"/><line x1="15" y1="10" x2="15.01" y2="10"/><line x1="9" y1="14" x2="9.01" y2="14"/><line x1="15" y1="14" x2="15.01" y2="14"/></svg>
                                    </div>
                                    <div class="flex-grow-1 overflow-hidden">
                                        <span class="text-uppercase text-muted fw-bold d-block mb-1" style="font-size: 0.725rem; letter-spacing: 0.05em;">Department</span>
                                        <span class="text-dark fw-semibold text-truncate d-block fs-6"><?= Security::e($department) ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-field-card p-3 rounded-3 border bg-surface h-100 d-flex align-items-start gap-3">
                                    <div class="field-icon-box bg-danger-subtle text-danger rounded-2 p-2.5 flex-shrink-0">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                                    </div>
                                    <div class="flex-grow-1 overflow-hidden">
                                        <span class="text-uppercase text-muted fw-bold d-block mb-1" style="font-size: 0.725rem; letter-spacing: 0.05em;">Office Location</span>
                                        <span class="text-dark fw-semibold text-truncate d-block fs-6"><?= Security::e($officeLocation) ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-field-card p-3 rounded-3 border bg-surface h-100 d-flex align-items-start gap-3">
                                    <div class="field-icon-box bg-info-subtle text-info rounded-2 p-2.5 flex-shrink-0">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                                    </div>
                                    <div class="flex-grow-1 overflow-hidden">
                                        <span class="text-uppercase text-muted fw-bold d-block mb-1" style="font-size: 0.725rem; letter-spacing: 0.05em;">Time Zone</span>
                                        <span class="text-dark fw-semibold text-truncate d-block fs-6"><?= Security::e($user['time_zone'] ?? 'Asia/Kuala_Lumpur (UTC+08:00)') ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="info-field-card p-3 rounded-3 border bg-surface d-flex align-items-start gap-3">
                                    <div class="field-icon-box bg-primary-subtle text-primary rounded-2 p-2.5 flex-shrink-0">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                                    </div>
                                    <div class="flex-grow-1 overflow-hidden">
                                        <span class="text-uppercase text-muted fw-bold d-block mb-1" style="font-size: 0.725rem; letter-spacing: 0.05em;">Physical Address</span>
                                        <span class="text-dark fw-semibold d-block fs-6"><?= Security::e($user['address'] ?? 'Jalan Canna, Off Jalan Wan Alwi, 93350 Kuching, Sarawak') ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="panel mb-4">
                        <h2 class="h5 mb-3">Profile Completion</h2>
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="small font-weight-bold">Administrator Record</span>
                            <span class="badge bg-success">100% Complete</span>
                        </div>
                        <div class="progress mb-3" style="height: 8px;">
                            <div class="progress-bar bg-success" role="progressbar" style="width: 100%"></div>
                        </div>
                        <p class="small text-muted mb-0">Your administrative profile is fully configured and verified against CENTEXS Directory.</p>
                    </div>

                    <div class="panel">
                        <h2 class="h5 mb-3">Quick Administrative Shortcuts</h2>
                        <div class="d-flex flex-column gap-2">
                            <a href="index.php?page=admin-users" class="btn btn-outline-light text-start text-dark d-flex align-items-center justify-content-between p-2.5 border">
                                <span class="d-inline-flex align-items-center gap-2">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-primary"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                                    User Management
                                </span>
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
                            </a>
                            <a href="index.php?page=admin-courses" class="btn btn-outline-light text-start text-dark d-flex align-items-center justify-content-between p-2.5 border">
                                <span class="d-inline-flex align-items-center gap-2">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-primary"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                                    Course Management
                                </span>
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
                            </a>
                            <a href="index.php?page=admin-system-settings" class="btn btn-outline-light text-start text-dark d-flex align-items-center justify-content-between p-2.5 border">
                                <span class="d-inline-flex align-items-center gap-2">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-primary"><rect x="2" y="2" width="20" height="8" rx="2" ry="2"/><rect x="2" y="14" width="20" height="8" rx="2" ry="2"/></svg>
                                    System Settings
                                </span>
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- TAB 2: SECURITY & SESSION MANAGEMENT -->
        <div class="tab-pane fade" id="tab-security" role="tabpanel">
            <div class="row g-4" id="security-section">
                <!-- Security Health Summary -->
                <div class="col-12">
                    <div class="panel bg-gradient-navy text-white p-4">
                        <div class="row align-items-center g-3">
                            <div class="col-md-8">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="security-shield-icon">
                                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><polyline points="9 11 12 14 22 4"/></svg>
                                    </div>
                                    <div>
                                        <h3 class="h5 mb-1 text-white">Administrator Security Status</h3>
                                        <p class="small text-white-50 mb-0">Your account is secured with password protection, active session logging, and IP tracking.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 text-md-end">
                                <span class="badge bg-success px-3 py-2 fs-6">High Security Score</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Password Change Form -->
                <div class="col-lg-6">
                    <div class="panel h-100">
                        <h2 class="h5 mb-3">Change Password</h2>
                        <form action="index.php?page=admin-change-password" method="post" id="changePasswordForm">
                            <input type="hidden" name="_csrf" value="<?= Security::e(Security::csrfToken()) ?>">
                            <div class="mb-3">
                                <label class="form-label font-weight-bold">Current Password</label>
                                <input type="password" name="current_password" class="form-control" required placeholder="Enter current password">
                            </div>
                            <div class="mb-3">
                                <label class="form-label font-weight-bold">New Password</label>
                                <input type="password" name="new_password" id="newPasswordInput" class="form-control" required placeholder="Min 8 characters" oninput="checkPasswordStrength(this.value)">
                                <div class="password-strength-bar mt-2">
                                    <div class="strength-bar-fill" id="strengthBarFill"></div>
                                </div>
                                <span class="small text-muted mt-1 d-block" id="strengthText">Password Strength: Enter password</span>
                            </div>
                            <div class="mb-3">
                                <label class="form-label font-weight-bold">Confirm New Password</label>
                                <input type="password" name="confirm_password" class="form-control" required placeholder="Re-enter new password">
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Update Password</button>
                        </form>
                    </div>
                </div>

                <!-- Security Details & Email Update -->
                <div class="col-lg-6">
                    <div class="panel h-100 d-flex flex-direction-column justify-content-between">
                        <div>
                            <h2 class="h5 mb-3">Security Information & Email</h2>
                            
                            <div class="profile-field-item mb-3">
                                <span class="field-label">Last Password Changed</span>
                                <span class="field-value fw-medium"><?= Security::e($passwordChangedAt) ?></span>
                            </div>

                            <div class="profile-field-item mb-3">
                                <span class="field-label">Failed Login Attempts (Last 30 Days)</span>
                                <span class="field-value fw-semibold text-success"><?= (int)($stats['failed_logins_count'] ?? 0) ?> failed attempts</span>
                            </div>

                            <div class="profile-field-item mb-3">
                                <span class="field-label">Two-Factor Authentication (2FA)</span>
                                <div class="d-flex align-items-center justify-content-between mt-1">
                                    <span class="badge bg-secondary-subtle text-secondary border px-2.5 py-1">Disabled (Future-Ready)</span>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" disabled id="2faSwitch">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Update Email Sub-form -->
                        <div class="border-top pt-3 mt-3">
                            <h3 class="h6 mb-2">Update Email Address</h3>
                            <form action="index.php?page=admin-update-email" method="post">
                                <input type="hidden" name="_csrf" value="<?= Security::e(Security::csrfToken()) ?>">
                                <div class="input-group">
                                    <input type="email" name="email" class="form-control" value="<?= Security::e($user['email']) ?>" required>
                                    <button type="submit" class="btn btn-outline-primary">Update Email</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Active Session Management -->
                <div class="col-12">
                    <div class="panel">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div>
                                <h2 class="h5 mb-1">Active Sessions & Remembered Devices</h2>
                                <p class="text-muted small mb-0">Devices currently logged into this administrator account.</p>
                            </div>
                            <form action="index.php?page=admin-logout-all-devices" method="post" onsubmit="return confirm('Log out from all other devices?')">
                                <input type="hidden" name="_csrf" value="<?= Security::e(Security::csrfToken()) ?>">
                                <button type="submit" class="btn btn-outline-danger btn-sm">Log Out All Other Devices</button>
                            </form>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Device / Browser</th>
                                        <th>IP Address</th>
                                        <th>Last Active</th>
                                        <th>Status</th>
                                        <th class="text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($active_sessions)): ?>
                                        <?php foreach ($active_sessions as $sess): ?>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center gap-2">
                                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-primary"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
                                                        <span><?= Security::e($sess['device_label'] ?? $sess['browser'] ?? 'Web Browser') ?></span>
                                                    </div>
                                                </td>
                                                <td><code><?= Security::e($sess['ip_address'] ?? '127.0.0.1') ?></code></td>
                                                <td><?= Security::e(date('M d, Y H:i', strtotime($sess['last_active']))) ?></td>
                                                <td>
                                                    <span class="badge bg-success-subtle text-success">Active Now</span>
                                                </td>
                                                <td class="text-end">
                                                    <form action="index.php?page=admin-revoke-session" method="post" class="d-inline">
                                                        <input type="hidden" name="_csrf" value="<?= Security::e(Security::csrfToken()) ?>">
                                                        <input type="hidden" name="session_id" value="<?= (int)$sess['id'] ?>">
                                                        <button type="submit" class="btn btn-link text-danger p-0 small">Revoke</button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-primary"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
                                                    <span>Current Workspace Desktop (Windows)</span>
                                                </div>
                                            </td>
                                            <td><code><?= Security::e($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1') ?></code></td>
                                            <td><?= date('M d, Y H:i') ?></td>
                                            <td><span class="badge bg-success-subtle text-success">Current Session</span></td>
                                            <td class="text-end"><span class="text-muted small">Active</span></td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Recent Login History -->
                <div class="col-12">
                    <div class="panel">
                        <h2 class="h5 mb-3">Login History Log</h2>
                        <div class="table-responsive">
                            <table class="table table-sm table-striped mb-0">
                                <thead>
                                    <tr>
                                        <th>Timestamp</th>
                                        <th>Status</th>
                                        <th>IP Address</th>
                                        <th>User Agent / Device</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($login_history)): ?>
                                        <?php foreach ($login_history as $log): ?>
                                            <tr>
                                                <td><?= Security::e($log['created_at']) ?></td>
                                                <td>
                                                    <span class="badge bg-<?= $log['status'] === 'success' ? 'success' : 'danger' ?>">
                                                        <?= Security::e(strtoupper($log['status'])) ?>
                                                    </span>
                                                </td>
                                                <td><code><?= Security::e($log['ip_address'] ?? '-') ?></code></td>
                                                <td class="small text-muted"><?= Security::e($log['user_agent'] ?? 'Browser') ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="4" class="text-muted text-center py-3">No recent login records.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- TAB 3: PREFERENCES & PERSONALIZATION -->
        <div class="tab-pane fade" id="tab-preferences" role="tabpanel">
            <div class="panel">
                <h2 class="h5 mb-3 border-bottom pb-3">Administrator System Preferences</h2>
                
                <form action="index.php?page=admin-save-preferences" method="post">
                    <input type="hidden" name="_csrf" value="<?= Security::e(Security::csrfToken()) ?>">

                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="form-label font-weight-bold">Interface Theme</label>
                            <div class="d-flex gap-3">
                                <div class="theme-option-card <?= $themePref === 'light' ? 'selected' : '' ?>" onclick="selectTheme('light')">
                                    <div class="theme-preview theme-preview-light"></div>
                                    <div class="d-flex align-items-center gap-2 mt-2">
                                        <input type="radio" name="theme_preference" id="themeLight" value="light" <?= $themePref === 'light' ? 'checked' : '' ?>>
                                        <label for="themeLight" class="mb-0 font-weight-bold">Light Mode</label>
                                    </div>
                                </div>
                                <div class="theme-option-card <?= $themePref === 'dark' ? 'selected' : '' ?>" onclick="selectTheme('dark')">
                                    <div class="theme-preview theme-preview-dark"></div>
                                    <div class="d-flex align-items-center gap-2 mt-2">
                                        <input type="radio" name="theme_preference" id="themeDark" value="dark" <?= $themePref === 'dark' ? 'checked' : '' ?>>
                                        <label for="themeDark" class="mb-0 font-weight-bold">Dark Mode</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label font-weight-bold">System Language</label>
                                <select name="language" class="form-select">
                                    <option value="en" selected>English (United States)</option>
                                    <option value="ms">Bahasa Melayu (Malaysia)</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label font-weight-bold">Default Landing Page</label>
                                <select name="default_landing_page" class="form-select">
                                    <option value="dashboard" <?= $landingPref === 'dashboard' ? 'selected' : '' ?>>Admin Dashboard</option>
                                    <option value="admin-users" <?= $landingPref === 'admin-users' ? 'selected' : '' ?>>User Management</option>
                                    <option value="admin-courses" <?= $landingPref === 'admin-courses' ? 'selected' : '' ?>>Course Management</option>
                                    <option value="profile" <?= $landingPref === 'profile' ? 'selected' : '' ?>>Admin Profile</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-12">
                            <h3 class="h6 font-weight-bold mb-3 border-top pt-3">Notification Preferences</h3>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <div class="notif-pref-card border rounded-3 p-3 d-flex align-items-center justify-content-between gap-3 shadow-xs">
                                        <div>
                                            <label class="fw-bold text-dark mb-0 d-block cursor-pointer" for="notifEmail">Email Notifications</label>
                                            <span class="text-muted small d-block mt-0.5">Receive system alerts via email</span>
                                        </div>
                                        <div class="form-check form-switch m-0 p-0 fs-5 flex-shrink-0">
                                            <input class="form-check-input ms-0 me-0" type="checkbox" name="notif_email" id="notifEmail" value="1" <?= $emailAlerts ? 'checked' : '' ?> style="cursor: pointer;">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="notif-pref-card border rounded-3 p-3 d-flex align-items-center justify-content-between gap-3 shadow-xs">
                                        <div>
                                            <label class="fw-bold text-dark mb-0 d-block cursor-pointer" for="notifSecurity">Security Alerts</label>
                                            <span class="text-muted small d-block mt-0.5">Receive urgent security notifications</span>
                                        </div>
                                        <div class="form-check form-switch m-0 p-0 fs-5 flex-shrink-0">
                                            <input class="form-check-input ms-0 me-0" type="checkbox" name="notif_security" id="notifSecurity" value="1" <?= $securityAlerts ? 'checked' : '' ?> style="cursor: pointer;">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="notif-pref-card border rounded-3 p-3 d-flex align-items-center justify-content-between gap-3 shadow-xs">
                                        <div>
                                            <label class="fw-bold text-dark mb-0 d-block cursor-pointer" for="notifAnnouncements">System Updates</label>
                                            <span class="text-muted small d-block mt-0.5">Receive system maintenance news</span>
                                        </div>
                                        <div class="form-check form-switch m-0 p-0 fs-5 flex-shrink-0">
                                            <input class="form-check-input ms-0 me-0" type="checkbox" name="notif_announcements" id="notifAnnouncements" value="1" <?= $systemAnnouncements ? 'checked' : '' ?> style="cursor: pointer;">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 mt-4 text-end">
                            <button type="submit" class="btn btn-primary px-4">Save Preferences</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- TAB 4: ACTIVITY & AUDIT LOG -->
        <div class="tab-pane fade" id="tab-activity" role="tabpanel">
            <div class="panel">
                <h2 class="h5 mb-3">Recent Administrative Activity Timeline</h2>
                
                <?php if (!empty($activity_logs)): ?>
                    <div class="admin-activity-timeline">
                        <?php foreach ($activity_logs as $act): ?>
                            <div class="timeline-item">
                                <div class="timeline-icon bg-primary-subtle text-primary">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="12 8 12 12 14 14"/><circle cx="12" cy="12" r="10"/></svg>
                                </div>
                                <div class="timeline-content">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <span class="fw-semibold text-dark"><?= Security::e($act['action']) ?></span>
                                        <span class="small text-muted"><?= Security::e($act['created_at']) ?></span>
                                    </div>
                                    <div class="small text-muted mt-1">
                                        IP: <code><?= Security::e($act['ip_address'] ?? 'Internal') ?></code>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-muted">No recent activity logs recorded for this administrator.</p>
                <?php endif; ?>
            </div>
        </div>

    </div>

    <!-- ══ Destructive Actions Card (Bottom Danger Zone) ══ -->
    <div class="panel panel-danger-zone mt-4 border-danger-subtle bg-danger-subtle-light">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div>
                <h2 class="h6 text-danger mb-1 font-weight-bold d-flex align-items-center gap-2">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                    Account Actions & Security Control
                </h2>
                <p class="small text-muted mb-0">Sensitive account management actions. Proceed with caution.</p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <form action="index.php?page=admin-logout-all-devices" method="post" class="d-inline">
                    <input type="hidden" name="_csrf" value="<?= Security::e(Security::csrfToken()) ?>">
                    <button type="submit" class="btn btn-outline-danger btn-sm">Log Out All Devices</button>
                </form>
                <button class="btn btn-outline-secondary btn-sm" disabled title="Future Feature">Disable Account</button>
                <button class="btn btn-danger btn-sm" disabled title="Restricted Action">Delete Account</button>
            </div>
        </div>
    </div>

</div>

<!-- ══ Edit Profile Modal ══ -->
<div class="modal fade" id="editProfileModal" tabindex="-1" aria-labelledby="editProfileModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title font-weight-bold" id="editProfileModalLabel">Quick Edit Admin Profile</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="index.php?page=admin-update-profile" method="post">
                <input type="hidden" name="_csrf" value="<?= Security::e(Security::csrfToken()) ?>">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label font-weight-bold">Full Name</label>
                        <input type="text" name="name" class="form-control" value="<?= Security::e($user['name']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label font-weight-bold">Phone Number</label>
                        <input type="text" name="phone" class="form-control" value="<?= Security::e($user['phone'] ?? '') ?>" placeholder="+60 82-200 001">
                    </div>
                    <div class="mb-3">
                        <label class="form-label font-weight-bold">Position / Title</label>
                        <input type="text" name="position" class="form-control" value="<?= Security::e($position) ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label font-weight-bold">Department</label>
                        <input type="text" name="department" class="form-control" value="<?= Security::e($department) ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label font-weight-bold">Office Location</label>
                        <input type="text" name="office_location" class="form-control" value="<?= Security::e($officeLocation) ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label font-weight-bold">Time Zone</label>
                        <select name="time_zone" class="form-select">
                            <option value="Asia/Kuala_Lumpur" selected>Asia/Kuala_Lumpur (UTC+08:00)</option>
                            <option value="Asia/Singapore">Asia/Singapore (UTC+08:00)</option>
                            <option value="UTC">UTC (Coordinated Universal Time)</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Relocate modal to body to prevent container stacking context issues
    var editModal = document.getElementById('editProfileModal');
    if (editModal && editModal.parentElement !== document.body) {
        document.body.appendChild(editModal);
    }
});

function checkPasswordStrength(password) {
    let score = 0;
    if (password.length >= 8) score += 25;
    if (/[A-Z]/.test(password)) score += 25;
    if (/[0-9]/.test(password)) score += 25;
    if (/[^A-Za-z0-9]/.test(password)) score += 25;

    const fill = document.getElementById('strengthBarFill');
    const text = document.getElementById('strengthText');
    fill.style.width = score + '%';

    if (score < 50) {
        fill.style.backgroundColor = '#dc2626';
        text.innerText = 'Password Strength: Weak';
        text.className = 'small text-danger mt-1 d-block';
    } else if (score < 75) {
        fill.style.backgroundColor = '#ea580c';
        text.innerText = 'Password Strength: Moderate';
        text.className = 'small text-warning mt-1 d-block';
    } else {
        fill.style.backgroundColor = '#16a34a';
        text.innerText = 'Password Strength: Strong';
        text.className = 'small text-success mt-1 d-block';
    }
}

function selectTheme(theme) {
    document.querySelectorAll('.theme-option-card').forEach(el => el.classList.remove('selected'));
    if (theme === 'light') {
        document.getElementById('themeLight').checked = true;
        document.querySelector('.theme-option-card:first-child').classList.add('selected');
        document.documentElement.setAttribute('data-theme', 'light');
    } else {
        document.getElementById('themeDark').checked = true;
        document.querySelector('.theme-option-card:last-child').classList.add('selected');
        document.documentElement.setAttribute('data-theme', 'dark');
    }
}
</script>
