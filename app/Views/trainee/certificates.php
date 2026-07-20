<?php use App\Core\Security; use App\Core\View; ?>
<section class="container py-4">
    <?php View::partial('partials/role-nav'); ?>

    <span class="section-label">Certification Module</span>
    <h1 class="section-title">Digital certificates and verification</h1>

    <?php
        $featured = $certificates[0] ?? null;
        $others = array_slice($certificates, 1);
    ?>

    <div class="lms-layout">
        <!-- ── Certificate Display ───────────────────── -->
        <div>
            <?php if ($featured): ?>
                <div class="lms-card">
                    <h2 class="lms-card-title">Digital certificate</h2>

                    <!-- Certificate Preview Area -->
                    <div class="cert-display">
                        <div class="cert-display-heading">Certificate of Completion</div>
                        <div class="cert-display-title"><?= Security::e($featured['course_title']) ?></div>
                        <div class="cert-display-number">Certificate No: <?= Security::e($featured['display_number']) ?></div>
                        <div class="cert-qr">QR</div>
                    </div>

                    <div class="lms-btn-group">
                        <a class="lms-btn lms-btn-primary" href="index.php?page=download-certificate&id=<?= (int) $featured['id'] ?>">Download PDF</a>
                        <a class="lms-btn" href="index.php?page=verify-certificate&code=<?= urlencode((string) $featured['verification_code']) ?>">Verify Certificate</a>
                    </div>
                </div>

                <!-- Full Certificate Preview (expandable) -->
                <div class="lms-card mt-3">
                    <h2 class="lms-card-title">Certificate preview</h2>
                    <?php View::partial('partials/certificate-preview', [
                        'template' => $featured,
                        'sample' => [
                            'title' => $featured['template_name'] ?: 'CENTEXS Certification',
                            'recipient' => 'Your Name',
                            'course' => $featured['course_title'],
                            'date' => $featured['issue_date'] ?? $featured['issued_at'],
                            'certificate_no' => $featured['display_number'],
                            'issuer_title' => $featured['instructor_name'] ?: 'Authorized Signatory',
                            'organization' => 'CENTEXS',
                        ],
                    ]); ?>
                </div>
            <?php else: ?>
                <div class="lms-card">
                    <h2 class="lms-card-title">Digital certificate</h2>
                    <p class="text-muted mb-0">No certificates have been issued to your account yet. Complete your courses and evaluations to earn certificates.</p>
                </div>
            <?php endif; ?>

            <!-- Additional Certificate Cards -->
            <?php if ($others): ?>
                <div class="lms-card mt-3">
                    <h2 class="lms-card-title">All certificates</h2>
                    <div class="row g-3">
                        <?php foreach ($others as $cert): ?>
                            <div class="col-md-6">
                                <div class="cert-display" style="margin-bottom:0">
                                    <div class="cert-display-heading">Certificate of Completion</div>
                                    <div class="cert-display-title" style="font-size:.95rem"><?= Security::e($cert['course_title']) ?></div>
                                    <div class="cert-display-number"><?= Security::e($cert['display_number']) ?></div>
                                    <div class="cert-qr" style="width:40px;height:40px;font-size:.6rem">QR</div>
                                </div>
                                <div class="lms-btn-group" style="margin-top:.5rem">
                                    <a class="lms-btn lms-btn-primary" style="font-size:.78rem;padding:.35rem .7rem" href="index.php?page=download-certificate&id=<?= (int) $cert['id'] ?>">Download PDF</a>
                                    <a class="lms-btn" style="font-size:.78rem;padding:.35rem .7rem" href="index.php?page=verify-certificate&code=<?= urlencode((string) $cert['verification_code']) ?>">Verify</a>
                                    <a class="lms-btn" style="font-size:.78rem;padding:.35rem .7rem" href="index.php?page=view-certificate&id=<?= (int) $cert['id'] ?>">Preview</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- ── Completion History Sidebar ─────────────── -->
        <div>
            <div class="lms-sidebar">
                <span class="lms-sidebar-label">Completion History</span>
                <h2 class="lms-sidebar-title">Issued certificates</h2>

                <?php if ($certificates): ?>
                    <?php foreach ($certificates as $cert): ?>
                        <div class="completion-item">
                            <div class="completion-item-title"><?= Security::e($cert['course_title']) ?></div>
                            <div class="completion-item-meta">
                                <?php
                                    $issueDate = $cert['issue_date'] ?? $cert['issued_at'] ?? '';
                                    if ($issueDate) {
                                        $ts = strtotime($issueDate);
                                        echo 'Issued ' . ($ts ? date('j M Y', $ts) : Security::e($issueDate));
                                    }
                                ?>
                            </div>
                            <span class="completion-item-badge">Completed</span>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-muted small mb-0">No certificates issued yet.</p>
                <?php endif; ?>
            </div>

            <!-- Search -->
            <div class="lms-sidebar mt-3">
                <span class="lms-sidebar-label">Search</span>
                <h2 class="lms-sidebar-title">Find a certificate</h2>
                <form method="get">
                    <input type="hidden" name="page" value="trainee-certificates">
                    <input class="form-control mb-2" name="q" value="<?= Security::e($q) ?>" placeholder="Search by course or certificate no.">
                    <button class="lms-btn lms-btn-primary w-100" type="submit">Search</button>
                </form>
            </div>
        </div>
    </div>
</section>
