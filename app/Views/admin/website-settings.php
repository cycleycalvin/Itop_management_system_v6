<?php use App\Core\Security; use App\Core\View; ?>
<?php View::partial('partials/role-nav'); ?>

<span class="section-label">System Administration</span>
<h1 class="section-title">Website Settings & Content</h1>

<div class="row g-4">
    <!-- General Settings Form -->
    <div class="col-lg-6 animate-in">
        <div class="overview-panel">
            <span class="section-label">General Configuration</span>
            <h2 class="overview-panel-title">Landing Page & Footer</h2>
            <form method="post" action="index.php?page=save-website-settings">
                <input type="hidden" name="_csrf" value="<?= Security::e(Security::csrfToken()) ?>">
                
                <div class="mb-3">
                    <label class="form-label fw-bold small text-muted">Hero Title</label>
                    <input class="form-control" name="hero_title" value="<?= Security::e($settings['hero_title'] ?? '') ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold small text-muted">Hero Subtitle</label>
                    <textarea class="form-control" name="hero_subtitle" rows="3" required><?= Security::e($settings['hero_subtitle'] ?? '') ?></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold small text-muted">Footer About Text</label>
                    <textarea class="form-control" name="footer_about" rows="3" required><?= Security::e($settings['footer_about'] ?? '') ?></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold small text-muted">Footer Address</label>
                    <input class="form-control" name="footer_address" value="<?= Security::e($settings['footer_address'] ?? '') ?>" required>
                </div>
                <div class="row g-2">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold small text-muted">Support Email</label>
                        <input class="form-control" type="email" name="footer_email" value="<?= Security::e($settings['footer_email'] ?? '') ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold small text-muted">Contact Phone</label>
                        <input class="form-control" name="footer_phone" value="<?= Security::e($settings['footer_phone'] ?? '') ?>" required>
                    </div>
                </div>
                <div class="row g-2">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold small text-muted">Facebook URL</label>
                        <input class="form-control" type="url" name="footer_social_facebook" value="<?= Security::e($settings['footer_social_facebook'] ?? '') ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold small text-muted">LinkedIn URL</label>
                        <input class="form-control" type="url" name="footer_social_linkedin" value="<?= Security::e($settings['footer_social_linkedin'] ?? '') ?>">
                    </div>
                </div>
                
                <h4 class="h6 fw-bold mt-4 mb-2">Homepage Sections Visibility</h4>
                <div class="form-check form-switch mb-2">
                    <input type="hidden" name="show_upcoming_intakes" value="0">
                    <input class="form-check-input" type="checkbox" name="show_upcoming_intakes" value="1" id="switchIntakes" <?= ($settings['show_upcoming_intakes'] ?? '1') === '1' ? 'checked' : '' ?>>
                    <label class="form-check-label small" for="switchIntakes">Show Upcoming Intakes</label>
                </div>
                <div class="form-check form-switch mb-2">
                    <input type="hidden" name="show_success_stories" value="0">
                    <input class="form-check-input" type="checkbox" name="show_success_stories" value="1" id="switchStories" <?= ($settings['show_success_stories'] ?? '1') === '1' ? 'checked' : '' ?>>
                    <label class="form-check-label small" for="switchStories">Show Success Stories</label>
                </div>
                <div class="form-check form-switch mb-3">
                    <input type="hidden" name="show_announcements_home" value="0">
                    <input class="form-check-input" type="checkbox" name="show_announcements_home" value="1" id="switchAnn" <?= ($settings['show_announcements_home'] ?? '1') === '1' ? 'checked' : '' ?>>
                    <label class="form-check-label small" for="switchAnn">Show Announcements</label>
                </div>
                
                <button class="btn btn-primary w-100 mt-2">Save Settings</button>
            </form>
        </div>
    </div>

    <!-- Right Side: Intake and Stories management -->
    <div class="col-lg-6 d-flex flex-column gap-4">
        <!-- Upcoming Intakes Manager -->
        <div class="overview-panel animate-in">
            <span class="section-label">Dynamic Content</span>
            <h2 class="overview-panel-title">Upcoming Intakes Manager</h2>
            <form method="post" action="index.php?page=save-intake" class="border-bottom pb-3 mb-3">
                <input type="hidden" name="_csrf" value="<?= Security::e(Security::csrfToken()) ?>">
                <div class="row g-2">
                    <div class="col-md-6 mb-2">
                        <input class="form-control" name="intake_title" placeholder="Intake Title" required>
                    </div>
                    <div class="col-md-6 mb-2">
                        <input class="form-control" type="date" name="intake_date" required>
                    </div>
                </div>
                <div class="mb-2">
                    <select class="form-select" name="academy_id">
                        <option value="">Select Academy (Optional)</option>
                        <?php foreach ($academies as $a): ?>
                            <option value="<?= (int) $a['id'] ?>"><?= Security::e($a['code']) ?> — <?= Security::e($a['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-2">
                    <textarea class="form-control" name="description" rows="2" placeholder="Description of the intake..."></textarea>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_active" value="1" checked id="intakeActive">
                        <label class="form-check-label small" for="intakeActive">Active</label>
                    </div>
                    <button class="btn btn-sm btn-primary">Add Intake</button>
                </div>
            </form>
            <div class="table-responsive" style="max-height: 250px; overflow-y: auto;">
                <table class="table table-sm align-middle">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Date</th>
                            <th>Academy</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($intakes as $intake): ?>
                            <tr>
                                <td>
                                    <strong><?= Security::e($intake['intake_title']) ?></strong>
                                    <?php if (!(int) $intake['is_active']): ?><span class="badge bg-secondary">Inactive</span><?php endif; ?>
                                </td>
                                <td class="small"><?= Security::e($intake['intake_date']) ?></td>
                                <td><span class="badge text-bg-info"><?= Security::e($intake['academy_code'] ?? 'Global') ?></span></td>
                                <td class="text-end">
                                    <form method="post" action="index.php?page=delete-intake" onsubmit="return confirm('Delete this intake?')">
                                        <input type="hidden" name="_csrf" value="<?= Security::e(Security::csrfToken()) ?>">
                                        <input type="hidden" name="id" value="<?= (int) $intake['id'] ?>">
                                        <button class="btn btn-sm btn-outline-danger py-0 px-2" style="font-size:0.75rem">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($intakes)): ?>
                            <tr><td colspan="4" class="text-center text-muted small">No upcoming intakes created.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Success Stories Manager -->
        <div class="overview-panel animate-in">
            <span class="section-label">Dynamic Content</span>
            <h2 class="overview-panel-title">Success Stories Manager</h2>
            <form method="post" action="index.php?page=save-success-story" class="border-bottom pb-3 mb-3">
                <input type="hidden" name="_csrf" value="<?= Security::e(Security::csrfToken()) ?>">
                <div class="row g-2">
                    <div class="col-md-6 mb-2">
                        <input class="form-control" name="trainee_name" placeholder="Alumni Name" required>
                    </div>
                    <div class="col-md-6 mb-2">
                        <input class="form-control" name="course_title" placeholder="Completed Course" required>
                    </div>
                </div>
                <div class="mb-2">
                    <textarea class="form-control" name="quote" rows="2" placeholder="Testimonial / Quote" required></textarea>
                </div>
                <div class="row g-2 align-items-center">
                    <div class="col-md-6">
                        <input class="form-control form-control-sm" type="number" name="sort_order" placeholder="Sort Order (e.g. 0)" value="0">
                    </div>
                    <div class="col-md-6 d-flex justify-content-between align-items-center">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" checked id="storyActive">
                            <label class="form-check-label small" for="storyActive">Active</label>
                        </div>
                        <button class="btn btn-sm btn-primary">Add Story</button>
                    </div>
                </div>
            </form>
            <div class="table-responsive" style="max-height: 250px; overflow-y: auto;">
                <table class="table table-sm align-middle">
                    <thead>
                        <tr>
                            <th>Alumni</th>
                            <th>Course</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($successStories as $story): ?>
                            <tr>
                                <td>
                                    <strong><?= Security::e($story['trainee_name']) ?></strong>
                                    <?php if (!(int) $story['is_active']): ?><span class="badge bg-secondary">Inactive</span><?php endif; ?>
                                </td>
                                <td class="small"><?= Security::e($story['course_title']) ?></td>
                                <td class="text-end">
                                    <form method="post" action="index.php?page=delete-success-story" onsubmit="return confirm('Delete this story?')">
                                        <input type="hidden" name="_csrf" value="<?= Security::e(Security::csrfToken()) ?>">
                                        <input type="hidden" name="id" value="<?= (int) $story['id'] ?>">
                                        <button class="btn btn-sm btn-outline-danger py-0 px-2" style="font-size:0.75rem">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($successStories)): ?>
                            <tr><td colspan="3" class="text-center text-muted small">No success stories created.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
