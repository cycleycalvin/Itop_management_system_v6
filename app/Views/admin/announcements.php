<?php use App\Core\Security; use App\Core\View; ?>
<?php View::partial('partials/role-nav'); ?>

<span class="section-label">Communication</span>
<h1 class="section-title">Announcements</h1>

<div class="row g-4">
    <!-- Publish Panel -->
    <div class="col-lg-4 animate-in">
        <div class="overview-panel">
            <span class="section-label">Create</span>
            <h2 class="overview-panel-title">Publish Announcement</h2>
            <form method="post" action="index.php?page=save-announcement">
                <input type="hidden" name="_csrf" value="<?= Security::e(Security::csrfToken()) ?>">
                <input class="form-control mb-2" name="title" placeholder="Title" required>
                <textarea class="form-control mb-3" name="body" rows="5" placeholder="Write announcement message here..." required></textarea>
                <?php if (\App\Core\Auth::role() === 'admin'): ?>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="is_public" checked id="checkPublic" value="1">
                        <label class="form-check-label small" for="checkPublic">Make Public (Available on Landing Page)</label>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info py-2 px-3 mb-3 small">
                        📢 This announcement will be visible only to trainees enrolled in your courses.
                    </div>
                    <input type="hidden" name="is_public" value="0">
                <?php endif; ?>
                <button class="btn btn-primary w-100">Publish Announcement</button>
            </form>
        </div>
    </div>

    <!-- Announcements Feed -->
    <div class="col-lg-8 animate-in">
        <div class="overview-panel">
            <span class="section-label">History</span>
            <h2 class="overview-panel-title">Active Announcements</h2>
            <?php foreach ($announcements as $item): ?>
                <article class="completion-item">
                    <h3 class="h6 mb-1 fw-bold"><?= Security::e($item['title']) ?></h3>
                    <p class="mb-2 text-muted small"><?= Security::e($item['body']) ?></p>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="small text-muted">By: <?= Security::e($item['author_name'] ?? '-') ?></span>
                        <span class="badge text-bg-<?= $item['is_public'] ? 'success' : 'secondary' ?>"><?= $item['is_public'] ? 'Public' : 'Internal' ?></span>
                    </div>
                </article>
            <?php endforeach; ?>
            <?php if (empty($announcements)): ?>
                <div class="empty-state py-4">
                    <div class="empty-state-icon">📢</div>
                    <div class="empty-state-title">No announcements</div>
                    <p class="text-muted small">Add your first announcement using the form.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
