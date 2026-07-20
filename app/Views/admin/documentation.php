<?php use App\Core\Security; use App\Core\View; ?>
<section class="container py-4">
    <?php View::partial('partials/role-nav'); ?>
    <div class="d-flex flex-column flex-md-row justify-content-between gap-3 mb-3">
        <div><h1 class="h3 mb-1">Trainee Documentation</h1><p class="text-muted mb-0">Review profile details and uploaded supporting documents.</p></div>
        <form class="d-flex" method="get"><input type="hidden" name="page" value="admin-documentation"><input class="form-control" name="q" value="<?= Security::e($q) ?>" placeholder="Search participants"><button class="btn btn-primary ms-2">Search</button><a class="btn btn-outline-primary ms-2" href="index.php?page=admin-documentation-export&q=<?= urlencode((string) $q) ?>">Export</a></form>
    </div>
    <div class="row g-4">
        <div class="col-lg-7"><div class="panel table-responsive"><table class="table align-middle"><thead><tr><th>Participant</th><th>Education</th><th>Documents</th><th></th></tr></thead><tbody><?php foreach ($trainees as $trainee): ?><tr><td><strong><?= Security::e($trainee['name']) ?></strong><br><span class="small text-muted"><?= Security::e($trainee['email']) ?></span></td><td><?= Security::e(Security::excerpt($trainee['education'] ?? '', 70)) ?></td><td><?= (int) $trainee['document_count'] ?></td><td><a class="btn btn-sm btn-outline-primary" href="index.php?page=admin-documentation&trainee_id=<?= (int) $trainee['id'] ?>">View</a></td></tr><?php endforeach; ?></tbody></table></div></div>
        <div class="col-lg-5">
            <div class="panel">
                <h2 class="h5">Profile Details</h2>
                <?php if ($selected): ?>
                    <p><strong><?= Security::e($selected['name']) ?></strong><br><span class="text-muted"><?= Security::e($selected['email']) ?></span></p>
                    <dl class="row small"><dt class="col-5">Identity No.</dt><dd class="col-7"><?= Security::e($selected['identity_number'] ?? '-') ?></dd><dt class="col-5">Phone</dt><dd class="col-7"><?= Security::e($selected['phone'] ?? '-') ?></dd><dt class="col-5">Education</dt><dd class="col-7"><?= nl2br(Security::e($selected['education'] ?? '-')) ?></dd><dt class="col-5">Employment</dt><dd class="col-7"><?= nl2br(Security::e($selected['employment'] ?? '-')) ?></dd><dt class="col-5">Emergency</dt><dd class="col-7"><?= nl2br(Security::e($selected['emergency_contact'] ?? '-')) ?></dd></dl>
                    <h3 class="h6 mt-3">Documents</h3>
                    <?php foreach ($documents as $doc): ?><p class="border-bottom pb-2"><strong><?= Security::e($doc['document_type']) ?></strong><br><a href="storage/uploads/<?= Security::e($doc['file_path']) ?>" download><?= Security::e($doc['file_name']) ?></a></p><?php endforeach; ?>
                <?php else: ?>
                    <p class="text-muted mb-0">Select a trainee to view submitted information.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
