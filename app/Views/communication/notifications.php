<?php use App\Core\Security; use App\Core\View; ?>
<section class="container py-4">
    <?php View::partial('partials/role-nav'); ?>
    <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 mb-3">
        <div><h1 class="h3 mb-1">Notifications</h1><p class="text-muted mb-0">Track learning, certificate, message, and system updates.</p></div>
        <form class="row g-2" method="get"><input type="hidden" name="page" value="notifications"><div class="col-sm"><input class="form-control" name="q" value="<?= Security::e($q) ?>" placeholder="Search notifications"></div><div class="col-sm"><select class="form-select" name="type"><option value="">All categories</option><?php foreach ($types as $item): ?><option value="<?= Security::e($item['notification_type']) ?>" <?= $type === $item['notification_type'] ? 'selected' : '' ?>><?= Security::e(ucwords(str_replace('_', ' ', $item['notification_type']))) ?></option><?php endforeach; ?></select></div><div class="col-sm-auto"><button class="btn btn-primary w-100">Filter</button></div></form>
    </div>
    <div class="panel mb-3">
        <form method="post" action="index.php?page=mark-notification-read" data-ajax-form>
            <input type="hidden" name="_csrf" value="<?= Security::e(Security::csrfToken()) ?>">
            <input type="hidden" name="all" value="1">
            <button class="btn btn-sm btn-outline-primary">Mark All as Read</button>
        </form>
    </div>
    <div class="row g-3">
        <?php foreach ($notifications as $notification): ?>
            <div class="col-md-6 col-xl-4">
                <div class="card h-100 notification-card <?= $notification['read_at'] ? '' : 'unread' ?>">
                    <div class="card-body">
                        <div class="d-flex justify-content-between gap-2 mb-2"><span class="badge text-bg-info"><?= Security::e(ucwords(str_replace('_', ' ', $notification['notification_type']))) ?></span><span class="small text-muted"><?= Security::e($notification['created_at']) ?></span></div>
                        <h2 class="h5"><?= Security::e($notification['title']) ?></h2>
                        <p><?= Security::e($notification['description'] ?? '') ?></p>
                        <p class="small text-muted">Sender: <?= Security::e($notification['sender_name'] ?? 'System') ?></p>
                        <div class="d-flex flex-wrap gap-2">
                            <?php if ($notification['related_url']): ?><a class="btn btn-sm btn-outline-primary" href="<?= Security::e($notification['related_url']) ?>">Open</a><?php endif; ?>
                            <?php if (!$notification['read_at']): ?><form method="post" action="index.php?page=mark-notification-read" data-ajax-form><input type="hidden" name="_csrf" value="<?= Security::e(Security::csrfToken()) ?>"><input type="hidden" name="id" value="<?= (int) $notification['id'] ?>"><button class="btn btn-sm btn-outline-secondary">Mark Read</button></form><?php endif; ?>
                            <form method="post" action="index.php?page=delete-notification" data-ajax-form><input type="hidden" name="_csrf" value="<?= Security::e(Security::csrfToken()) ?>"><input type="hidden" name="id" value="<?= (int) $notification['id'] ?>"><button class="btn btn-sm btn-outline-danger">Delete</button></form>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        <?php if (!$notifications): ?><div class="col-12"><div class="panel text-muted">No notifications found.</div></div><?php endif; ?>
    </div>
</section>
