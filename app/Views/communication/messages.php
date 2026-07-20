<?php use App\Core\Security; use App\Core\View; ?>
<section class="container py-4">
    <?php View::partial('partials/role-nav'); ?>
    <div class="d-flex flex-column flex-md-row justify-content-between gap-3 mb-3">
        <div><h1 class="h3 mb-1">Messages</h1><p class="text-muted mb-0">Communicate with trainees, instructors, and administrators.</p></div>
        <form class="d-flex" method="get"><input type="hidden" name="page" value="messages"><input class="form-control" name="q" value="<?= Security::e($q) ?>" placeholder="Search conversation"><button class="btn btn-primary ms-2">Search</button></form>
    </div>
    <div class="row g-4">
        <div class="col-lg-4">
            <div class="panel mb-4">
                <h2 class="h5">New Message</h2>
                <form method="post" action="index.php?page=send-message" enctype="multipart/form-data" data-ajax-form>
                    <input type="hidden" name="_csrf" value="<?= Security::e(Security::csrfToken()) ?>">
                    <select class="form-select mb-2" name="receiver_id" required><option value="">Send to</option><?php foreach ($contacts as $contact): ?><option value="<?= (int) $contact['id'] ?>"><?= Security::e($contact['name']) ?> (<?= Security::e($contact['role_slug']) ?>)</option><?php endforeach; ?></select>
                    <input class="form-control mb-2" name="subject" placeholder="Subject">
                    <textarea class="form-control mb-2" name="body" rows="4" placeholder="Message" required></textarea>
                    <input class="form-control mb-3" type="file" name="attachment">
                    <button class="btn btn-primary w-100">Send</button>
                </form>
            </div>
            <div class="panel conversation-list">
                <h2 class="h5">Conversations</h2>
                <?php foreach ($conversations as $conversation): ?>
                    <a class="conversation-item <?= (int) $conversationId === (int) $conversation['id'] ? 'active' : '' ?>" href="index.php?page=messages&conversation_id=<?= (int) $conversation['id'] ?>">
                        <strong><?= Security::e($conversation['subject']) ?></strong>
                        <span><?= Security::e($conversation['participants']) ?></span>
                        <?php if ((int) $conversation['unread_count'] > 0): ?><em><?= (int) $conversation['unread_count'] ?></em><?php endif; ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="panel">
                <h2 class="h5">Conversation</h2>
                <div class="message-thread" data-conversation-id="<?= (int) $conversationId ?>">
                    <?php foreach ($messages as $message): ?>
                        <div class="message-bubble">
                            <strong><?= Security::e($message['sender_name']) ?></strong>
                            <p><?= nl2br(Security::e($message['body'])) ?></p>
                            <?php if ($message['attachment_path']): ?><a class="small" href="storage/uploads/<?= Security::e($message['attachment_path']) ?>" download>Attachment</a><?php endif; ?>
                            <span><?= Security::e($message['created_at']) ?> · <?= $message['read_at'] ? 'Read' : 'Unread' ?></span>
                        </div>
                    <?php endforeach; ?>
                    <?php if (!$conversationId): ?><p class="text-muted mb-0">Select or start a conversation.</p><?php endif; ?>
                </div>
                <?php if ($conversationId): ?>
                    <form class="mt-3" method="post" action="index.php?page=send-message" enctype="multipart/form-data" data-ajax-form>
                        <input type="hidden" name="_csrf" value="<?= Security::e(Security::csrfToken()) ?>">
                        <input type="hidden" name="conversation_id" value="<?= (int) $conversationId ?>">
                        <textarea class="form-control mb-2" name="body" rows="3" placeholder="Reply" required></textarea>
                        <input class="form-control mb-2" type="file" name="attachment">
                        <button class="btn btn-primary">Reply</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
