<?php
use App\Core\Auth;
use App\Core\Security;
use App\Core\View;

$initials = strtoupper(substr($user['name'] ?? 'Trainee', 0, 1));
$nric = $user['identity_number'] ?? $profile['identity_number'] ?? 'Not Specified';
$phone = $user['phone'] ?? $profile['phone'] ?? 'Not Specified';
$address = $user['address'] ?? $profile['address'] ?? 'Not Specified';
$education = $profile['education'] ?? 'Not Specified';
$employment = $profile['employment'] ?? 'Not Specified';
$emergency = $profile['emergency_contact'] ?? 'Not Specified';
$company = $user['institution_company'] ?? 'CENTEXS Trainee Program';
$gender = $user['gender'] ?? 'Not Specified';
$dob = $user['date_of_birth'] ?? 'Not Specified';
$timeZone = $user['time_zone'] ?? 'Asia/Kuala_Lumpur';

$isVerified = !empty($profile['is_verified']) && (int)$profile['is_verified'] === 1;
$verificationStatus = $profile['verification_status'] ?? ($isVerified ? 'Verified' : 'Pending Verification');
?>

<div class="trainee-profile-container animate-in">

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

    <?php if (!empty($_GET['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show mb-4 shadow-sm" role="alert">
            <div class="d-flex align-items-center gap-2">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                <span><?= Security::e($_GET['error']) ?></span>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- ══ Centralized Trainee Header Card ══ -->
    <div class="admin-profile-header-card mb-4 p-4 d-flex align-items-center flex-wrap flex-md-nowrap gap-4">
        
        <!-- Left: Framed Avatar Window -->
        <div class="admin-profile-avatar-wrapper flex-shrink-0" onclick="document.getElementById('traineeAvatarFileInput').click()" title="Click to change profile picture" style="width: 120px; height: 120px; min-width: 120px; min-height: 120px; max-width: 120px; max-height: 120px; border-radius: 20px; border: 4px solid #ffffff; outline: 2px solid #cbd5e1; box-shadow: 0 8px 20px rgba(0,0,0,0.12); position: relative; overflow: hidden; cursor: pointer; margin: 0; background: #ffffff;">
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

        <form id="traineeAvatarForm" action="index.php?page=save-profile-picture" method="post" enctype="multipart/form-data" class="d-none">
            <input type="hidden" name="_csrf" value="<?= Security::e(Security::csrfToken()) ?>">
            <input type="file" name="profile_picture" id="traineeAvatarFileInput" accept="image/*" onchange="document.getElementById('traineeAvatarForm').submit()">
        </form>

        <!-- Right: Trainee Information (Side-by-Side Centralized) -->
        <div class="admin-profile-info flex-grow-1 d-flex flex-column justify-content-center">
            <div class="d-flex align-items-center flex-wrap gap-2 mb-1.5">
                <h1 class="h3 font-weight-bold mb-0 text-dark"><?= Security::e($user['name']) ?></h1>
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1 fw-semibold d-inline-flex align-items-center gap-1">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
                    CENTEXS Trainee
                </span>
                <?php if ($isVerified): ?>
                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1 fw-semibold d-inline-flex align-items-center gap-1">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg> Verified
                    </span>
                <?php else: ?>
                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-2.5 py-1 fw-semibold d-inline-flex align-items-center gap-1">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg> Pending Verification
                    </span>
                <?php endif; ?>
            </div>

            <div class="d-flex align-items-center flex-wrap gap-3 text-muted small mb-2.5">
                <span class="d-inline-flex align-items-center gap-1">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                    <?= Security::e($user['email']) ?>
                </span>
                <span class="d-inline-flex align-items-center gap-1">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="16" rx="2"/><line x1="7" y1="8" x2="17" y2="8"/><line x1="7" y1="12" x2="13" y2="12"/></svg>
                    IC/NRIC: <strong><?= Security::e($nric) ?></strong>
                </span>
                <span class="d-inline-flex align-items-center gap-1">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                    <?= Security::e($company) ?>
                </span>
            </div>

            <div class="d-flex align-items-center gap-2 flex-wrap">
                <button class="btn btn-primary btn-sm px-3 d-inline-flex align-items-center gap-1.5 shadow-xs" data-bs-toggle="modal" data-bs-target="#editTraineeModal">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    Edit Profile
                </button>
                <button class="btn btn-outline-primary btn-sm px-3 d-inline-flex align-items-center gap-1.5" data-bs-toggle="modal" data-bs-target="#uploadDocModal">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                    Upload Document
                </button>
                <a href="#tab-security" class="btn btn-outline-secondary btn-sm px-3 d-inline-flex align-items-center gap-1.5" onclick="bootstrap.Tab.getOrCreateInstance(document.querySelector('#trainee-security-tab')).show()">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    Security
                </a>
            </div>
        </div>

    </div>

    <!-- ══ Quick Statistics Row ══ -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="admin-stat-card">
                <div class="stat-icon bg-primary-subtle text-primary">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                </div>
                <div>
                    <div class="stat-value"><?= (int)($stats['enrolled_courses'] ?? 0) ?></div>
                    <div class="stat-label">Enrolled Courses</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="admin-stat-card">
                <div class="stat-icon bg-success-subtle text-success">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                </div>
                <div>
                    <div class="stat-value"><?= (int)($stats['completed_courses'] ?? 0) ?></div>
                    <div class="stat-label">Completed Courses</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="admin-stat-card">
                <div class="stat-icon bg-warning-subtle text-warning">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="7"/><polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"/></svg>
                </div>
                <div>
                    <div class="stat-value"><?= (int)($stats['certificates_earned'] ?? 0) ?></div>
                    <div class="stat-label">Certificates Earned</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="admin-stat-card">
                <div class="stat-icon bg-info-subtle text-info">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                </div>
                <div>
                    <div class="stat-value"><?= count($documents) ?></div>
                    <div class="stat-label">Uploaded Documents</div>
                </div>
            </div>
        </div>
    </div>

    <!-- ══ Trainee Profile Tabs ══ -->
    <ul class="nav nav-tabs admin-profile-tabs mb-4" id="traineeProfileTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active d-inline-flex align-items-center gap-2" id="trainee-details-tab" data-bs-toggle="tab" data-bs-target="#tab-trainee-details" type="button" role="tab">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                Personal Details & Verification
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link d-inline-flex align-items-center gap-2" id="trainee-reports-tab" data-bs-toggle="tab" data-bs-target="#tab-trainee-reports" type="button" role="tab">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                My Reports
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link d-inline-flex align-items-center gap-2" id="trainee-security-tab" data-bs-toggle="tab" data-bs-target="#tab-trainee-security" type="button" role="tab">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                Account Settings & Security
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link d-inline-flex align-items-center gap-2" id="trainee-preferences-tab" data-bs-toggle="tab" data-bs-target="#tab-trainee-preferences" type="button" role="tab">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                Preferences
            </button>
        </li>
    </ul>

    <!-- ══ Tab Contents ══ -->
    <div class="tab-content" id="traineeProfileTabContent">

        <!-- TAB 1: PERSONAL DETAILS & VERIFICATION DOCUMENTS -->
        <div class="tab-pane fade show active" id="tab-trainee-details" role="tabpanel">
            <div class="row g-4">
                
                <!-- Left Column: Key-Value Information Grid -->
                <div class="col-lg-7">
                    <div class="panel h-100">
                        <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-3">
                            <div>
                                <h2 class="h5 mb-1">Personal & Academic Profile</h2>
                                <p class="text-muted small mb-0">Trainee identification, contact information, and educational background.</p>
                            </div>
                            <button class="btn btn-outline-primary btn-sm d-inline-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#editTraineeModal">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                Edit
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
                                        <span class="text-uppercase text-muted fw-bold d-block mb-1" style="font-size: 0.725rem; letter-spacing: 0.05em;">IC / NRIC / Passport</span>
                                        <span class="text-dark fw-semibold text-truncate d-block fs-6"><?= Security::e($nric) ?></span>
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
                                        <span class="text-dark fw-semibold text-truncate d-block fs-6"><?= Security::e($phone) ?></span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="info-field-card p-3 rounded-3 border bg-surface h-100 d-flex align-items-start gap-3">
                                    <div class="field-icon-box bg-warning-subtle text-warning rounded-2 p-2.5 flex-shrink-0">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                                    </div>
                                    <div class="flex-grow-1 overflow-hidden">
                                        <span class="text-uppercase text-muted fw-bold d-block mb-1" style="font-size: 0.725rem; letter-spacing: 0.05em;">Gender & Date of Birth</span>
                                        <span class="text-dark fw-semibold text-truncate d-block fs-6"><?= Security::e($gender) ?> · <?= Security::e($dob) ?></span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="info-field-card p-3 rounded-3 border bg-surface h-100 d-flex align-items-start gap-3">
                                    <div class="field-icon-box bg-secondary-subtle text-secondary rounded-2 p-2.5 flex-shrink-0">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                                    </div>
                                    <div class="flex-grow-1 overflow-hidden">
                                        <span class="text-uppercase text-muted fw-bold d-block mb-1" style="font-size: 0.725rem; letter-spacing: 0.05em;">Institution / Sponsor</span>
                                        <span class="text-dark fw-semibold text-truncate d-block fs-6"><?= Security::e($company) ?></span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="info-field-card p-3 rounded-3 border bg-surface h-100 d-flex align-items-start gap-3">
                                    <div class="field-icon-box bg-primary-subtle text-primary rounded-2 p-2.5 flex-shrink-0">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
                                    </div>
                                    <div class="flex-grow-1 overflow-hidden">
                                        <span class="text-uppercase text-muted fw-bold d-block mb-1" style="font-size: 0.725rem; letter-spacing: 0.05em;">Highest Education</span>
                                        <span class="text-dark fw-semibold d-block fs-6"><?= Security::e($education) ?></span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="info-field-card p-3 rounded-3 border bg-surface h-100 d-flex align-items-start gap-3">
                                    <div class="field-icon-box bg-danger-subtle text-danger rounded-2 p-2.5 flex-shrink-0">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/></svg>
                                    </div>
                                    <div class="flex-grow-1 overflow-hidden">
                                        <span class="text-uppercase text-muted fw-bold d-block mb-1" style="font-size: 0.725rem; letter-spacing: 0.05em;">Emergency Contact</span>
                                        <span class="text-dark fw-semibold d-block fs-6"><?= Security::e($emergency) ?></span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="info-field-card p-3 rounded-3 border bg-surface d-flex align-items-start gap-3">
                                    <div class="field-icon-box bg-primary-subtle text-primary rounded-2 p-2.5 flex-shrink-0">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                                    </div>
                                    <div class="flex-grow-1 overflow-hidden">
                                        <span class="text-uppercase text-muted fw-bold d-block mb-1" style="font-size: 0.725rem; letter-spacing: 0.05em;">Home / Mailing Address</span>
                                        <span class="text-dark fw-semibold d-block fs-6"><?= Security::e($address) ?></span>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                <!-- Right Column: Verification & Uploaded Supporting Documents (Collected for Master Data) -->
                <div class="col-lg-5">
                    <div class="panel h-100">
                        <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-3">
                            <div>
                                <h2 class="h5 mb-1">Verification Documents</h2>
                                <p class="text-muted small mb-0">Documents submitted for CENTEXS admin verification.</p>
                            </div>
                            <button class="btn btn-primary btn-sm d-inline-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#uploadDocModal">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                Upload File
                            </button>
                        </div>

                        <?php if (!empty($documents)): ?>
                            <div class="d-flex flex-column gap-3">
                                <?php foreach ($documents as $doc): ?>
                                    <?php 
                                        $docStatus = $doc['status'] ?? 'Pending Verification';
                                        $isDocVerified = ($docStatus === 'Verified');
                                    ?>
                                    <div class="border rounded-3 p-3 bg-surface shadow-xs transition-all hover-border-primary">
                                        <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="bg-primary-subtle text-primary p-2 rounded-2">
                                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                                                </div>
                                                <div>
                                                    <span class="fw-bold text-dark d-block text-truncate" style="max-width: 180px;"><?= Security::e($doc['document_type']) ?></span>
                                                    <span class="small text-muted d-block" style="font-size: 0.75rem;">Uploaded: <?= Security::e(date('M d, Y', strtotime($doc['uploaded_at']))) ?></span>
                                                </div>
                                            </div>
                                            <?php if ($isDocVerified): ?>
                                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-0.5 small">Verified</span>
                                            <?php else: ?>
                                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-2 py-0.5 small">Pending</span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="d-flex align-items-center justify-content-between pt-2 border-top mt-2">
                                            <span class="small text-muted text-truncate" style="max-width: 160px;"><?= Security::e($doc['file_name']) ?></span>
                                            <a href="storage/uploads/<?= Security::e($doc['file_path']) ?>" download class="btn btn-outline-primary btn-sm py-0 px-2.5" style="font-size: 0.75rem;">
                                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-1"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg> Download
                                            </a>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4 text-muted bg-surface rounded-3 border border-dashed p-4">
                                <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="text-muted mb-2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="12" y1="11" x2="12" y2="17"/><line x1="9" y1="14" x2="15" y2="14"/></svg>
                                <h3 class="h6 font-weight-bold mb-1">No Verification Documents Uploaded</h3>
                                <p class="small text-muted mb-3">Please upload your NRIC scan, academic qualification, or supporting files for admin master data verification.</p>
                                <button class="btn btn-outline-primary btn-sm px-3" data-bs-toggle="modal" data-bs-target="#uploadDocModal">Upload First Document</button>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

            </div>
        </div>

        <!-- TAB 2: MY REPORTS (EXCLUDES LEARNING REPORT AS INSTRUCTED) -->
        <div class="tab-pane fade" id="tab-trainee-reports" role="tabpanel">
            <div class="panel">
                <div class="d-flex align-items-center justify-content-between mb-4 border-bottom pb-3">
                    <div>
                        <h2 class="h5 mb-1">Trainee Progress & Academic Reports</h2>
                        <p class="text-muted small mb-0">Summary of attendance records, course evaluations, and certificate verification logs.</p>
                    </div>
                </div>

                <div class="row g-4">
                    <!-- Course Attendance Summary Card -->
                    <div class="col-md-6">
                        <div class="border rounded-3 p-4 bg-surface h-100 shadow-xs">
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <div class="p-2.5 bg-primary-subtle text-primary rounded-3">
                                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><rect x="8" y="2" width="8" height="4" rx="1" ry="1"/><path d="M9 14l2 2 4-4"/></svg>
                                </div>
                                <div>
                                    <h3 class="h6 font-weight-bold mb-0 text-dark">Attendance & Participation Report</h3>
                                    <span class="small text-muted">Tracked class sessions & attendance records</span>
                                </div>
                            </div>
                            <p class="small text-muted mb-3">Review verified attendance logs recorded across all active and completed training courses.</p>
                            <a href="index.php?page=trainee-dashboard" class="btn btn-outline-primary btn-sm px-3">View Attendance Summary →</a>
                        </div>
                    </div>

                    <!-- Academic Course Evaluation Report -->
                    <div class="col-md-6">
                        <div class="border rounded-3 p-4 bg-surface h-100 shadow-xs">
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <div class="p-2.5 bg-success-subtle text-success rounded-3">
                                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                </div>
                                <div>
                                    <h3 class="h6 font-weight-bold mb-0 text-dark">Course Evaluation & Performance Report</h3>
                                    <span class="small text-muted">Submitted feedback and instructor ratings</span>
                                </div>
                            </div>
                            <p class="small text-muted mb-3">Access your completed course evaluation submissions and rating records.</p>
                            <a href="index.php?page=trainee-evaluations" class="btn btn-outline-success btn-sm px-3">View Evaluation Reports →</a>
                        </div>
                    </div>

                    <!-- Certificate Verification Report -->
                    <div class="col-md-6">
                        <div class="border rounded-3 p-4 bg-surface h-100 shadow-xs">
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <div class="p-2.5 bg-warning-subtle text-warning rounded-3">
                                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="7"/><polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"/></svg>
                                </div>
                                <div>
                                    <h3 class="h6 font-weight-bold mb-0 text-dark">Certificate Verification Log</h3>
                                    <span class="small text-muted">Official CENTEXS credentials & verification codes</span>
                                </div>
                            </div>
                            <p class="small text-muted mb-3">View issued digital certificates, QR verification links, and downloadable PDF credentials.</p>
                            <a href="index.php?page=trainee-certificates" class="btn btn-outline-warning btn-sm px-3">View Certificates →</a>
                        </div>
                    </div>

                    <!-- Document Verification Status Report -->
                    <div class="col-md-6">
                        <div class="border rounded-3 p-4 bg-surface h-100 shadow-xs">
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <div class="p-2.5 bg-info-subtle text-info rounded-3">
                                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="12" y1="11" x2="12" y2="17"/><line x1="9" y1="14" x2="15" y2="14"/></svg>
                                </div>
                                <div>
                                    <h3 class="h6 font-weight-bold mb-0 text-dark">Master Data Verification Report</h3>
                                    <span class="small text-muted">Admin document audit and verification status</span>
                                </div>
                            </div>
                            <p class="small text-muted mb-3">Track the verification status of your identity card and supporting credentials in CENTEXS master data.</p>
                            <button class="btn btn-outline-info btn-sm px-3" data-bs-toggle="modal" data-bs-target="#uploadDocModal">Manage Verification Files →</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- TAB 3: ACCOUNT SETTINGS & SECURITY -->
        <div class="tab-pane fade" id="tab-trainee-security" role="tabpanel">
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
                        <h2 class="h5 mb-3 border-bottom pb-3">Account Security Details</h2>
                        <div class="d-flex flex-column gap-3">
                            <div class="p-3 border rounded-3 bg-surface">
                                <span class="text-uppercase text-muted fw-bold d-block mb-1" style="font-size: 0.725rem;">Account Email</span>
                                <span class="fw-semibold text-dark fs-6"><?= Security::e($user['email']) ?></span>
                            </div>
                            <div class="p-3 border rounded-3 bg-surface">
                                <span class="text-uppercase text-muted fw-bold d-block mb-1" style="font-size: 0.725rem;">Verification Status</span>
                                <span class="fw-semibold text-dark fs-6"><?= Security::e($verificationStatus) ?></span>
                            </div>
                            <div class="p-3 border rounded-3 bg-surface">
                                <span class="text-uppercase text-muted fw-bold d-block mb-1" style="font-size: 0.725rem;">Account Created Date</span>
                                <span class="fw-semibold text-dark fs-6"><?= Security::e($user['created_at'] ?? 'N/A') ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- TAB 4: PREFERENCES -->
        <div class="tab-pane fade" id="tab-trainee-preferences" role="tabpanel">
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

<!-- ══ Edit Trainee Profile Modal ══ -->
<div class="modal fade" id="editTraineeModal" tabindex="-1" aria-labelledby="editTraineeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title font-weight-bold" id="editTraineeModalLabel">Edit Trainee Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="index.php?page=save-trainee-profile" method="post">
                <input type="hidden" name="_csrf" value="<?= Security::e(Security::csrfToken()) ?>">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label font-weight-bold">Full Name</label>
                            <input type="text" name="name" class="form-control" value="<?= Security::e($user['name']) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label font-weight-bold">IC / NRIC / Passport Number</label>
                            <input type="text" name="identity_number" class="form-control" value="<?= Security::e($nric) ?>" placeholder="e.g. 950101-13-1234">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label font-weight-bold">Contact Phone Number</label>
                            <input type="text" name="phone" class="form-control" value="<?= Security::e($phone) ?>" placeholder="+60 12-345 6789">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label font-weight-bold">Gender</label>
                            <select name="gender" class="form-select">
                                <option value="Male" <?= $gender === 'Male' ? 'selected' : '' ?>>Male</option>
                                <option value="Female" <?= $gender === 'Female' ? 'selected' : '' ?>>Female</option>
                                <option value="Other" <?= $gender === 'Other' ? 'selected' : '' ?>>Other</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label font-weight-bold">Date of Birth</label>
                            <input type="date" name="date_of_birth" class="form-control" value="<?= Security::e($dob) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label font-weight-bold">Institution / Company Sponsor</label>
                            <input type="text" name="institution_company" class="form-control" value="<?= Security::e($company) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label font-weight-bold">Highest Educational Qualification</label>
                            <input type="text" name="education" class="form-control" value="<?= Security::e($education) ?>" placeholder="e.g. Diploma in IT">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label font-weight-bold">Emergency Contact (Name & Phone)</label>
                            <input type="text" name="emergency_contact" class="form-control" value="<?= Security::e($emergency) ?>" placeholder="e.g. Father: +6012-9998877">
                        </div>
                        <div class="col-12">
                            <label class="form-label font-weight-bold">Home / Mailing Address</label>
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

<!-- ══ Upload Verification Document Modal ══ -->
<div class="modal fade" id="uploadDocModal" tabindex="-1" aria-labelledby="uploadDocModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title font-weight-bold" id="uploadDocModalLabel">Upload Verification Document</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="index.php?page=save-trainee-profile" method="post" enctype="multipart/form-data">
                <input type="hidden" name="_csrf" value="<?= Security::e(Security::csrfToken()) ?>">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label font-weight-bold">Document Category / Type</label>
                        <select name="document_type" class="form-select" required>
                            <option value="Identity Card (NRIC / Passport)">Identity Card (NRIC / Passport)</option>
                            <option value="Academic Qualification / Diploma">Academic Qualification / Diploma</option>
                            <option value="Professional Certification">Professional Certification</option>
                            <option value="Registration Form / Sponsor Letter">Registration Form / Sponsor Letter</option>
                            <option value="Supporting Document">Supporting Document</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label font-weight-bold">Select File (PDF, PNG, JPG)</label>
                        <input type="file" name="document" class="form-control" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" required>
                        <span class="small text-muted mt-1 d-block">Maximum upload size: 25 MB</span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Upload for Admin Verification</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var editM = document.getElementById('editTraineeModal');
    if (editM && editM.parentElement !== document.body) {
        document.body.appendChild(editM);
    }
    var uploadM = document.getElementById('uploadDocModal');
    if (uploadM && uploadM.parentElement !== document.body) {
        document.body.appendChild(uploadM);
    }
});
</script>
