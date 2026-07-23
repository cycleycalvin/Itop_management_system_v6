<?php
use App\Core\Auth;
use App\Core\Security;
use App\Core\View;

$initials = strtoupper(substr($user['name'] ?? 'Instructor', 0, 1));
$employeeId = $user['employee_id'] ?? 'INS-' . str_pad((string)$user['id'], 4, '0', STR_PAD_LEFT);
$phone = $user['phone'] ?? '+60 82-200 002';
$address = $user['address'] ?? 'CENTEXS Main Campus, Jalan Canna, Kuching';
$department = $user['department'] ?? 'Technical & Vocational Education';
$position = $user['position'] ?? 'Senior Instructor';
$officeLocation = $user['office_location'] ?? 'Academic Building 2, Level 3';
$timeZone = $user['time_zone'] ?? 'Asia/Kuala_Lumpur';
?>

<div class="instructor-profile-container animate-in">

    <?php View::partial('partials/role-nav'); ?>

    <!-- Alert Messages -->
    <?php if (!empty($_GET['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show mb-4 shadow-sm" role="alert">
            <div class="d-flex align-items-center gap-2">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                <span><?= Security::e($_GET['success']) ?></span>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- ══ Centralized Instructor Header Card ══ -->
    <div class="admin-profile-header-card mb-4 p-4 d-flex align-items-center flex-wrap flex-md-nowrap gap-4">
        
        <!-- Left: Framed Avatar Window -->
        <div class="admin-profile-avatar-wrapper flex-shrink-0" onclick="document.getElementById('instructorAvatarFileInput').click()" title="Click to change profile picture" style="width: 120px; height: 120px; min-width: 120px; min-height: 120px; max-width: 120px; max-height: 120px; border-radius: 20px; border: 4px solid #ffffff; outline: 2px solid #cbd5e1; box-shadow: 0 8px 20px rgba(0,0,0,0.12); position: relative; overflow: hidden; cursor: pointer; margin: 0; background: #ffffff;">
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

        <form id="instructorAvatarForm" action="index.php?page=save-profile-picture" method="post" enctype="multipart/form-data" class="d-none">
            <input type="hidden" name="_csrf" value="<?= Security::e(Security::csrfToken()) ?>">
            <input type="file" name="profile_picture" id="instructorAvatarFileInput" accept="image/*" onchange="document.getElementById('instructorAvatarForm').submit()">
        </form>

        <!-- Right: Instructor Information (Side-by-Side Centralized) -->
        <div class="admin-profile-info flex-grow-1 d-flex flex-column justify-content-center">
            <div class="d-flex align-items-center flex-wrap gap-2 mb-1.5">
                <h1 class="h3 font-weight-bold mb-0 text-dark"><?= Security::e($user['name']) ?></h1>
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1 fw-semibold d-inline-flex align-items-center gap-1">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                    CENTEXS Instructor
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
                    Staff ID: <strong><?= Security::e($employeeId) ?></strong>
                </span>
                <span class="d-inline-flex align-items-center gap-1">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                    <?= Security::e($department) ?>
                </span>
            </div>

            <div class="d-flex align-items-center gap-2 flex-wrap">
                <button class="btn btn-primary btn-sm px-3 d-inline-flex align-items-center gap-1.5 shadow-xs" data-bs-toggle="modal" data-bs-target="#editInstructorModal">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    Edit Profile
                </button>
                <a href="#tab-security" class="btn btn-outline-secondary btn-sm px-3 d-inline-flex align-items-center gap-1.5" onclick="bootstrap.Tab.getOrCreateInstance(document.querySelector('#instructor-security-tab')).show()">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    Security
                </a>
            </div>
        </div>

    </div>

    <!-- ══ Instructor Quick Statistics Row ══ -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="admin-stat-card">
                <div class="stat-icon bg-primary-subtle text-primary">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                </div>
                <div>
                    <div class="stat-value"><?= (int)($stats['assigned_courses'] ?? 0) ?></div>
                    <div class="stat-label">Courses Taught</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="admin-stat-card">
                <div class="stat-icon bg-success-subtle text-success">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                </div>
                <div>
                    <div class="stat-value"><?= (int)($stats['total_students'] ?? 0) ?></div>
                    <div class="stat-label">Total Trainees</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="admin-stat-card">
                <div class="stat-icon bg-warning-subtle text-warning">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="7"/><polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"/></svg>
                </div>
                <div>
                    <div class="stat-value"><?= (int)($stats['certificates_issued'] ?? 0) ?></div>
                    <div class="stat-label">Certificates Issued</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="admin-stat-card">
                <div class="stat-icon bg-info-subtle text-info">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                </div>
                <div>
                    <div class="stat-value"><?= number_format((float)($stats['avg_rating'] ?? 4.9), 1) ?> / 5.0</div>
                    <div class="stat-label">Instructor Rating</div>
                </div>
            </div>
        </div>
    </div>

    <!-- ══ Instructor Profile Tabs ══ -->
    <ul class="nav nav-tabs admin-profile-tabs mb-4" id="instructorProfileTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active d-inline-flex align-items-center gap-2" id="instructor-details-tab" data-bs-toggle="tab" data-bs-target="#tab-instructor-details" type="button" role="tab">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                Personal & Professional Details
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link d-inline-flex align-items-center gap-2" id="instructor-reports-tab" data-bs-toggle="tab" data-bs-target="#tab-instructor-reports" type="button" role="tab">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                Teaching Reports
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link d-inline-flex align-items-center gap-2" id="instructor-security-tab" data-bs-toggle="tab" data-bs-target="#tab-instructor-security" type="button" role="tab">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                Account Settings & Security
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link d-inline-flex align-items-center gap-2" id="instructor-preferences-tab" data-bs-toggle="tab" data-bs-target="#tab-instructor-preferences" type="button" role="tab">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                Preferences
            </button>
        </li>
    </ul>

    <!-- ══ Tab Contents ══ -->
    <div class="tab-content" id="instructorProfileTabContent">

        <!-- TAB 1: PERSONAL & PROFESSIONAL DETAILS -->
        <div class="tab-pane fade show active" id="tab-instructor-details" role="tabpanel">
            <div class="panel">
                <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-3">
                    <div>
                        <h2 class="h5 mb-1">Instructor Profile Details</h2>
                        <p class="text-muted small mb-0">Professional credentials, department assignment, and contact information.</p>
                    </div>
                    <button class="btn btn-outline-primary btn-sm d-inline-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#editInstructorModal">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                        Edit Details
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
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="16" rx="2"/><line x1="7" y1="8" x2="17" y2="8"/><line x1="7" y1="12" x2="13" y2="12"/></svg>
                            </div>
                            <div class="flex-grow-1 overflow-hidden">
                                <span class="text-uppercase text-muted fw-bold d-block mb-1" style="font-size: 0.725rem; letter-spacing: 0.05em;">Staff / Employee ID</span>
                                <span class="text-dark fw-semibold text-truncate d-block fs-6"><?= Security::e($employeeId) ?></span>
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
                                <span class="text-uppercase text-muted fw-bold d-block mb-1" style="font-size: 0.725rem; letter-spacing: 0.05em;">Contact Phone</span>
                                <span class="text-dark fw-semibold text-truncate d-block fs-6"><?= Security::e($phone) ?></span>
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
                                <span class="text-dark fw-semibold text-truncate d-block fs-6"><?= Security::e($timeZone) ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="info-field-card p-3 rounded-3 border bg-surface d-flex align-items-start gap-3">
                            <div class="field-icon-box bg-primary-subtle text-primary rounded-2 p-2.5 flex-shrink-0">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                            </div>
                            <div class="flex-grow-1 overflow-hidden">
                                <span class="text-uppercase text-muted fw-bold d-block mb-1" style="font-size: 0.725rem; letter-spacing: 0.05em;">Campus / Office Address</span>
                                <span class="text-dark fw-semibold d-block fs-6"><?= Security::e($address) ?></span>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <!-- TAB 2: INSTRUCTOR RELEVANT TEACHING REPORTS -->
        <div class="tab-pane fade" id="tab-instructor-reports" role="tabpanel">
            <div class="panel">
                <div class="d-flex align-items-center justify-content-between mb-4 border-bottom pb-3">
                    <div>
                        <h2 class="h5 mb-1">Instructor Teaching & Course Reports</h2>
                        <p class="text-muted small mb-0">Relevant performance reports, student course feedback, and class attendance statistics.</p>
                    </div>
                </div>

                <div class="row g-4">
                    <!-- Teaching Course Performance Report -->
                    <div class="col-md-6">
                        <div class="border rounded-3 p-4 bg-surface h-100 shadow-xs">
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <div class="p-2.5 bg-primary-subtle text-primary rounded-3">
                                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                                </div>
                                <div>
                                    <h3 class="h6 font-weight-bold mb-0 text-dark">Course Teaching Report</h3>
                                    <span class="small text-muted">Assigned courses, active enrolments & completion rates</span>
                                </div>
                            </div>
                            <p class="small text-muted mb-3">View class performance, student enrolment numbers, and course progress for all your assigned training modules.</p>
                            <a href="index.php?page=instructor-dashboard" class="btn btn-outline-primary btn-sm px-3">View Course Reports →</a>
                        </div>
                    </div>

                    <!-- Student Evaluation & Feedback Report -->
                    <div class="col-md-6">
                        <div class="border rounded-3 p-4 bg-surface h-100 shadow-xs">
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <div class="p-2.5 bg-success-subtle text-success rounded-3">
                                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                                </div>
                                <div>
                                    <h3 class="h6 font-weight-bold mb-0 text-dark">Student Evaluation & Rating Report</h3>
                                    <span class="small text-muted">Feedback & rating scores submitted by trainees</span>
                                </div>
                            </div>
                            <p class="small text-muted mb-3">Review anonymous student evaluation ratings and course feedback to measure teaching satisfaction.</p>
                            <a href="index.php?page=reports" class="btn btn-outline-success btn-sm px-3">View Student Feedback →</a>
                        </div>
                    </div>

                    <!-- Student Attendance Summary Report -->
                    <div class="col-md-6">
                        <div class="border rounded-3 p-4 bg-surface h-100 shadow-xs">
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <div class="p-2.5 bg-warning-subtle text-warning rounded-3">
                                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><rect x="8" y="2" width="8" height="4" rx="1" ry="1"/><path d="M9 14l2 2 4-4"/></svg>
                                </div>
                                <div>
                                    <h3 class="h6 font-weight-bold mb-0 text-dark">Class Attendance Report</h3>
                                    <span class="small text-muted">Daily student attendance & participation logs</span>
                                </div>
                            </div>
                            <p class="small text-muted mb-3">Audit daily attendance records for enrolled trainees across all your course cohorts.</p>
                            <a href="index.php?page=reports" class="btn btn-outline-warning btn-sm px-3">View Attendance Reports →</a>
                        </div>
                    </div>

                    <!-- Certificate Summary Report -->
                    <div class="col-md-6">
                        <div class="border rounded-3 p-4 bg-surface h-100 shadow-xs">
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <div class="p-2.5 bg-info-subtle text-info rounded-3">
                                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="7"/><polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"/></svg>
                                </div>
                                <div>
                                    <h3 class="h6 font-weight-bold mb-0 text-dark">Certificate Issuance Report</h3>
                                    <span class="small text-muted">Summary of certificates awarded to trainees</span>
                                </div>
                            </div>
                            <p class="small text-muted mb-3">Track completed trainees eligible for CENTEXS certificate issuance in your courses.</p>
                            <a href="index.php?page=reports" class="btn btn-outline-info btn-sm px-3">View Certificate Summary →</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- TAB 3: ACCOUNT SETTINGS & SECURITY -->
        <div class="tab-pane fade" id="tab-instructor-security" role="tabpanel">
            <div class="row g-4">
                <div class="col-lg-6">
                    <div class="panel h-100">
                        <h2 class="h5 mb-3 border-bottom pb-3">Change Account Password</h2>
                        <form action="index.php?page=admin-change-password" method="post">
                            <input type="hidden" name="_csrf" value="<?= Security::e(Security::csrfToken()) ?>">
                            
                            <div class="mb-3">
                                <label class="form-label font-weight-bold">Current Password</label>
                                <input type="password" name="current_password" class="form-control" required placeholder="Enter current password">
                            </div>

                            <div class="mb-3">
                                <label class="form-label font-weight-bold">New Password</label>
                                <input type="password" name="new_password" class="form-control" required placeholder="Enter new strong password" onkeyup="checkPasswordStrength(this.value)">
                                <div class="progress mt-2" style="height: 6px;">
                                    <div id="strengthBarFill" class="progress-bar bg-danger" style="width: 0%;"></div>
                                </div>
                                <span id="strengthText" class="small text-muted mt-1 d-block">Password Strength: Weak</span>
                            </div>

                            <div class="mb-4">
                                <label class="form-label font-weight-bold">Confirm New Password</label>
                                <input type="password" name="confirm_password" class="form-control" required placeholder="Re-enter new password">
                            </div>

                            <button type="submit" class="btn btn-primary px-4">Update Password</button>
                        </form>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="panel h-100">
                        <h2 class="h5 mb-3 border-bottom pb-3">Account Security Summary</h2>
                        <div class="d-flex flex-column gap-3">
                            <div class="p-3 border rounded-3 bg-surface">
                                <span class="text-uppercase text-muted fw-bold d-block mb-1" style="font-size: 0.725rem;">Official Email</span>
                                <span class="fw-semibold text-dark fs-6"><?= Security::e($user['email']) ?></span>
                            </div>
                            <div class="p-3 border rounded-3 bg-surface">
                                <span class="text-uppercase text-muted fw-bold d-block mb-1" style="font-size: 0.725rem;">Role Assignment</span>
                                <span class="fw-semibold text-dark fs-6">CENTEXS Instructor</span>
                            </div>
                            <div class="p-3 border rounded-3 bg-surface">
                                <span class="text-uppercase text-muted fw-bold d-block mb-1" style="font-size: 0.725rem;">Account Registration Date</span>
                                <span class="fw-semibold text-dark fs-6"><?= Security::e($user['created_at'] ?? 'N/A') ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- TAB 4: PREFERENCES -->
        <div class="tab-pane fade" id="tab-instructor-preferences" role="tabpanel">
            <div class="panel">
                <h2 class="h5 mb-3 border-bottom pb-3">System Preferences</h2>
                <form action="index.php?page=admin-save-preferences" method="post">
                    <input type="hidden" name="_csrf" value="<?= Security::e(Security::csrfToken()) ?>">

                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="form-label font-weight-bold">Interface Theme</label>
                            <div class="d-flex gap-3">
                                <div class="theme-option-card selected" onclick="selectTheme('light')">
                                    <div class="theme-preview theme-preview-light"></div>
                                    <div class="d-flex align-items-center gap-2 mt-2">
                                        <input type="radio" name="theme_preference" id="themeLight" value="light" checked>
                                        <label for="themeLight" class="mb-0 font-weight-bold">Light Mode</label>
                                    </div>
                                </div>
                                <div class="theme-option-card" onclick="selectTheme('dark')">
                                    <div class="theme-preview theme-preview-dark"></div>
                                    <div class="d-flex align-items-center gap-2 mt-2">
                                        <input type="radio" name="theme_preference" id="themeDark" value="dark">
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
                        </div>
                    </div>

                    <div class="mt-4 text-end">
                        <button type="submit" class="btn btn-primary px-4">Save Preferences</button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>

<!-- ══ Edit Instructor Profile Modal ══ -->
<div class="modal fade" id="editInstructorModal" tabindex="-1" aria-labelledby="editInstructorModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title font-weight-bold" id="editInstructorModalLabel">Edit Instructor Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="index.php?page=admin-update-profile" method="post">
                <input type="hidden" name="_csrf" value="<?= Security::e(Security::csrfToken()) ?>">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label font-weight-bold">Full Name</label>
                            <input type="text" name="name" class="form-control" value="<?= Security::e($user['name']) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label font-weight-bold">Contact Phone Number</label>
                            <input type="text" name="phone" class="form-control" value="<?= Security::e($phone) ?>" placeholder="+60 82-200 002">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label font-weight-bold">Position / Title</label>
                            <input type="text" name="position" class="form-control" value="<?= Security::e($position) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label font-weight-bold">Department</label>
                            <input type="text" name="department" class="form-control" value="<?= Security::e($department) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label font-weight-bold">Office Location</label>
                            <input type="text" name="office_location" class="form-control" value="<?= Security::e($officeLocation) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label font-weight-bold">Time Zone</label>
                            <input type="text" name="time_zone" class="form-control" value="<?= Security::e($timeZone) ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label font-weight-bold">Campus / Office Address</label>
                            <textarea name="address" class="form-control" rows="2"><?= Security::e($address) ?></textarea>
                        </div>
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
    var editM = document.getElementById('editInstructorModal');
    if (editM && editM.parentElement !== document.body) {
        document.body.appendChild(editM);
    }
});
</script>
