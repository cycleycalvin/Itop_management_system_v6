<?php
declare(strict_types=1);
use App\Core\Security;
use App\Core\View;

// Calculate Stats
$totalIssued = count($certificates);
$pendingApproval = 0;
$revokedCount = 0;
$approvedCount = 0;
foreach ($certificates as $cert) {
    if (($cert['approval_status'] ?? 'pending') === 'pending') {
        $pendingApproval++;
    } elseif (($cert['approval_status'] ?? 'pending') === 'approved') {
        $approvedCount++;
    }
    if (($cert['status'] ?? 'issued') === 'revoked') {
        $revokedCount++;
    }
}

// Default Classic Layout JSON if editing template is empty
$classicLayout = json_encode([
    'style' => [
        'template' => 'centexs_sarawak',
        'title_color' => '#000000',
        'accent_color' => '#aa3338',
        'border_color' => '#aa3338',
        'seal_color' => '#b53638',
        'pattern_opacity' => 0.05,
    ],
    'show_seal' => false,
    'show_verification' => true,
    'show_watermark' => true,
    'show_qr' => true,
    'title' => 'CERTIFICATE OF COMPLETION',
    'intro' => 'This is to certify that',
    'intro_script' => 'has successfully completed the training programme for :',
    'description' => 'Congratulations on your active participation in this program which have equipped you with valuable knowledge and skills on Artificial Intelligence (AI), Ethical Use of AI, Instructional Design Planning, Educational Data Analytics, AI for Visuals and Audio, and AI-Based Tasks.',
    'signatory_name' => 'Dato Haji Syeed Mohd Hussien Bin Wan Abd Rahman',
    'signatory_title' => 'Chief Executive Officer',
    'organization' => 'Centre for Technology Excellence Sarawak',
    'script_font' => 'Playball'
], JSON_PRETTY_PRINT);
?>

<!-- Include Google Script Font for Cursive Preview -->
<link href="https://fonts.googleapis.com/css2?family=Playball&family=Dancing+Script&family=Great+Vibes&display=swap" rel="stylesheet">

<style>
/* Local Styles for Template Designer Workspace */
.designer-workspace {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.75rem;
    align-items: start;
    margin-top: 1rem;
}
@media (max-width: 992px) {
    .designer-workspace {
        grid-template-columns: 1fr;
    }
}
.designer-form-card {
    background: var(--ims-surface);
    border: 1px solid var(--ims-border);
    border-radius: var(--ims-radius);
    padding: 1.5rem;
    box-shadow: var(--ims-shadow-sm);
}
.designer-preview-card {
    background: var(--ims-surface);
    border: 1px solid var(--ims-border);
    border-radius: var(--ims-radius);
    padding: 1.5rem;
    box-shadow: var(--ims-shadow-sm);
    position: sticky;
    top: 20px;
}

/* Certificate HTML Mockup Preview */
.cert-mock-container {
    width: 100%;
    aspect-ratio: 1 / 1.414; /* A4 Portrait Ratio */
    background: #fff;
    box-shadow: var(--ims-shadow-lg);
    position: relative;
    box-sizing: border-box;
    overflow: hidden;
}
.cert-mock-watermark {
    position: absolute;
    top: 25%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 70%;
    height: 70%;
    display: flex;
    align-items: center;
    justify-content: center;
    pointer-events: none;
    opacity: 0.08;
    z-index: 1;
}
.cert-mock-watermark img {
    width: 100%;
    height: 100%;
    object-fit: contain;
}
.cert-mock-content {
    position: relative;
    z-index: 2;
    padding-top: 10%;
    text-align: center;
}
.cert-mock-logo-wrapper {
    height: 50px;
    margin-bottom: 10px;
}
.cert-mock-logo {
    max-height: 50px;
    object-fit: contain;
}
.cert-mock-title {
    font-size: 26px;
    font-weight: 900;
    line-height: 1.1;
    color: var(--cert-title-color, #000);
    margin: 0;
}
.cert-mock-subtitle {
    font-size: 16px;
    font-weight: bold;
    margin: 0 0 10px 0;
    color: var(--cert-title-color, #000);
}
.cert-mock-intro {
    font-size: 10px;
    font-weight: bold;
    color: var(--ims-muted);
    margin: 0 0 15px 0;
}
.cert-mock-recipient {
    font-size: 14px;
    font-weight: 800;
    text-transform: uppercase;
    color: #111;
    margin: 0 0 15px 0;
}
.cert-mock-intro-script {
    font-size: 14px;
    font-family: 'Playball', cursive;
    color: var(--ims-muted);
    margin: 0 0 10px 0;
}
.cert-mock-course {
    font-size: 13px;
    font-weight: 800;
    text-transform: uppercase;
    color: #111;
    margin: 0 0 3px 0;
}
.cert-mock-course-date {
    font-size: 10px;
    font-weight: bold;
    margin: 0 0 15px 0;
}
.cert-mock-description {
    font-size: 8px;
    line-height: 1.3;
    color: var(--ims-muted);
    max-width: 85%;
    margin: 0 auto;
}
.cert-mock-footer {
    position: absolute;
    bottom: 8%;
    left: 8%;
    right: 8%;
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    z-index: 2;
}
.cert-mock-footer-left {
    width: 30%;
    text-align: left;
}
.cert-mock-footer-center {
    width: 40%;
    text-align: center;
    display: flex;
    flex-direction: column;
    align-items: center;
}
.cert-mock-footer-right {
    width: 30%;
}
.cert-mock-qr-img {
    width: 45px;
    height: 45px;
    margin-bottom: 2px;
    object-fit: contain;
}
.cert-mock-qr-label {
    font-size: 6px;
    color: var(--cert-accent, #aa3338);
    font-weight: bold;
}
.cert-mock-qr-no {
    font-size: 7px;
    font-weight: bold;
}
.cert-mock-signature-img {
    height: 35px;
    margin-bottom: 2px;
    object-fit: contain;
}
.cert-mock-signature-placeholder {
    height: 20px;
}
.cert-mock-sign-line {
    width: 140px;
    border-top: 1px solid #000;
    padding-top: 3px;
    margin-top: 3px;
    line-height: 1.2;
}
.cert-mock-sign-name {
    font-size: 8px;
    font-weight: bold;
    display: block;
}
.cert-mock-sign-title {
    font-size: 7px;
    display: block;
    color: #333;
}
.cert-mock-sign-org {
    font-size: 6px;
    display: block;
    color: #555;
}
</style>

<div class="pm-container">

    <!-- ── Breadcrumb ── -->
    <nav class="pm-breadcrumb" aria-label="breadcrumb">
        <a href="index.php?page=admin-dashboard">Dashboard</a>
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
        <span>Certificate Management</span>
    </nav>

    <!-- Header -->
    <div class="pm-header">
        <div>
            <h1 class="pm-title">Certificate Center</h1>
            <p class="pm-subtitle">Manage, customize templates, and bulk-issue certified achievements.</p>
        </div>
    </div>

    <!-- Stats summary widgets -->
    <div class="pm-stats">
        <div class="pm-stat-card">
            <div class="pm-stat-icon pm-stat-icon-all" style="background: var(--ims-primary-light); color: var(--ims-primary);">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="7"/><polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"/></svg>
            </div>
            <div class="pm-stat-info">
                <span class="pm-stat-value"><?= $totalIssued ?></span>
                <span class="pm-stat-label">Total Issued</span>
            </div>
        </div>
        <div class="pm-stat-card">
            <div class="pm-stat-icon pm-stat-icon-active" style="background: var(--ims-warning-light); color: var(--ims-warning);">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            </div>
            <div class="pm-stat-info">
                <span class="pm-stat-value"><?= $pendingApproval ?></span>
                <span class="pm-stat-label">Pending Approval</span>
            </div>
        </div>
        <div class="pm-stat-card">
            <div class="pm-stat-icon pm-stat-icon-completed" style="background: var(--ims-success-light); color: var(--ims-success);">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            </div>
            <div class="pm-stat-info">
                <span class="pm-stat-value"><?= $approvedCount ?></span>
                <span class="pm-stat-label">Approved & Active</span>
            </div>
        </div>
        <div class="pm-stat-card">
            <div class="pm-stat-icon pm-stat-icon-all" style="background: var(--ims-danger-light); color: var(--ims-danger);">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </div>
            <div class="pm-stat-info">
                <span class="pm-stat-value"><?= $revokedCount ?></span>
                <span class="pm-stat-label">Revoked</span>
            </div>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="pm-toolbar">
        <div class="pm-toolbar-row">
            <div class="pm-tabs" role="tablist" style="margin-bottom:0;">
                <button class="pm-tab pm-tab-active" id="tabIssued" onclick="switchSection('issued')">
                    Issued Certificates
                    <span class="pm-tab-badge"><?= $totalIssued ?></span>
                </button>
                <button class="pm-tab" id="tabTemplates" onclick="switchSection('templates')">
                    Templates Designer
                    <span class="pm-tab-badge"><?= count($templates) ?></span>
                </button>
                <button class="pm-tab" id="tabIssue" onclick="switchSection('issue')">
                    Issuance Center
                </button>
            </div>
        </div>
    </div>

    <!-- ═══════════════ SECTION 1: ISSUED CERTIFICATES ═══════════════ -->
    <div class="pm-view-section pm-view-section-active" id="sectionIssued">
        <!-- Search bar -->
        <div class="pm-toolbar mb-3">
            <form class="pm-filters w-100" method="get">
                <input type="hidden" name="page" value="admin-certificates">
                <div class="pm-search-box flex-grow-1">
                    <svg class="pm-search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    <input class="pm-search-input" name="q" value="<?= Security::e($q) ?>" placeholder="Search certificates by number, trainee or course title…">
                </div>
                <button class="pm-btn pm-btn-secondary" type="submit">Search</button>
                <?php if ($q): ?>
                    <a href="index.php?page=admin-certificates" class="pm-btn pm-btn-ghost">Clear</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="pm-table-container">
            <table class="pm-table">
                <thead>
                    <tr>
                        <th>Certificate Details</th>
                        <th>Recipient Details</th>
                        <th>Course</th>
                        <th>Approval Status</th>
                        <th>Issuance Status</th>
                        <th>Issued Date</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($certificates)): ?>
                        <tr><td colspan="7" class="text-center text-muted py-4">No certificate records found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($certificates as $cert): ?>
                            <tr class="align-middle" style="cursor: pointer;" onclick="openCertDrawer(<?= (int) $cert['id'] ?>, '<?= Security::e($cert['display_number']) ?>', '<?= Security::e($cert['trainee_name']) ?>', '<?= Security::e($cert['course_title']) ?>', '<?= Security::e($cert['verification_code']) ?>', '<?= Security::e($cert['approval_status'] ?? 'pending') ?>')">
                                <td>
                                    <strong style="display:block;"><?= Security::e($cert['display_number']) ?></strong>
                                    <span class="text-muted small">Template: <?= Security::e($cert['template_name'] ?? 'Classic') ?></span>
                                </td>
                                <td>
                                    <div class="pm-table-avatar-cell">
                                        <div class="pm-avatar-small"><?= strtoupper(substr($cert['trainee_name'] ?? 'T', 0, 1)) ?></div>
                                        <div>
                                            <strong style="display:block;"><?= Security::e($cert['trainee_name']) ?></strong>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="small font-weight-bold"><?= Security::e($cert['course_title']) ?></span></td>
                                <td>
                                    <?php $appStat = $cert['approval_status'] ?? 'pending'; ?>
                                    <span class="badge bg-<?= $appStat === 'approved' ? 'success' : ($appStat === 'rejected' ? 'danger' : 'warning text-dark') ?>">
                                        <?= Security::e(ucfirst($appStat)) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php $issStat = $cert['status'] ?? 'issued'; ?>
                                    <span class="badge bg-<?= $issStat === 'issued' ? 'info' : ($issStat === 'reissued' ? 'primary' : 'secondary') ?>">
                                        <?= Security::e(ucfirst($issStat)) ?>
                                    </span>
                                </td>
                                <td class="text-muted small"><?= Security::e($cert['issue_date'] ?? $cert['issued_at']) ?></td>
                                <td>
                                    <div class="d-flex justify-content-end gap-2" onclick="event.stopPropagation()">
                                        <a class="pm-btn pm-btn-secondary py-1 px-2 small" style="font-size: .75rem;" href="index.php?page=view-certificate&id=<?= (int) $cert['id'] ?>" target="_blank">Preview</a>
                                        
                                        <?php if ($appStat === 'pending'): ?>
                                            <form method="post" action="index.php?page=review-certificate" style="display:inline;">
                                                <input type="hidden" name="_csrf" value="<?= Security::e(Security::csrfToken()) ?>">
                                                <input type="hidden" name="id" value="<?= (int) $cert['id'] ?>">
                                                <input type="hidden" name="approval_status" value="approved">
                                                <button class="pm-btn pm-btn-primary py-1 px-2 small" style="font-size: .75rem; background: var(--ims-success); border:none;">Approve</button>
                                            </form>
                                            <form method="post" action="index.php?page=review-certificate" style="display:inline;">
                                                <input type="hidden" name="_csrf" value="<?= Security::e(Security::csrfToken()) ?>">
                                                <input type="hidden" name="id" value="<?= (int) $cert['id'] ?>">
                                                <input type="hidden" name="approval_status" value="rejected">
                                                <input type="hidden" name="remarks" value="Rejected by admin.">
                                                <button class="pm-btn pm-btn-secondary py-1 px-2 small" style="font-size: .75rem; color: var(--ims-danger); border-color: var(--ims-danger-light);">Reject</button>
                                            </form>
                                        <?php endif; ?>

                                        <?php if ($issStat !== 'revoked'): ?>
                                            <form method="post" action="index.php?page=admin-revoke-certificate" style="display:inline;" onsubmit="return confirm('Are you sure you want to revoke this certificate?');">
                                                <input type="hidden" name="_csrf" value="<?= Security::e(Security::csrfToken()) ?>">
                                                <input type="hidden" name="id" value="<?= (int) $cert['id'] ?>">
                                                <button class="pm-btn pm-btn-secondary py-1 px-2 small" style="font-size: .75rem; border-color: #ffd8d8; color: var(--ims-danger);">Revoke</button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- ═══════════════ SECTION 2: TEMPLATES DESIGNER ═══════════════ -->
    <div class="pm-view-section" id="sectionTemplates">
        
        <?php if ($editingTemplate): ?>
            <!-- Two Column Live Workspace Editor -->
            <div class="designer-workspace">
                
                <!-- Left Pane: Fields Form -->
                <div class="designer-form-card">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h2 class="h5 m-0">Edit Layout Parameters</h2>
                        <a href="index.php?page=admin-certificates&tab=templates" class="pm-btn pm-btn-ghost py-1 px-2 small">Back to List</a>
                    </div>
                    
                    <form method="post" action="index.php?page=save-certificate-template" enctype="multipart/form-data" id="templateDesignForm">
                        <input type="hidden" name="_csrf" value="<?= Security::e(Security::csrfToken()) ?>">
                        <input type="hidden" name="template_id" value="<?= (int) $editingTemplate['template_id'] ?>">
                        
                        <?php
                            $layout = [];
                            if (!empty($editingTemplate['layout_json'])) {
                                $layout = json_decode((string) $editingTemplate['layout_json'], true) ?: [];
                            }
                            $style = $layout['style'] ?? [];
                        ?>

                        <div class="mb-3">
                            <label class="form-label small font-weight-bold">Template Name</label>
                            <input class="form-control" name="template_name" id="fieldTemplateName" value="<?= Security::e($editingTemplate['template_name']) ?>" required>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-md-6">
                                <label class="form-label small font-weight-bold">Font Family</label>
                                <select class="form-select" name="font_family" id="fieldFontFamily">
                                    <option value="Arial, sans-serif" <?= ($editingTemplate['font_family'] ?? '') === 'Arial, sans-serif' ? 'selected' : '' ?>>Arial</option>
                                    <option value="'Inter', sans-serif" <?= ($editingTemplate['font_family'] ?? '') === "'Inter', sans-serif" ? 'selected' : '' ?>>Inter</option>
                                    <option value="'Georgia', serif" <?= ($editingTemplate['font_family'] ?? '') === "'Georgia', serif" ? 'selected' : '' ?>>Georgia</option>
                                    <option value="'Times New Roman', serif" <?= ($editingTemplate['font_family'] ?? '') === "'Times New Roman', serif" ? 'selected' : '' ?>>Times</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small font-weight-bold">Base Font Size (px)</label>
                                <input class="form-control" type="number" name="font_size" id="fieldFontSize" value="<?= (int) ($editingTemplate['font_size'] ?? 28) ?>">
                            </div>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-md-4">
                                <label class="form-label small font-weight-bold">Text Color</label>
                                <input class="form-control form-control-color w-100" type="color" name="text_color" id="fieldTextColor" value="<?= Security::e($editingTemplate['text_color'] ?? '#182230') ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small font-weight-bold">Accent Color</label>
                                <input class="form-control form-control-color w-100" type="color" name="accent_color" id="fieldAccentColor" value="<?= Security::e($style['accent_color'] ?? '#aa3338') ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small font-weight-bold">Border Color</label>
                                <input class="form-control form-control-color w-100" type="color" name="border_color" id="fieldBorderColor" value="<?= Security::e($style['border_color'] ?? '#aa3338') ?>">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small font-weight-bold">Certificate Title</label>
                            <input class="form-control" name="title" id="fieldTitle" value="<?= Security::e($layout['title'] ?? 'CERTIFICATE OF COMPLETION') ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label small font-weight-bold">Recipient Intro text</label>
                            <input class="form-control" name="intro" id="fieldIntro" value="<?= Security::e($layout['intro'] ?? 'This is to certify that') ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label small font-weight-bold">Cursive/Script Intro text</label>
                            <input class="form-control" name="intro_script" id="fieldIntroScript" value="<?= Security::e($layout['intro_script'] ?? 'has successfully completed the training programme for :') ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label small font-weight-bold">Congratulations / Description text</label>
                            <textarea class="form-control" name="description" id="fieldDescription" rows="4" style="font-size: .8rem;"><?= Security::e($layout['description'] ?? '') ?></textarea>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-md-6">
                                <label class="form-label small font-weight-bold">Signatory Name</label>
                                <input class="form-control" name="signatory_name" id="fieldSignatoryName" value="<?= Security::e($layout['signatory_name'] ?? 'Dato Haji Syeed Mohd Hussien Bin Wan Abd Rahman') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small font-weight-bold">Signatory Title</label>
                                <input class="form-control" name="signatory_title" id="fieldSignatoryTitle" value="<?= Security::e($layout['signatory_title'] ?? 'Chief Executive Officer') ?>">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small font-weight-bold">Organization Name</label>
                            <input class="form-control" name="organization" id="fieldOrganization" value="<?= Security::e($layout['organization'] ?? 'Centre for Technology Excellence Sarawak') ?>">
                        </div>

                        <!-- Graphic Upload Fields -->
                        <div class="row g-2 mb-3">
                            <div class="col-md-6">
                                <label class="form-label small font-weight-bold">Logo Graphic</label>
                                <input class="form-control form-control-sm" type="file" name="logo" id="fileLogo" accept=".png,.jpg,.jpeg,.webp">
                                <?php if (!empty($editingTemplate['logo'])): ?>
                                    <span class="text-muted small d-block mt-1">Current: <?= Security::e($editingTemplate['logo']) ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small font-weight-bold">Signature Graphic</label>
                                <input class="form-control form-control-sm" type="file" name="signature" id="fileSignature" accept=".png,.jpg,.jpeg,.webp">
                                <?php if (!empty($editingTemplate['signature'])): ?>
                                    <span class="text-muted small d-block mt-1">Current: <?= Security::e($editingTemplate['signature']) ?></span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small font-weight-bold">Background / Watermark Graphic</label>
                            <input class="form-control form-control-sm" type="file" name="background_image" id="fileBackground" accept=".png,.jpg,.jpeg,.webp">
                            <?php if (!empty($editingTemplate['background_image'])): ?>
                                <span class="text-muted small d-block mt-1">Current: <?= Security::e($editingTemplate['background_image']) ?></span>
                            <?php endif; ?>
                        </div>

                        <!-- Checkbox Controls -->
                        <div class="d-flex gap-4 mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="show_watermark" id="fieldShowWatermark" value="1" <?= ($layout['show_watermark'] ?? true) ? 'checked' : '' ?>>
                                <label class="form-check-label small" for="fieldShowWatermark">Watermark Background</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="show_qr" id="fieldShowQr" value="1" <?= ($layout['show_qr'] ?? true) ? 'checked' : '' ?>>
                                <label class="form-check-label small" for="fieldShowQr">QR Verification Code</label>
                            </div>
                        </div>

                        <!-- HIDDEN REAL layout_json to be updated by script on submit -->
                        <textarea name="layout_json" id="realLayoutJson" style="display:none;"></textarea>
                        <select class="form-select mb-3" name="status">
                            <option value="active" <?= ($editingTemplate['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Active Status</option>
                            <option value="inactive" <?= ($editingTemplate['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive Status</option>
                        </select>

                        <button class="pm-btn pm-btn-primary w-100" type="submit" onclick="assembleLayoutJson()">Save Template Changes</button>
                    </form>
                </div>

                <!-- Right Pane: Live Interactive HTML Preview -->
                <div class="designer-preview-card">
                    <h2 class="h5 mb-3">Live Certificate Preview</h2>
                    
                    <div class="cert-mock-container" id="certMockContainer">
                        <!-- Watermark -->
                        <div class="cert-mock-watermark" id="mockWatermark" style="display: <?= ($layout['show_watermark'] ?? true) ? 'flex' : 'none' ?>;">
                            <?php if (!empty($editingTemplate['background_image'])): ?>
                                <img src="storage/uploads/<?= Security::e($editingTemplate['background_image']) ?>" alt="" id="mockWatermarkImg">
                            <?php else: ?>
                                <img src="<?= APP_URL ?>/public/assets/img/centexs-logo-with-outline-1.png" alt="" id="mockWatermarkImg">
                            <?php endif; ?>
                        </div>
                        
                        <div class="cert-mock-content">
                            <!-- Logo -->
                            <div class="cert-mock-logo-wrapper">
                                <?php if (!empty($editingTemplate['logo'])): ?>
                                    <img src="storage/uploads/<?= Security::e($editingTemplate['logo']) ?>" class="cert-mock-logo" id="mockLogo" alt="">
                                <?php else: ?>
                                    <img src="<?= APP_URL ?>/public/assets/img/centexs-logo-with-outline-1.png" class="cert-mock-logo" id="mockLogo" alt="">
                                <?php endif; ?>
                            </div>
                            
                            <!-- Title -->
                            <div class="cert-mock-title" id="mockTitle"><?= Security::e($layout['title'] ?? 'CERTIFICATE') ?></div>
                            <div class="cert-mock-subtitle">OF COMPLETION</div>
                            
                            <!-- Intro -->
                            <div class="cert-mock-intro" id="mockIntro"><?= Security::e($layout['intro'] ?? 'This is to certify that') ?></div>
                            
                            <!-- Recipient placeholder -->
                            <div class="cert-mock-recipient">NORAIDAH BINTI MOHAMAD</div>
                            
                            <!-- Script Intro -->
                            <div class="cert-mock-intro-script" id="mockIntroScript"><?= Security::e($layout['intro_script'] ?? 'has successfully completed the training programme for :') ?></div>
                            
                            <!-- Course placeholder -->
                            <div class="cert-mock-course">CELIK AI : MEMACU PENDIDIKAN</div>
                            <div class="cert-mock-course-date">(22 NOVEMBER 2025)</div>
                            
                            <!-- Description text -->
                            <div class="cert-mock-description" id="mockDescription"><?= Security::e($layout['description'] ?? 'Congratulations on your active participation in this program...') ?></div>
                        </div>
                            
                        <!-- Footer -->
                        <div class="cert-mock-footer">
                            <div class="cert-mock-footer-left">
                                <div id="mockQrWrap" style="display: <?= ($layout['show_qr'] ?? true) ? 'block' : 'none' ?>;">
                                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=120x120&data=example" class="cert-mock-qr-img" alt="">
                                    <div class="cert-mock-qr-label" id="mockQrLabel">Certificate No.</div>
                                    <div class="cert-mock-qr-no">ITOP-YYYY-0000</div>
                                </div>
                            </div>
                            
                            <div class="cert-mock-footer-center">
                                <?php if (!empty($editingTemplate['signature'])): ?>
                                    <img src="storage/uploads/<?= Security::e($editingTemplate['signature']) ?>" class="cert-mock-signature-img" id="mockSignature" alt="">
                                <?php else: ?>
                                    <div class="cert-mock-signature-placeholder"></div>
                                <?php endif; ?>
                                
                                <div class="cert-mock-sign-line">
                                    <span class="cert-mock-sign-name" id="mockSignatoryName"><?= Security::e($layout['signatory_name'] ?? 'Dato Haji Syeed') ?></span>
                                    <span class="cert-mock-sign-title" id="mockSignatoryTitle"><?= Security::e($layout['signatory_title'] ?? 'CEO') ?></span>
                                    <span class="cert-mock-sign-org" id="mockOrganization"><?= Security::e($layout['organization'] ?? 'CENTEXS') ?></span>
                                </div>
                            </div>
                            
                            <div class="cert-mock-footer-right"></div>
                        </div>
                    </div>
                </div>
            </div>
            
        <?php else: ?>
            <!-- Templates Card Grid View -->
            <div class="row g-4 mt-2">
                <!-- Add new template card -->
                <div class="col-md-4">
                    <div class="card h-100 border-dashed" style="border: 2px dashed var(--ims-border); cursor: pointer;" onclick="createNewTemplatePreset()">
                        <div class="card-body d-flex flex-column align-items-center justify-content-center py-5">
                            <div class="pm-stat-icon pm-stat-icon-all mb-3" style="background: var(--ims-primary-light); color: var(--ims-primary);">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                            </div>
                            <h3 class="h6 font-weight-bold">Create New Template</h3>
                            <p class="text-muted small text-center px-3 m-0">Setup layout colors, text placeholders, and signatory branding</p>
                        </div>
                    </div>
                </div>
                
                <?php foreach ($templates as $template): ?>
                    <div class="col-md-4">
                        <div class="card h-100">
                            <div class="card-body d-flex flex-column justify-content-between">
                                <div>
                                    <h3 class="h6 font-weight-bold text-truncate mb-2"><?= Security::e($template['template_name']) ?></h3>
                                    <span class="badge bg-<?= $template['status'] === 'active' ? 'success' : 'secondary' ?> mb-3">
                                        <?= Security::e(ucfirst($template['status'])) ?>
                                    </span>
                                    
                                    <!-- Render mini mock block -->
                                    <div class="bg-light border rounded p-2 text-center text-muted small mb-3" style="font-size: .7rem; height: 110px; display:flex; flex-direction:column; justify-content:center;">
                                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="mx-auto mb-2"><circle cx="12" cy="8" r="7"/><polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"/></svg>
                                        <span>Font Family: <?= Security::e($template['font_family'] ?? 'Arial') ?></span>
                                        <span>Text Color: <?= Security::e($template['text_color']) ?></span>
                                    </div>
                                </div>
                                
                                <div class="d-flex gap-2">
                                    <a class="pm-btn pm-btn-secondary py-1 px-3 small flex-grow-1 text-center justify-content-center" href="index.php?page=admin-certificates&template=<?= (int) $template['template_id'] ?>&tab=templates">Edit Design</a>
                                    <form method="post" action="index.php?page=delete-certificate-template" onsubmit="return confirm('Are you sure you want to delete this template?');">
                                        <input type="hidden" name="_csrf" value="<?= Security::e(Security::csrfToken()) ?>">
                                        <input type="hidden" name="template_id" value="<?= (int) $template['template_id'] ?>">
                                        <button class="pm-btn pm-btn-ghost py-1 px-2 text-danger" title="Delete Template" style="border:none;">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Hidden form to trigger template creation -->
            <form method="post" action="index.php?page=save-certificate-template" id="createTemplatePresetForm" style="display:none;">
                <input type="hidden" name="_csrf" value="<?= Security::e(Security::csrfToken()) ?>">
                <input type="hidden" name="template_name" value="New Certificate Template Preset">
                <input type="hidden" name="font_family" value="Inter, sans-serif">
                <input type="hidden" name="font_size" value="28">
                <input type="hidden" name="text_color" value="#182230">
                <input type="hidden" name="layout_json" value="<?= Security::e($classicLayout) ?>">
                <input type="hidden" name="status" value="active">
            </form>
        <?php endif; ?>
    </div>

    <!-- ═══════════════ SECTION 3: ISSUANCE CENTER ═══════════════ -->
    <div class="pm-view-section" id="sectionIssue">
        <div class="row g-4 mt-2">
            
            <!-- Column 1: Individual Issuance -->
            <div class="col-md-5">
                <div class="panel h-100">
                    <h2 class="h5 mb-3">Issue Individual Certificate</h2>
                    <form method="post" action="index.php?page=issue-certificate">
                        <input type="hidden" name="_csrf" value="<?= Security::e(Security::csrfToken()) ?>">
                        
                        <div class="mb-3">
                            <label class="form-label small font-weight-bold">Trainee</label>
                            <select class="form-select" name="trainee_id" required>
                                <option value="">Select Trainee</option>
                                <?php foreach ($trainees as $t): ?>
                                    <option value="<?= (int) $t['id'] ?>"><?= Security::e($t['name']) ?> (<?= Security::e($t['email']) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label small font-weight-bold">Course / Programme</label>
                            <select class="form-select" name="course_id" required>
                                <option value="">Select Course</option>
                                <?php foreach ($courses as $c): ?>
                                    <option value="<?= (int) $c['id'] ?>"><?= Security::e($c['title']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label small font-weight-bold">Branding Template</label>
                            <select class="form-select" name="template_id" required>
                                <option value="">Select Template</option>
                                <?php foreach ($activeTemplates as $t): ?>
                                    <option value="<?= (int) $t['template_id'] ?>"><?= Security::e($t['template_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small font-weight-bold">Certificate Number (Optional)</label>
                            <input class="form-control" name="certificate_number" placeholder="ITOP-YYYY-XXXX (Auto if blank)">
                        </div>

                        <div class="mb-3">
                            <label class="form-label small font-weight-bold">Verification Code (Optional)</label>
                            <input class="form-control" name="verification_code" placeholder="Auto-generated if blank">
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-md-6">
                                <label class="form-label small font-weight-bold">Issue Date</label>
                                <input class="form-control" type="date" name="issue_date" value="<?= date('Y-m-d') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small font-weight-bold">Status</label>
                                <select class="form-select" name="status">
                                    <option value="issued">Issued</option>
                                    <option value="reissued">Reissued</option>
                                </select>
                            </div>
                        </div>

                        <button class="pm-btn pm-btn-primary w-100 justify-content-center" type="submit">Issue Certificate</button>
                    </form>
                </div>
            </div>

            <!-- Column 2: Bulk Issuance Utility -->
            <div class="col-md-7">
                <div class="panel h-100">
                    <h2 class="h5 m-0">Bulk Certificates Issuance</h2>
                    <p class="text-muted small mb-3">Select a course to view all trainees who have met completion requirements but haven't received certificates.</p>
                    
                    <form method="post" action="index.php?page=admin-bulk-issue-certificates">
                        <input type="hidden" name="_csrf" value="<?= Security::e(Security::csrfToken()) ?>">
                        
                        <div class="row g-2 mb-3">
                            <div class="col-md-6">
                                <label class="form-label small font-weight-bold">Course / Intake</label>
                                <select class="form-select" name="course_id" id="bulkCourseSelect" onchange="fetchEligibleTrainees()" required>
                                    <option value="">Choose Course</option>
                                    <?php foreach ($courses as $c): ?>
                                        <option value="<?= (int) $c['id'] ?>"><?= Security::e($c['title']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small font-weight-bold">Select Template</label>
                                <select class="form-select" name="template_id" required>
                                    <option value="">Choose Template</option>
                                    <?php foreach ($activeTemplates as $t): ?>
                                        <option value="<?= (int) $t['template_id'] ?>"><?= Security::e($t['template_name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <!-- Eligible Trainees List Container -->
                        <div class="mb-4">
                            <label class="form-label small font-weight-bold d-flex justify-content-between">
                                <span>Eligible Trainees Checklist</span>
                                <span id="eligibleCountLabel" class="text-muted font-weight-normal">(0 eligible)</span>
                            </label>
                            
                            <div class="border rounded p-3 bg-light" style="max-height: 250px; overflow-y: auto;" id="traineesListWrapper">
                                <p class="text-muted small italic text-center m-0 py-3">Please choose a course above to pull records.</p>
                            </div>
                        </div>

                        <button class="pm-btn pm-btn-primary w-100 justify-content-center" type="submit" id="bulkIssueSubmitBtn" disabled>
                            Bulk Issue Certificates
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </div>

</div>

<!-- ════════════════════════════════════════════════════
     CERTIFICATE AUDIT DRAWER — Slide-in Drawer Modal
     ════════════════════════════════════════════════════ -->
<div class="pm-panel-overlay" id="certPanelOverlay"></div>
<div class="pm-detail-panel" id="certDetailPanel">
    <div class="pm-panel-header">
        <h3 class="pm-panel-title">Certificate Audit Ledger</h3>
        <button class="pm-panel-close" id="certPanelClose" type="button" aria-label="Close">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
    </div>
    <div class="pm-panel-body" id="certPanelBody">
        <!-- Loading spinner -->
        <div class="pm-panel-loading" id="certPanelLoading" style="display:none;">
            <div class="pm-spinner"></div>
            <span>Fetching verification history ledger…</span>
        </div>
        
        <!-- Enriched Content Drawer -->
        <div class="pm-panel-content" id="certPanelContent">
            <!-- Header Identity Card -->
            <div class="pm-drawer-profile-card">
                <div class="pm-avatar-large" style="background: var(--ims-primary-light); color: var(--ims-primary); font-weight:800; display:flex; align-items:center; justify-content:center;" id="drCertIcon">C</div>
                <div>
                    <h4 class="pm-drawer-name" id="drCertNo">ITOP-YYYY-0000</h4>
                    <p class="pm-drawer-email" id="drTraineeName">Recipient Trainee</p>
                </div>
            </div>

            <!-- Meta details lists -->
            <div class="cm-detail-section">
                <h4 class="pm-drawer-section-title">Certificate Records</h4>
                <div class="cm-detail-row">
                    <span class="cm-detail-key">Course Title:</span>
                    <span class="cm-detail-value font-weight-bold" id="drCourseTitle">—</span>
                </div>
                <div class="cm-detail-row">
                    <span class="cm-detail-key">Verification Code:</span>
                    <span class="cm-detail-value" style="font-family: monospace; font-size: .8rem;" id="drVerificationCode">—</span>
                </div>
                <div class="cm-detail-row">
                    <span class="cm-detail-key">Public Verify URL:</span>
                    <span class="cm-detail-value">
                        <a href="" target="_blank" class="small text-decoration-none" id="drVerifyLink">Open Link ↗</a>
                    </span>
                </div>
            </div>

            <!-- Download Audit Log logs list -->
            <div class="cm-detail-section">
                <h4 class="pm-drawer-section-title">Download & Access Audit Trail</h4>
                <div class="pm-drawer-list" id="drLogsList" style="max-height: 300px; overflow-y: auto;">
                    <!-- Dynamic rows populate here -->
                </div>
            </div>
            
            <div class="mt-auto pt-3 d-flex gap-2">
                <a href="" target="_blank" class="pm-btn pm-btn-secondary w-50 justify-content-center" id="drDownloadWordBtn">
                    Download Word (.doc)
                </a>
                <a href="" target="_blank" class="pm-btn pm-btn-primary w-50 justify-content-center" id="drDownloadBtn">
                    Download Official PDF
                </a>
            </div>
        </div>
    </div>
</div>

<script>
// Switch Tabs Section view
function switchSection(sectionName) {
    document.querySelectorAll('.pm-view-section').forEach(s => {
        s.classList.remove('pm-view-section-active');
    });
    document.querySelectorAll('.pm-tabs .pm-tab').forEach(t => {
        t.classList.remove('pm-tab-active');
    });
    
    if (sectionName === 'issued') {
        document.getElementById('sectionIssued').classList.add('pm-view-section-active');
        document.getElementById('tabIssued').classList.add('pm-tab-active');
    } else if (sectionName === 'templates') {
        document.getElementById('sectionTemplates').classList.add('pm-view-section-active');
        document.getElementById('tabTemplates').classList.add('pm-tab-active');
    } else if (sectionName === 'issue') {
        document.getElementById('sectionIssue').classList.add('pm-view-section-active');
        document.getElementById('tabIssue').classList.add('pm-tab-active');
    }
}

// Ensure active tab matches query param if present
document.addEventListener('DOMContentLoaded', () => {
    const urlParams = new URLSearchParams(window.location.search);
    const activeTab = urlParams.get('tab');
    if (activeTab) {
        switchSection(activeTab);
    }
});

// Trigger Template Preset Creation
function createNewTemplatePreset() {
    document.getElementById('createTemplatePresetForm').submit();
}

// Fetch Trainees Completed Course without certificate
function fetchEligibleTrainees() {
    const courseId = document.getElementById('bulkCourseSelect').value;
    const wrapper = document.getElementById('traineesListWrapper');
    const submitBtn = document.getElementById('bulkIssueSubmitBtn');
    const labelCount = document.getElementById('eligibleCountLabel');
    
    if (!courseId) {
        wrapper.innerHTML = '<p class="text-muted small italic text-center m-0 py-3">Please choose a course above to pull records.</p>';
        submitBtn.disabled = true;
        labelCount.textContent = '(0 eligible)';
        return;
    }
    
    wrapper.innerHTML = '<div class="text-center py-3"><div class="spinner-border spinner-border-sm text-primary" role="status"></div><span class="small text-muted ms-2">Retrieving completion ledger…</span></div>';
    
    fetch('index.php?page=admin-certificates&get_completed_course_id=' + courseId)
        .then(res => res.json())
        .then(data => {
            wrapper.innerHTML = '';
            if (data.length === 0) {
                wrapper.innerHTML = '<p class="text-center text-muted small py-3 m-0">No eligible trainees found (everyone is certified or requirements not met).</p>';
                submitBtn.disabled = true;
                labelCount.textContent = '(0 eligible)';
                return;
            }
            
            // Add Toggle All
            const selectAllDiv = document.createElement('div');
            selectAllDiv.className = 'form-check mb-2 pb-2 border-bottom';
            selectAllDiv.innerHTML = `<input class="form-check-input" type="checkbox" id="selectAllTrainees" checked onchange="toggleSelectAllTrainees(this)"><label class="form-check-label small font-weight-bold" for="selectAllTrainees">Select All Trainees</label>`;
            wrapper.appendChild(selectAllDiv);
            
            data.forEach(t => {
                const item = document.createElement('div');
                item.className = 'form-check mb-2';
                item.innerHTML = `
                    <input class="form-check-input trainee-bulk-check" type="checkbox" name="trainee_ids[]" value="${t.id}" id="bulk_t_${t.id}" checked>
                    <label class="form-check-label small" for="bulk_t_${t.id}">
                        <strong>${escapeHtml(t.name)}</strong> <span class="text-muted">(${escapeHtml(t.email)})</span>
                    </label>
                `;
                wrapper.appendChild(item);
            });
            
            submitBtn.disabled = false;
            labelCount.textContent = `(${data.length} eligible)`;
        })
        .catch(err => {
            wrapper.innerHTML = '<p class="text-danger small py-3 m-0">Error fetching eligible trainees checklist.</p>';
        });
}

function toggleSelectAllTrainees(master) {
    document.querySelectorAll('.trainee-bulk-check').forEach(chk => {
        chk.checked = master.checked;
    });
}

function escapeHtml(str) {
    if (!str) return '';
    return str.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
}

// Assembly layout JSON before saving template
function assembleLayoutJson() {
    const layout = {
        style: {
            template: 'centexs_sarawak',
            title_color: document.getElementById('fieldTextColor').value,
            accent_color: document.getElementById('fieldAccentColor').value,
            border_color: document.getElementById('fieldBorderColor').value,
            seal_color: '#b53638',
            pattern_opacity: 0.05
        },
        show_seal: false,
        show_verification: true,
        show_watermark: document.getElementById('fieldShowWatermark').checked,
        show_qr: document.getElementById('fieldShowQr').checked,
        title: document.getElementById('fieldTitle').value,
        intro: document.getElementById('fieldIntro').value,
        intro_script: document.getElementById('fieldIntroScript').value,
        description: document.getElementById('fieldDescription').value,
        signatory_name: document.getElementById('fieldSignatoryName').value,
        signatory_title: document.getElementById('fieldSignatoryTitle').value,
        organization: document.getElementById('fieldOrganization').value,
        script_font: 'Playball'
    };
    document.getElementById('realLayoutJson').value = JSON.stringify(layout, null, 4);
}

// Drawer audit overlay trigger
const certOverlay = document.getElementById('certPanelOverlay');
const certPanel = document.getElementById('certDetailPanel');
const certLoading = document.getElementById('certPanelLoading');
const certContent = document.getElementById('certPanelContent');
const certClose = document.getElementById('certPanelClose');

if (certOverlay) document.body.appendChild(certOverlay);
if (certPanel) document.body.appendChild(certPanel);

function closeCertDrawer() {
    certPanel.classList.remove('pm-panel-open');
    certOverlay.classList.remove('pm-panel-overlay-active');
}
if (certClose) certClose.addEventListener('click', closeCertDrawer);
if (certOverlay) certOverlay.addEventListener('click', closeCertDrawer);

function openCertDrawer(id, number, trainee, course, code, status) {
    certOverlay.classList.add('pm-panel-overlay-active');
    certPanel.classList.add('pm-panel-open');
    certLoading.style.display = 'block';
    certContent.style.display = 'none';

    document.getElementById('drCertNo').textContent = number;
    document.getElementById('drTraineeName').textContent = trainee;
    document.getElementById('drCourseTitle').textContent = course;
    document.getElementById('drVerificationCode').textContent = code;
    document.getElementById('drVerifyLink').href = 'index.php?page=verify-certificate&code=' + encodeURIComponent(code);
    document.getElementById('drDownloadBtn').href = 'index.php?page=download-certificate&id=' + id;
    document.getElementById('drDownloadWordBtn').href = 'index.php?page=download-certificate-word&id=' + id;
    
    // Fetch logs
    fetch('index.php?page=admin-certificate-logs&id=' + id)
        .then(res => res.json())
        .then(data => {
            const list = document.getElementById('drLogsList');
            list.innerHTML = '';
            
            if (data.status === 'success' && data.logs && data.logs.length > 0) {
                data.logs.forEach(log => {
                    const row = document.createElement('div');
                    row.className = 'pm-drawer-item';
                    row.innerHTML = `
                        <div>
                            <strong style="display:block; font-size: .8rem;">${escapeHtml(log.user_name)}</strong>
                            <span class="text-muted small">${escapeHtml(log.user_email)} | IP: ${escapeHtml(log.ip_address || '—')}</span>
                        </div>
                        <span class="small text-muted">${escapeHtml(log.downloaded_at)}</span>
                    `;
                    list.appendChild(row);
                });
            } else {
                list.innerHTML = '<p class="text-muted small italic text-center m-0 py-3">No downloads or access records found yet.</p>';
            }
            
            certLoading.style.display = 'none';
            certContent.style.display = 'block';
        })
        .catch(err => {
            certLoading.style.display = 'none';
            certContent.style.display = 'block';
            document.getElementById('drLogsList').innerHTML = '<p class="text-danger small text-center m-0 py-3">Error fetching download logs.</p>';
        });
}

// Live interactive Preview binding
if (document.getElementById('templateDesignForm')) {
    const bindLivePreview = () => {
        // Text Fields
        const fTitle = document.getElementById('fieldTitle');
        const fIntro = document.getElementById('fieldIntro');
        const fIntroScript = document.getElementById('fieldIntroScript');
        const fDesc = document.getElementById('fieldDescription');
        const fSignName = document.getElementById('fieldSignatoryName');
        const fSignTitle = document.getElementById('fieldSignatoryTitle');
        
        const mTitle = document.getElementById('mockTitle');
        const mIntro = document.getElementById('mockIntro');
        const mIntroScript = document.getElementById('mockIntroScript');
        const mDesc = document.getElementById('mockDescription');
        const mSignName = document.getElementById('mockSignatoryName');
        const mSignTitle = document.getElementById('mockSignatoryTitle');
        
        // Colors
        const fTextCol = document.getElementById('fieldTextColor');
        const fAccentCol = document.getElementById('fieldAccentColor');
        const fBorderCol = document.getElementById('fieldBorderColor');
        
        const mBorder = document.getElementById('mockBorder');
        const mInnerBorder = document.getElementById('mockInnerBorder');
        const mMockContainer = document.getElementById('certMockContainer');
        
        // Listeners for values
        if (fTitle) fTitle.addEventListener('input', () => mTitle.textContent = fTitle.value);
        if (fIntro) fIntro.addEventListener('input', () => mIntro.textContent = fIntro.value);
        if (fIntroScript) fIntroScript.addEventListener('input', () => mIntroScript.textContent = fIntroScript.value);
        if (fDesc) fDesc.addEventListener('input', () => mDesc.textContent = fDesc.value);
        if (fSignName) fSignName.addEventListener('input', () => mSignName.textContent = fSignName.value);
        if (fSignTitle) fSignTitle.addEventListener('input', () => mSignTitle.textContent = fSignTitle.value);
        
        // Listeners for colors
        const updateColors = () => {
            if (fTextCol) mMockContainer.style.color = fTextCol.value;
            if (fTextCol) mTitle.style.color = fTextCol.value;
            if (fAccentCol) {
                mInnerBorder.style.borderColor = fAccentCol.value;
                mIntroScript.style.color = fAccentCol.value;
            }
            if (fBorderCol) mBorder.style.borderColor = fBorderCol.value;
        };
        
        if (fTextCol) fTextCol.addEventListener('input', updateColors);
        if (fAccentCol) fAccentCol.addEventListener('input', updateColors);
        if (fBorderCol) fBorderCol.addEventListener('input', updateColors);
        
        // Toggles
        const fShowWatermark = document.getElementById('fieldShowWatermark');
        const fShowQr = document.getElementById('fieldShowQr');
        const mWatermark = document.getElementById('mockWatermark');
        const mQr = document.getElementById('mockQrBox');
        
        if (fShowWatermark) fShowWatermark.addEventListener('change', () => mWatermark.style.display = fShowWatermark.checked ? 'flex' : 'none');
        if (fShowQr) fShowQr.addEventListener('change', () => mQr.style.display = fShowQr.checked ? 'flex' : 'none');
        
        // Font Family selector
        const fFontFam = document.getElementById('fieldFontFamily');
        if (fFontFam) {
            fFontFam.addEventListener('change', () => {
                mMockContainer.style.fontFamily = fFontFam.value;
            });
        }
        
        // Local File Previews
        const logoInput = document.getElementById('fileLogo');
        const mLogo = document.getElementById('mockLogo');
        if (logoInput) {
            logoInput.addEventListener('change', () => {
                if (logoInput.files && logoInput.files[0]) {
                    mLogo.src = URL.createObjectURL(logoInput.files[0]);
                }
            });
        }
        
        const sigInput = document.getElementById('fileSignature');
        const mSig = document.getElementById('mockSignature');
        if (sigInput) {
            sigInput.addEventListener('change', () => {
                if (sigInput.files && sigInput.files[0]) {
                    if (mSig) {
                        mSig.src = URL.createObjectURL(sigInput.files[0]);
                        mSig.style.display = 'block';
                    } else {
                        // Create image element on the fly
                        const placeholder = document.getElementById('mockSignaturePlaceholder');
                        if (placeholder) {
                            const img = document.createElement('img');
                            img.src = URL.createObjectURL(sigInput.files[0]);
                            img.className = 'cert-mock-signature-img';
                            img.id = 'mockSignature';
                            placeholder.replaceWith(img);
                        }
                    }
                }
            });
        }

        const bgInput = document.getElementById('fileBackground');
        const mBgImg = document.getElementById('mockWatermarkImg');
        if (bgInput) {
            bgInput.addEventListener('change', () => {
                if (bgInput.files && bgInput.files[0]) {
                    mBgImg.src = URL.createObjectURL(bgInput.files[0]);
                }
            });
        }
        
        // Initial color/preview alignment
        updateColors();
        if (fFontFam) mMockContainer.style.fontFamily = fFontFam.value;
    };
    bindLivePreview();
}
</script>
