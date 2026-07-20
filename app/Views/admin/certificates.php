<?php
use App\Core\Security;
use App\Core\View;

$classicLayout = json_encode([
    'style' => [
        'template' => 'classic_border',
        'title_color' => '#aa3338',
        'accent_color' => '#244f63',
        'border_color' => '#25566a',
        'seal_color' => '#b53638',
        'pattern_opacity' => 0.45,
    ],
    'show_seal' => true,
    'show_verification' => true,
    'seal_text' => 'ITOP',
    'seal_caption' => 'Certified',
    'placeholders' => [
        'title' => 'Certificate Title',
        'recipient' => 'Participant Name',
        'course' => 'Course Name',
        'date' => 'Issue Date',
        'certificate_no' => 'Certificate Number',
        'issuer_title' => 'Authorized Signatory',
    ],
], JSON_PRETTY_PRINT);
?>
<section class="container py-4">
    <?php View::partial('partials/role-nav'); ?>
    <h1 class="h3 mb-3">Certificate Management</h1>
    <div class="row g-4">
        <div class="col-lg-4">
            <div class="panel mb-4">
                <h2 class="h5"><?= $editingTemplate ? 'Edit Template' : 'Create Template' ?></h2>
                <form method="post" action="index.php?page=save-certificate-template" enctype="multipart/form-data">
                    <input type="hidden" name="_csrf" value="<?= Security::e(Security::csrfToken()) ?>">
                    <input type="hidden" name="template_id" value="<?= (int) ($editingTemplate['template_id'] ?? 0) ?>">
                    <input class="form-control mb-2" name="template_name" value="<?= Security::e($editingTemplate['template_name'] ?? 'Classic Bordered Certification') ?>" placeholder="Template name" required>
                    <div class="row g-2"><div class="col"><input class="form-control mb-2" name="font_family" value="<?= Security::e($editingTemplate['font_family'] ?? 'Arial, sans-serif') ?>" placeholder="Font"></div><div class="col"><input class="form-control mb-2" type="number" name="font_size" value="<?= (int) ($editingTemplate['font_size'] ?? 28) ?>"></div></div>
                    <input class="form-control form-control-color mb-2" type="color" name="text_color" value="<?= Security::e($editingTemplate['text_color'] ?? '#182230') ?>">
                    <label class="form-label small">Background Image</label><input class="form-control mb-2" type="file" name="background_image" accept=".jpg,.jpeg,.png,.webp">
                    <label class="form-label small">Organization Logo</label><input class="form-control mb-2" type="file" name="logo" accept=".jpg,.jpeg,.png,.webp">
                    <label class="form-label small">Signature Image</label><input class="form-control mb-2" type="file" name="signature" accept=".jpg,.jpeg,.png,.webp">
                    <textarea class="form-control mb-2" name="layout_json" rows="12"><?= Security::e($editingTemplate['layout_json'] ?? $classicLayout) ?></textarea>
                    <div class="small text-muted mb-2">Use this JSON to customize border colours, title colour, seal text, verification block, and placeholder labels.</div>
                    <select class="form-select mb-3" name="status"><option value="active" <?= ($editingTemplate['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Active</option><option value="inactive" <?= ($editingTemplate['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option></select>
                    <button class="btn btn-primary w-100">Save Template</button>
                </form>
            </div>
            <div class="panel">
                <h2 class="h5">Issue Certificate</h2>
                <form method="post" action="index.php?page=issue-certificate">
                    <input type="hidden" name="_csrf" value="<?= Security::e(Security::csrfToken()) ?>">
                    <select class="form-select mb-2" name="trainee_id" required><option value="">Trainee</option><?php foreach ($trainees as $trainee): ?><option value="<?= (int) $trainee['id'] ?>"><?= Security::e($trainee['name']) ?></option><?php endforeach; ?></select>
                    <select class="form-select mb-2" name="course_id" required><option value="">Course</option><?php foreach ($courses as $course): ?><option value="<?= (int) $course['id'] ?>"><?= Security::e($course['title']) ?></option><?php endforeach; ?></select>
                    <select class="form-select mb-2" name="template_id"><option value="">Template</option><?php foreach ($activeTemplates as $template): ?><option value="<?= (int) $template['template_id'] ?>"><?= Security::e($template['template_name']) ?></option><?php endforeach; ?></select>
                    <input class="form-control mb-2" name="certificate_number" placeholder="Certificate number (auto if blank)">
                    <input class="form-control mb-2" name="verification_code" placeholder="Verification code (auto if blank)">
                    <input class="form-control mb-2" name="pdf_path" placeholder="PDF path or generated file name">
                    <input class="form-control mb-2" type="date" name="issue_date" value="<?= date('Y-m-d') ?>">
                    <select class="form-select mb-3" name="status"><option>issued</option><option>reissued</option><option>revoked</option></select>
                    <button class="btn btn-primary w-100">Issue / Reissue</button>
                </form>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="row g-3 mb-4">
                <?php foreach ($templates as $template): ?>
                    <div class="col-md-6"><div class="card h-100"><div class="card-body">
                        <h2 class="h6"><?= Security::e($template['template_name']) ?></h2>
                        <?php View::partial('partials/certificate-preview', ['template' => $template]); ?>
                        <div class="d-flex gap-2 mt-3"><a class="btn btn-sm btn-outline-secondary" href="index.php?page=admin-certificates&template=<?= (int) $template['template_id'] ?>">Edit</a><form method="post" action="index.php?page=delete-certificate-template"><input type="hidden" name="_csrf" value="<?= Security::e(Security::csrfToken()) ?>"><input type="hidden" name="template_id" value="<?= (int) $template['template_id'] ?>"><button class="btn btn-sm btn-outline-danger">Delete</button></form></div>
                    </div></div></div>
                <?php endforeach; ?>
            </div>
            <div class="panel table-responsive">
                <form class="d-flex mb-3" method="get"><input type="hidden" name="page" value="admin-certificates"><input class="form-control" name="q" value="<?= Security::e($q) ?>" placeholder="Search certificates"><button class="btn btn-primary ms-2">Search</button></form>
                <table class="table align-middle"><thead><tr><th>Certificate</th><th>Trainee</th><th>Course</th><th>Status</th><th>Issued</th><th class="text-end">Review</th></tr></thead><tbody><?php foreach ($certificates as $cert): ?><tr><td><?= Security::e($cert['display_number']) ?></td><td><?= Security::e($cert['trainee_name']) ?></td><td><?= Security::e($cert['course_title']) ?></td><td><span class="badge text-bg-<?= ($cert['approval_status'] ?? 'pending') === 'approved' ? 'success' : (($cert['approval_status'] ?? 'pending') === 'rejected' ? 'danger' : 'warning') ?>"><?= Security::e($cert['approval_status'] ?? 'pending') ?></span></td><td><?= Security::e($cert['issue_date'] ?? $cert['issued_at']) ?></td><td><div class="d-flex justify-content-end gap-2"><a class="btn btn-sm btn-outline-primary" href="index.php?page=view-certificate&id=<?= (int) $cert['id'] ?>">Preview</a><form method="post" action="index.php?page=review-certificate"><input type="hidden" name="_csrf" value="<?= Security::e(Security::csrfToken()) ?>"><input type="hidden" name="id" value="<?= (int) $cert['id'] ?>"><input type="hidden" name="approval_status" value="approved"><button class="btn btn-sm btn-outline-success">Approve</button></form><form method="post" action="index.php?page=review-certificate"><input type="hidden" name="_csrf" value="<?= Security::e(Security::csrfToken()) ?>"><input type="hidden" name="id" value="<?= (int) $cert['id'] ?>"><input type="hidden" name="approval_status" value="rejected"><button class="btn btn-sm btn-outline-danger">Reject</button></form></div></td></tr><?php endforeach; ?></tbody></table>
            </div>
        </div>
    </div>
</section>
