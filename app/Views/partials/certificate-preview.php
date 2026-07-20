<?php
use App\Core\Security;

$template = $template ?? [];
$sample = array_merge([
    'title' => 'CENTEXS Certification',
    'recipient' => 'Participant Name',
    'intro' => 'has successfully completed the certification requirements and is recognized for',
    'course' => 'Programme / Course Title',
    'subtitle' => 'Certificate of Completion',
    'date_label' => 'Issued On',
    'date' => date('F j, Y'),
    'certificate_no' => 'ITOP-YYYY-0000',
    'issuer_title' => 'Authorized Signatory',
    'organization' => 'CENTEXS',
    'verification' => 'Verify this certificate using the certificate number.',
], $sample ?? []);

$layout = [];
if (!empty($template['layout_json'])) {
    $decoded = json_decode((string) $template['layout_json'], true);
    $layout = is_array($decoded) ? $decoded : [];
}

$style = $layout['style'] ?? [];
$titleColor = $style['title_color'] ?? '#aa3338';
$accentColor = $style['accent_color'] ?? '#244f63';
$borderColor = $style['border_color'] ?? '#25566a';
$sealColor = $style['seal_color'] ?? '#b53638';
$patternOpacity = (float) ($style['pattern_opacity'] ?? 0.45);
$showSeal = (bool) ($layout['show_seal'] ?? true);
$showVerification = (bool) ($layout['show_verification'] ?? true);
$logo = $template['logo'] ?? '';
$signature = $template['signature'] ?? '';
$background = $template['background_image'] ?? '';
?>
<div class="certificate-preview certificate-preview-classic"
    style="
        --cert-title: <?= Security::e($titleColor) ?>;
        --cert-accent: <?= Security::e($accentColor) ?>;
        --cert-border: <?= Security::e($borderColor) ?>;
        --cert-seal: <?= Security::e($sealColor) ?>;
        --cert-pattern-opacity: <?= Security::e((string) $patternOpacity) ?>;
        font-family: <?= Security::e($template['font_family'] ?? 'Arial') ?>;
        color: <?= Security::e($template['text_color'] ?? '#182230') ?>;
        <?= $background ? 'background-image: url(storage/uploads/' . Security::e($background) . '); background-size: cover; background-position: center;' : '' ?>
    ">
    <div class="certificate-pattern"></div>
    <div class="certificate-border"></div>
    <?php if ($logo): ?>
        <img class="certificate-logo" src="storage/uploads/<?= Security::e($logo) ?>" alt="">
    <?php else: ?>
        <div class="certificate-logo certificate-logo-placeholder"><?= Security::e($sample['organization']) ?></div>
    <?php endif; ?>
    <div class="certificate-main">
        <div class="certificate-title"><?= Security::e($sample['title']) ?></div>
        <div class="certificate-recipient"><?= Security::e($sample['recipient']) ?></div>
        <div class="certificate-intro"><?= Security::e($sample['intro']) ?></div>
        <div class="certificate-course"><?= Security::e($sample['course']) ?></div>
        <div class="certificate-subtitle"><?= Security::e($sample['subtitle']) ?></div>
        <?php if ($showSeal): ?>
            <div class="certificate-seal"><span><?= Security::e($layout['seal_text'] ?? 'ITOP') ?></span><small><?= Security::e($layout['seal_caption'] ?? 'Certified') ?></small></div>
        <?php endif; ?>
        <div class="certificate-date"><?= Security::e($sample['date_label']) ?> <strong><?= Security::e($sample['date']) ?></strong></div>
    </div>
    <div class="certificate-footer-left">
        <?php if ($showVerification): ?>
            <div><?= Security::e($sample['verification']) ?></div>
            <strong><?= Security::e($sample['certificate_no']) ?></strong>
        <?php endif; ?>
    </div>
    <div class="certificate-footer-right">
        <?php if ($signature): ?><img class="certificate-signature" src="storage/uploads/<?= Security::e($signature) ?>" alt=""><?php endif; ?>
        <div class="certificate-sign-line"></div>
        <strong><?= Security::e($sample['issuer_title']) ?></strong>
        <span><?= Security::e($sample['organization']) ?></span>
    </div>
</div>
