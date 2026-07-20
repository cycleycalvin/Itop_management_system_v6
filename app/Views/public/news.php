<?php use App\Core\Security; ?>
<section class="container py-5">
    <h1 class="h2">News & Announcements</h1>
    <div class="list-group mt-3">
        <?php foreach ($announcements as $item): ?>
            <article class="list-group-item">
                <h2 class="h5"><?= Security::e($item['title']) ?></h2>
                <p><?= nl2br(Security::e($item['body'])) ?></p>
                <small class="text-muted"><?= Security::e($item['published_at'] ?? $item['created_at']) ?></small>
            </article>
        <?php endforeach; ?>
    </div>
</section>

