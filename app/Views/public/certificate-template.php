<?php
declare(strict_types=1);
use App\Core\Security;

// Unpack template variables
$template = $template ?? [];
$layout = [];
if (!empty($template['layout_json'])) {
    $layout = json_decode((string) $template['layout_json'], true) ?: [];
}

$style = $layout['style'] ?? [];
$title = $layout['title'] ?? 'CERTIFICATE OF COMPLETION';
$intro = $layout['intro'] ?? 'This is to certify that';
$introScript = $layout['intro_script'] ?? 'has successfully completed the training programme for :';
$description = $layout['description'] ?? 'Congratulations on your active participation in this program which have equipped you with valuable knowledge and skills...';
$signatoryName = $layout['signatory_name'] ?? 'Dato Haji Syeed Mohd Hussien Bin Wan Abd Rahman';
$signatoryTitle = $layout['signatory_title'] ?? 'Chief Executive Officer';
$organization = $layout['organization'] ?? 'Centre for Technology Excellence Sarawak';
$showWatermark = (bool) ($layout['show_watermark'] ?? true);
$showQr = (bool) ($layout['show_qr'] ?? true);

$textColor = $template['text_color'] ?? '#182230';
$accentColor = $style['accent_color'] ?? '#aa3338';
$borderColor = $style['border_color'] ?? '#aa3338';
$titleColor = $style['title_color'] ?? '#000000';
$patternOpacity = $style['pattern_opacity'] ?? 0.05;

$logo = $template['logo'] ?? '';
$signature = $template['signature'] ?? '';
$background = $template['background_image'] ?? '';

// Build verification URL for QR code
$verifyUrl = APP_URL . '/index.php?page=verify-certificate&code=' . urlencode((string) $certificate['verification_code']);
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Certificate Verification Preview</title>
    <!-- Include premium Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800;900&family=Playball&display=swap" rel="stylesheet">
    <style>
        :root {
            --cert-text: <?= Security::e($textColor) ?>;
            --cert-title: <?= Security::e($titleColor) ?>;
            --cert-accent: <?= Security::e($accentColor) ?>;
            --cert-border: <?= Security::e($borderColor) ?>;
            --cert-font: <?= Security::e($template['font_family'] ?? 'Inter, sans-serif') ?>;
        }
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
            background: #f4f6f9;
            font-family: var(--cert-font);
            color: var(--cert-text);
        }
        .actions-panel {
            background: #fff;
            padding: 12px 24px;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        .btn-download {
            background: #054d9e;
            color: #fff;
            padding: 8px 18px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            transition: background 0.2s;
        }
        .btn-download:hover {
            background: #043972;
        }
        .btn-word {
            background: #2b579a;
            color: #fff;
            padding: 8px 18px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            transition: background 0.2s;
            margin-right: 10px;
        }
        .btn-word:hover {
            background: #1e3f6f;
        }
        .cert-outer-wrapper {
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 40px 20px;
        }
        .cert-frame {
            box-sizing: border-box;
            width: 100%;
            max-width: 800px;
            aspect-ratio: 1 / 1.414; /* A4 Portrait */
            background: #ffffff;
            position: relative;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            overflow: hidden;
            font-family: var(--cert-font);
        }
        
        /* Watermark Background */
        .cert-watermark-bg {
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
            opacity: <?= Security::e((string) $patternOpacity) ?>;
            z-index: 1;
        }
        .cert-watermark-bg img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }
        
        /* Main Content Container */
        .cert-content {
            position: relative;
            z-index: 2;
            padding-top: 10%;
            text-align: center;
        }
        
        .cert-logo-img {
            height: 160px;
            margin-bottom: 20px;
            object-fit: contain;
        }
        .cert-title {
            font-size: 52px;
            font-weight: 900;
            line-height: 1.1;
            color: var(--cert-title);
            margin: 0;
        }
        .cert-subtitle {
            font-size: 28px;
            font-weight: bold;
            margin: 0 0 20px 0;
            color: var(--cert-title);
        }
        .cert-intro {
            font-size: 16px;
            font-weight: bold;
            color: var(--cert-text);
            margin: 0 0 25px 0;
        }
        .cert-recipient {
            font-size: 26px;
            font-weight: bold;
            text-transform: uppercase;
            color: #000;
            margin: 0 0 25px 0;
        }
        .cert-intro-script {
            font-size: 24px;
            font-family: 'Playball', 'Brush Script MT', 'Lucida Handwriting', cursive, serif;
            color: var(--cert-text);
            margin: 0 0 20px 0;
        }
        .cert-course {
            font-size: 22px;
            font-weight: bold;
            text-transform: uppercase;
            color: #000;
            margin: 0 0 5px 0;
        }
        .cert-course-date {
            font-size: 16px;
            font-weight: bold;
            margin: 0 0 25px 0;
        }
        .cert-description {
            font-size: 14px;
            line-height: 1.5;
            color: var(--cert-text);
            max-width: 85%;
            margin: 0 auto;
        }
        
        /* Footer */
        .cert-footer {
            position: absolute;
            bottom: 8%;
            left: 8%;
            right: 8%;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            z-index: 2;
        }
        .cert-footer-left {
            width: 25%;
            text-align: left;
        }
        .cert-footer-center {
            width: 50%;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .cert-footer-right {
            width: 25%;
        }
        
        .cert-qr-img {
            width: 90px;
            height: 90px;
            margin-bottom: 5px;
            object-fit: contain;
        }
        .cert-qr-label {
            font-size: 10px;
            color: var(--cert-accent);
            font-weight: bold;
            margin-bottom: 2px;
        }
        .cert-qr-no {
            font-size: 11px;
            font-weight: bold;
        }
        
        .cert-signature-img {
            height: 90px;
            margin-bottom: 5px;
            object-fit: contain;
        }
        .cert-sign-spacer {
            height: 60px;
        }
        .cert-sign-line {
            width: 350px;
            border-top: 1.5px solid #000;
            padding-top: 8px;
            margin-top: 5px;
            line-height: 1.2;
        }
        .cert-sign-name {
            font-size: 14px;
            font-weight: bold;
            display: block;
            margin-bottom: 3px;
        }
        .cert-sign-title {
            font-size: 12px;
            display: block;
            color: #333;
            margin-bottom: 2px;
        }
        .cert-sign-org {
            font-size: 11px;
            display: block;
            color: #555;
        }

        /* print media format */
        @media print {
            .actions-panel {
                display: none;
            }
            .cert-outer-wrapper {
                padding: 0;
            }
            .cert-frame {
                box-shadow: none;
                max-width: 100%;
            }
        }
    </style>
</head>
<body>

<div class="actions-panel">
    <div>
        <span style="font-weight:700;">Certificate Authenticity Status:</span>
        <span style="color:#16a34a; font-weight:700; margin-left: 5px;">✓ VERIFIED</span>
    </div>
    <div>
        <a href="<?= APP_URL ?>/index.php?page=download-certificate-word&id=<?= Security::e($certificate['id'] ?? '') ?>" class="btn-word">Download as Word (.doc)</a>
        <a href="<?= APP_URL ?>/index.php?page=download-certificate&id=<?= Security::e($certificate['id'] ?? '') ?>" class="btn-download">Download Official PDF</a>
    </div>
</div>

<div class="cert-outer-wrapper">
    <div class="cert-frame">
        
        <!-- Watermark Background -->
        <?php if ($showWatermark): ?>
            <div class="cert-watermark-bg">
                <?php if ($background): ?>
                    <img src="<?= APP_URL ?>/storage/uploads/<?= Security::e($background) ?>" alt="">
                <?php else: ?>
                    <img src="<?= APP_URL ?>/public/assets/img/centexs-logo-with-outline-1.png" alt="">
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <!-- Main Content -->
        <div class="cert-content">
            <?php if ($logo): ?>
                <img class="cert-logo-img" src="<?= APP_URL ?>/storage/uploads/<?= Security::e($logo) ?>" alt="">
            <?php else: ?>
                <img class="cert-logo-img" src="<?= APP_URL ?>/public/assets/img/centexs-logo-with-outline-1.png" alt="">
            <?php endif; ?>
            
            <div class="cert-title">CERTIFICATE</div>
            <div class="cert-subtitle">OF COMPLETION</div>
            
            <div class="cert-intro"><?= Security::e($intro) ?></div>
            
            <div class="cert-recipient"><?= Security::e($certificate['trainee_name'] ?? 'Participant Name') ?></div>
            
            <div class="cert-intro-script"><?= Security::e($introScript) ?></div>
            
            <div class="cert-course"><?= Security::e($certificate['course_title'] ?? 'Course Title') ?></div>
            <div class="cert-course-date">
                (<?= Security::e(strtoupper(date('d F Y', strtotime($certificate['issue_date'] ?? $certificate['issued_at'] ?? date('Y-m-d'))))) ?>)
            </div>
            
            <div class="cert-description"><?= Security::e($description) ?></div>
        </div>
            
        <!-- Footer -->
        <div class="cert-footer">
            <div class="cert-footer-left">
                <?php if ($showQr): ?>
                    <img class="cert-qr-img" src="https://api.qrserver.com/v1/create-qr-code/?size=120x120&data=<?= urlencode($verifyUrl) ?>" alt="Verification QR">
                    <div class="cert-qr-label">Certificate No.</div>
                    <div class="cert-qr-no"><?= Security::e($certificate['certificate_number'] ?? $certificate['certificate_no'] ?? 'ITOP-YYYY-0000') ?></div>
                <?php endif; ?>
            </div>
            
            <div class="cert-footer-center">
                <?php if ($signature): ?>
                    <img src="<?= APP_URL ?>/storage/uploads/<?= Security::e($signature) ?>" class="cert-signature-img" alt="">
                <?php else: ?>
                    <div class="cert-sign-spacer"></div>
                <?php endif; ?>
                
                <div class="cert-sign-line">
                    <span class="cert-sign-name"><?= Security::e($signatoryName) ?></span>
                    <span class="cert-sign-title"><?= Security::e($signatoryTitle) ?></span>
                    <span class="cert-sign-org"><?= Security::e($organization) ?></span>
                </div>
            </div>
            
            <div class="cert-footer-right"></div>
        </div>
        
    </div>
</div>

</body>
</html>
