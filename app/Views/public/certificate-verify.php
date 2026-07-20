<?php use App\Core\Security; ?>
<section class="container py-5">
    <h1 class="h2">Certificate Verification</h1>
    <form class="row g-2 mb-4" method="get">
        <input type="hidden" name="page" value="verify-certificate">
        <div class="col-md-8"><input class="form-control" name="code" value="<?= Security::e($code) ?>" placeholder="Enter verification code"></div>
        <div class="col-md-4"><button class="btn btn-primary w-100">Verify</button></div>
    </form>
    <?php if ($code): ?>
        <?php if ($certificate): ?>
            <div class="alert alert-success">Valid certificate for <strong><?= Security::e($certificate['trainee_name']) ?></strong> in <?= Security::e($certificate['course_title']) ?>, issued on <?= Security::e($certificate['issued_at']) ?>.</div>
        <?php else: ?>
            <div class="alert alert-warning">No certificate was found for that verification code.</div>
        <?php endif; ?>
    <?php endif; ?>
</section>

