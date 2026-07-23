<?php use App\Core\Security; use App\Core\View; ?>
<?php View::partial('partials/role-nav'); ?>
<div class="profile-hero overview-panel mb-4 animate-in">
    <div class="profile-avatar" onclick="document.getElementById('avatarFileInput').click()" style="width: 120px; height: 120px; min-width: 120px; min-height: 120px; max-width: 120px; max-height: 120px; border-radius: 18px; border: 4px solid #ffffff; outline: 2px solid #e2e8f0; box-shadow: 0 8px 20px rgba(0,0,0,0.15); position: relative; overflow: hidden; cursor: pointer; flex-shrink: 0; background: #ffffff;">
        <?php if (!empty($user['profile_picture']) && file_exists(UPLOAD_PATH . '/' . $user['profile_picture'])): ?>
            <img src="storage/uploads/<?= Security::e($user['profile_picture']) ?>" alt="Profile Picture" style="width: 100%; height: 100%; object-fit: cover; object-position: center; display: block; border-radius: 14px;">
        <?php else: ?>
            <span style="width: 100%; height: 100%; display: grid; place-items: center; color: #ffffff; font-size: 2.5rem; font-weight: 800; background: linear-gradient(135deg, #054d9e 0%, #18a999 100%); border-radius: 14px;"><?= Security::e(strtoupper(substr($user['name'], 0, 1))) ?></span>
        <?php endif; ?>
        <div class="profile-avatar-overlay">
            <span>Change</span>
        </div>
    </div>
    <form id="avatarForm" action="index.php?page=save-profile-picture" method="post" enctype="multipart/form-data"
        class="d-none">
        <input type="hidden" name="_csrf" value="<?= Security::e(Security::csrfToken()) ?>">
        <input type="file" name="profile_picture" id="avatarFileInput" accept="image/*"
            onchange="document.getElementById('avatarForm').submit()">
    </form>
    <div>
        <h1 class="h3 mb-1"><?= Security::e($user['name']) ?></h1>
        <div class="text-muted"><?= Security::e($user['role_name'] ?? $user['role_slug']) ?> ·
            <?= Security::e($user['status']) ?></div>
    </div>
    <div class="ms-lg-auto d-flex gap-2">
        <a class="btn btn-outline-primary" href="index.php?page=trainee-profile">Edit Profile</a>
        <a class="btn btn-primary" href="index.php?page=messages">Message</a>
        <a class="btn btn-outline-secondary" href="index.php?page=notifications">Notifications</a>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="panel h-100">
            <h2 class="h5">User Details</h2>
            <dl class="profile-list">
                <dt>Full Name</dt>
                <dd><?= Security::e($user['name']) ?></dd>
                <dt>Email</dt>
                <dd><?= Security::e($user['email']) ?></dd>
                <dt>Phone</dt>
                <dd><?= Security::e($user['phone'] ?? '-') ?></dd>
                <dt>IC / Passport</dt>
                <dd><?= Security::e($user['identity_number'] ?? $profile['identity_number'] ?? '-') ?></dd>
                <dt>Gender</dt>
                <dd><?= Security::e($user['gender'] ?? '-') ?></dd>
                <dt>Date of Birth</dt>
                <dd><?= Security::e($user['date_of_birth'] ?? '-') ?></dd>
                <dt>Institution / Company</dt>
                <dd><?= Security::e($user['institution_company'] ?? '-') ?></dd>
                <dt>Time Zone</dt>
                <dd><?= Security::e($user['time_zone'] ?? 'Asia/Kuala_Lumpur') ?></dd>
                <dt>Registered</dt>
                <dd><?= Security::e($user['created_at'] ?? '-') ?></dd>
                <dt>Last Login</dt>
                <dd><?= Security::e($user['last_login'] ?? '-') ?></dd>
            </dl>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="panel h-100">
            <h2 class="h5">Reports</h2>
            <div class="quick-grid">
                <a href="index.php?page=trainee-dashboard">Course Progress</a>
                <a href="index.php?page=trainee-dashboard">Attendance Report</a>
                <a href="index.php?page=trainee-dashboard">Assignment Status</a>
                <a href="index.php?page=trainee-dashboard">Assessment Results</a>
                <a href="index.php?page=trainee-certificates">Certificate Status</a>
                <a href="index.php?page=reports">Learning Report</a>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="panel h-100">
            <h2 class="h5">Login Activity</h2>
            <p class="small text-muted">First login: <?= Security::e($user['first_login'] ?? '-') ?><br>Last active:
                <?= Security::e($user['last_active'] ?? '-') ?></p>
            <?php foreach ($learning['login_activity'] as $row): ?>
                <p class="border-bottom pb-2 mb-2"><strong><?= Security::e($row['status']) ?></strong><br><span
                        class="small text-muted"><?= Security::e($row['created_at']) ?> ·
                        <?= Security::e($row['user_agent'] ?? '') ?></span></p>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<div class="panel mt-4">
    <h2 class="h5">Course Details</h2>
    <div class="row g-3">
        <?php foreach ($learning['courses'] as $course): ?>
            <div class="col-md-6 col-xl-4">
                <div class="info-tile h-100">
                    <h3 class="h6"><?= Security::e($course['course_title']) ?></h3>
                    <p class="small text-muted mb-2">Instructor:
                        <?= Security::e($course['instructor_name'] ?? '-') ?><br>Schedule:
                        <?= Security::e($course['start_date'] ?? '-') ?> to <?= Security::e($course['end_date'] ?? '-') ?>
                    </p>
                    <div class="progress mb-2">
                        <div class="progress-bar" style="width: <?= (int) $course['progress_percent'] ?>%">
                            <?= (int) $course['progress_percent'] ?>%</div>
                    </div>
                    <span
                        class="badge text-bg-<?= $course['certificate_available'] ? 'success' : 'secondary' ?>"><?= $course['certificate_available'] ? 'Certificate Available' : 'Certificate Pending' ?></span>
                </div>
            </div>
        <?php endforeach; ?>
        <?php if (!$learning['courses']): ?>
            <div class="col-12 text-muted">No course records yet.</div><?php endif; ?>
    </div>
</div>