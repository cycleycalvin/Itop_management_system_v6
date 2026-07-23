<?php use App\Core\Security; use App\Core\View; ?>
<div class="admin-documentation-container animate-in">
    <?php View::partial('partials/role-nav'); ?>

    <!-- Alert Notifications -->
    <?php if (!empty($_GET['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show mb-4 shadow-sm" role="alert">
            <div class="d-flex align-items-center gap-2">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                <span><?= Security::e($_GET['success']) ?></span>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (!empty($_GET['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show mb-4 shadow-sm" role="alert">
            <div class="d-flex align-items-center gap-2">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                <span><?= Security::e($_GET['error']) ?></span>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Header Section -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h1 class="h3 font-weight-bold mb-1 text-dark">Trainee Verification & Document Audit</h1>
            <p class="text-muted small mb-0">Review submitted identity documents, verify trainee profiles, and collect verified data into CENTEXS Master Data.</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <form class="d-flex gap-2" method="get">
                <input type="hidden" name="page" value="admin-documentation">
                <input class="form-control form-control-sm" name="q" value="<?= Security::e($q) ?>" placeholder="Search trainees, NRIC or email..." style="min-width: 240px;">
                <button class="btn btn-primary btn-sm px-3">Search</button>
            </form>
            <a class="btn btn-outline-secondary btn-sm px-3 d-inline-flex align-items-center gap-1" href="index.php?page=admin-documentation-export&q=<?= urlencode((string) $q) ?>">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg> Export CSV
            </a>
        </div>
    </div>

    <div class="row g-4">
        <!-- Left Column: Trainees List -->
        <div class="col-lg-6">
            <div class="panel h-100">
                <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-3">
                    <h2 class="h5 mb-0 font-weight-bold">Trainees Master List</h2>
                    <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-1"><?= count($trainees) ?> Trainees</span>
                </div>

                <div class="table-responsive">
                    <table class="table align-middle table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Trainee</th>
                                <th>NRIC / Identity</th>
                                <th>Documents</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($trainees as $trainee): ?>
                                <?php $isSelected = ($selected && (int)$selected['id'] === (int)$trainee['id']); ?>
                                <tr class="<?= $isSelected ? 'table-active' : '' ?>">
                                    <td>
                                        <div class="fw-bold text-dark"><?= Security::e($trainee['name']) ?></div>
                                        <div class="small text-muted"><?= Security::e($trainee['email']) ?></div>
                                    </td>
                                    <td>
                                        <span class="small font-monospace"><?= Security::e($trainee['identity_number'] ?? 'Not Specified') ?></span>
                                    </td>
                                    <td>
                                        <?php if ((int)$trainee['document_count'] > 0): ?>
                                            <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill px-2.5 py-1">
                                                📁 <?= (int) $trainee['document_count'] ?> File(s)
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary-subtle text-secondary rounded-pill px-2 py-0.5 small">No Files</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <a class="btn btn-sm <?= $isSelected ? 'btn-primary' : 'btn-outline-primary' ?> px-3 py-1" href="index.php?page=admin-documentation&trainee_id=<?= (int) $trainee['id'] ?>">
                                            Audit Details →
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($trainees)): ?>
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">No trainees found matching query.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Right Column: Verification Audit & Document Details -->
        <div class="col-lg-6">
            <div class="panel h-100">
                <?php if ($selected): ?>
                    <?php 
                        $isProfileVerified = !empty($selected['is_verified']) && (int)$selected['is_verified'] === 1;
                    ?>
                    <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-3">
                        <div>
                            <h2 class="h5 mb-1 font-weight-bold"><?= Security::e($selected['name']) ?></h2>
                            <span class="text-muted small"><?= Security::e($selected['email']) ?></span>
                        </div>
                        <div>
                            <?php if ($isProfileVerified): ?>
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1.5 fw-semibold d-inline-flex align-items-center gap-1">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                                    Master Data Verified
                                </span>
                            <?php else: ?>
                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-3 py-1.5 fw-semibold d-inline-flex align-items-center gap-1">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                                    Pending Audit
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Profile Key Details -->
                    <div class="row g-3 mb-4">
                        <div class="col-6">
                            <div class="p-2.5 border rounded-3 bg-surface">
                                <span class="text-uppercase text-muted fw-bold d-block mb-0.5" style="font-size: 0.7rem;">IC / NRIC Number</span>
                                <span class="fw-semibold text-dark small"><?= Security::e($selected['identity_number'] ?? 'Not Specified') ?></span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-2.5 border rounded-3 bg-surface">
                                <span class="text-uppercase text-muted fw-bold d-block mb-0.5" style="font-size: 0.7rem;">Contact Phone</span>
                                <span class="fw-semibold text-dark small"><?= Security::e($selected['phone'] ?? 'Not Specified') ?></span>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="p-2.5 border rounded-3 bg-surface">
                                <span class="text-uppercase text-muted fw-bold d-block mb-0.5" style="font-size: 0.7rem;">Highest Education</span>
                                <span class="fw-semibold text-dark small"><?= Security::e($selected['education'] ?? 'Not Specified') ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Uploaded Documents Audit List -->
                    <h3 class="h6 font-weight-bold mb-3 d-flex align-items-center justify-content-between">
                        <span>Submitted Verification Documents</span>
                        <span class="badge bg-secondary-subtle text-secondary rounded-pill px-2.5 py-0.5"><?= count($documents) ?> File(s)</span>
                    </h3>

                    <?php if (!empty($documents)): ?>
                        <div class="d-flex flex-column gap-3">
                            <?php foreach ($documents as $doc): ?>
                                <?php 
                                    $docStatus = $doc['status'] ?? 'Pending Verification';
                                    $isDocVerified = ($docStatus === 'Verified');
                                ?>
                                <div class="p-3 border rounded-3 bg-surface shadow-xs">
                                    <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                                        <div>
                                            <span class="fw-bold text-dark d-block"><?= Security::e($doc['document_type']) ?></span>
                                            <span class="small text-muted d-block">Uploaded: <?= Security::e(date('M d, Y · H:i', strtotime($doc['uploaded_at']))) ?></span>
                                        </div>
                                        <?php if ($isDocVerified): ?>
                                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1 small">Verified</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-2.5 py-1 small">Pending Audit</span>
                                        <?php endif; ?>
                                    </div>

                                    <div class="d-flex align-items-center justify-content-between pt-2 border-top mt-2 flex-wrap gap-2">
                                        <span class="small text-muted text-truncate" style="max-width: 200px;"><?= Security::e($doc['file_name']) ?></span>
                                        <div class="d-flex align-items-center gap-2">
                                            <a href="storage/uploads/<?= Security::e($doc['file_path']) ?>" download class="btn btn-outline-secondary btn-sm py-1 px-2.5" style="font-size: 0.775rem;">
                                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-1"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg> Download
                                            </a>
                                            <?php if (!$isDocVerified): ?>
                                                <form action="index.php?page=admin-verify-document" method="post" class="d-inline">
                                                    <input type="hidden" name="_csrf" value="<?= Security::e(Security::csrfToken()) ?>">
                                                    <input type="hidden" name="document_id" value="<?= (int)$doc['document_id'] ?>">
                                                    <input type="hidden" name="trainee_id" value="<?= (int)$selected['id'] ?>">
                                                    <button type="submit" class="btn btn-success btn-sm py-1 px-3 d-inline-flex align-items-center gap-1" style="font-size: 0.775rem;">
                                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg> Verify & Approve
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4 text-muted bg-surface rounded-3 border border-dashed p-4">
                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="text-muted mb-2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                            <h4 class="h6 font-weight-bold mb-1">No Verification Files Uploaded</h4>
                            <p class="small text-muted mb-0">This trainee has not submitted any supporting verification files yet.</p>
                        </div>
                    <?php endif; ?>

                <?php else: ?>
                    <div class="text-center py-5 text-muted">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" class="text-muted mb-2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        <h3 class="h6 font-weight-bold mb-1">Select a Trainee to Audit</h3>
                        <p class="small text-muted mb-0">Click "Audit Details" on any trainee in the left table to inspect uploaded documents and update master data verification.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
