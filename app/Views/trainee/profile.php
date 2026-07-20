<?php use App\Core\Auth; use App\Core\Security; use App\Core\View; ?>
<?php View::partial('partials/role-nav'); ?>

<span class="section-label">Profile Settings</span>
<h1 class="section-title">Edit Profile</h1>

<div class="row g-4">
    <!-- Edit Form Panel -->
    <div class="col-lg-<?= Auth::role() === 'trainee' ? '7' : '12' ?> animate-in">
        <div class="overview-panel">
            <span class="section-label">Personal Information</span>
            <h2 class="overview-panel-title">Update Your Account Details</h2>
            
            <form method="post" action="index.php?page=save-trainee-profile" enctype="multipart/form-data">
                <input type="hidden" name="_csrf" value="<?= Security::e(Security::csrfToken()) ?>">
                
                <div class="row g-2 mb-2">
                    <div class="col-md-6">
                        <label class="form-label fw-bold small text-muted">Full Name</label>
                        <input class="form-control" name="name" value="<?= Security::e($user['name'] ?? Auth::user()['name']) ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold small text-muted">Email Address (Cannot change)</label>
                        <input class="form-control" value="<?= Security::e($user['email'] ?? Auth::user()['email']) ?>" disabled>
                    </div>
                </div>

                <div class="row g-2 mb-2">
                    <div class="col-md-6">
                        <label class="form-label fw-bold small text-muted">IC / Passport Number</label>
                        <input class="form-control" name="identity_number" value="<?= Security::e($user['identity_number'] ?? $profile['identity_number'] ?? '') ?>" placeholder="e.g. 950101-13-1234">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold small text-muted">Contact Phone</label>
                        <input class="form-control" name="phone" value="<?= Security::e($user['phone'] ?? $profile['phone'] ?? '') ?>" placeholder="e.g. +60123456789">
                    </div>
                </div>

                <div class="row g-3 mb-2">
                    <div class="col-md-4">
                        <label class="form-label fw-bold small text-muted">Gender</label>
                        <select class="form-select" name="gender">
                            <option value="">Select Gender</option>
                            <option value="Male" <?= ($user['gender'] ?? '') === 'Male' ? 'selected' : '' ?>>Male</option>
                            <option value="Female" <?= ($user['gender'] ?? '') === 'Female' ? 'selected' : '' ?>>Female</option>
                            <option value="Other" <?= ($user['gender'] ?? '') === 'Other' ? 'selected' : '' ?>>Other</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold small text-muted">Date of Birth</label>
                        <input class="form-control" type="date" name="date_of_birth" value="<?= Security::e($user['date_of_birth'] ?? '') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold small text-muted">Time Zone</label>
                        <input class="form-control" name="time_zone" value="<?= Security::e($user['time_zone'] ?? 'Asia/Kuala_Lumpur') ?>">
                    </div>
                </div>

                <div class="mb-2">
                    <label class="form-label fw-bold small text-muted">Institution / Company</label>
                    <input class="form-control" name="institution_company" value="<?= Security::e($user['institution_company'] ?? '') ?>" placeholder="e.g. CENTEXS Kuching">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold small text-muted">Address</label>
                    <textarea class="form-control" name="address" rows="2" placeholder="Street Address, City, Postcode, State"><?= Security::e($user['address'] ?? $profile['address'] ?? '') ?></textarea>
                </div>

                <!-- Trainee-specific Fields -->
                <?php if (Auth::role() === 'trainee'): ?>
                    <hr class="my-4">
                    <span class="section-label">Trainee Specifics</span>
                    <h4 class="h6 fw-bold mb-3">Education & Emergency Contact</h4>

                    <div class="mb-2">
                        <label class="form-label fw-bold small text-muted">Educational Background</label>
                        <textarea class="form-control" name="education" rows="2" placeholder="Degrees, Diplomas, Schools, Year of completion"><?= Security::e($profile['education'] ?? '') ?></textarea>
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-bold small text-muted">Employment Details</label>
                        <textarea class="form-control" name="employment" rows="2" placeholder="Current or past work experience"><?= Security::e($profile['employment'] ?? '') ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">Emergency Contact Name & Phone</label>
                        <textarea class="form-control" name="emergency_contact" rows="2" placeholder="e.g. Jane Doe (Mother) - +6012-3456789"><?= Security::e($profile['emergency_contact'] ?? '') ?></textarea>
                    </div>
                    
                    <div class="row g-2 mb-3">
                        <div class="col-md-5">
                            <label class="form-label fw-bold small text-muted">New Document Type</label>
                            <input class="form-control" name="document_type" placeholder="e.g. Certificate, Resume">
                        </div>
                        <div class="col-md-7">
                            <label class="form-label fw-bold small text-muted">Upload Supporting File</label>
                            <input class="form-control" type="file" name="document" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                        </div>
                    </div>
                <?php endif; ?>

                <div class="mb-2">
                    <label class="form-label fw-bold small text-muted">New Profile Picture (Optional)</label>
                    <input class="form-control mb-3" type="file" name="profile_picture" accept=".jpg,.jpeg,.png,.webp">
                </div>

                <button class="btn btn-primary px-4">Save Changes</button>
            </form>
        </div>
    </div>

    <!-- Trainee Uploaded Documents Column -->
    <?php if (Auth::role() === 'trainee'): ?>
        <div class="col-lg-5 animate-in">
            <div class="overview-panel">
                <span class="section-label">Verification</span>
                <h2 class="overview-panel-title">Uploaded Documents</h2>
                
                <?php foreach ($documents as $doc): ?>
                    <div class="completion-item">
                        <div class="completion-item-title"><?= Security::e($doc['document_type']) ?></div>
                        <div class="completion-item-meta">
                            <a href="storage/uploads/<?= Security::e($doc['file_path']) ?>" download class="fw-bold text-decoration-none" style="font-size:0.85rem">
                                📂 <?= Security::e($doc['file_name']) ?>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
                
                <?php if (empty($documents)): ?>
                    <div class="empty-state py-4">
                        <div class="empty-state-icon">📁</div>
                        <div class="empty-state-title">No files uploaded</div>
                        <p class="text-muted small">Upload support files like certificates, identity scans, etc.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>
