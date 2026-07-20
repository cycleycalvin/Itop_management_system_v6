<?php use App\Core\Security; ?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Certificate</title>
    <style>
        :root{
            --centex-primary: #0b5b66;
            --centex-accent: #cfe8e9;
            --centex-secondary: #b93b3b;
            --centex-text: #182230;
            --centex-muted: #5b6f74;
        }
        html,body { height:100%; }
        body { margin:0; font-family: <?= htmlspecialchars($template['font_family'] ?? 'Georgia, serif') ?>; color: var(--centex-text); background: #f6fbfb; }
        .frame { box-sizing: border-box; padding:30px; border:20px solid var(--centex-primary); background: linear-gradient(180deg,#f9fbfb, #ffffff); min-height:100vh; }
        /* decorative inner patterned border using SVG data URI */
        .inner { border:8px solid var(--centex-accent); padding:48px; position:relative; background: white; box-shadow: 0 4px 18px rgba(13,32,33,0.06); }
        .inner::before { content:""; position:absolute; inset:12px; pointer-events:none; border:6px solid transparent; background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="40" height="40"><rect width="40" height="40" fill="none" stroke="%230b5b66" stroke-width="0.6" opacity="0.08"/></svg>'); background-repeat: repeat; mix-blend-mode:overlay; }
        .logo { position:absolute; left:48px; top:36px; }
        .title { text-align:center; margin-top:10px; color: var(--centex-secondary); font-size:48px; font-weight:800; letter-spacing:0.6px; font-family: 'Segoe UI', Tahoma, sans-serif }
        .recipient { text-align:center; margin-top:26px; font-size:34px; font-weight:700; color: var(--centex-text); font-family: 'Segoe UI', Tahoma, sans-serif }
        .line { text-align:center; margin-top:12px; color: var(--centex-muted); font-size:16px }
        .badge { display:block; margin:28px auto; width:120px; height:120px; border-radius:50%; border:6px solid var(--centex-secondary); display:flex; align-items:center; justify-content:center; font-weight:700; color:var(--centex-secondary); background: #fffaf9; font-family: 'Segoe UI', Tahoma, sans-serif }
        .meta { text-align:center; margin-top:8px; color:var(--centex-muted); font-size:13px }
        .footer { display:flex; justify-content:space-between; align-items:flex-end; margin-top:28px; }
        .signature { text-align:right; color: var(--centex-text); }
        .valid { font-size:12px; color: var(--centex-muted); }
        .watermark { position:absolute; inset:0; display:flex; align-items:center; justify-content:center; pointer-events:none; opacity:0.06; font-size:120px; transform:rotate(-10deg); color:var(--centex-primary); font-weight:800 }
        /* print sizing */
        @media print { html,body{height:auto;} .frame{padding:10px;border-width:6px} .inner{padding:24px} .watermark{opacity:0.04}} 
    </style>
    </head>
<body>
<div class="frame">
    <div class="inner">
        <?php if (!empty($template['logo'])): ?>
            <img class="logo" src="<?= APP_URL ?>/storage/uploads/<?= Security::e($template['logo']) ?>" alt="logo" style="height:70px;">
        <?php endif; ?>
        <?php if (!empty($template['background_image'])): ?>
            <div style="position:absolute;inset:0;opacity:0.06;background:url('<?= APP_URL ?>/storage/uploads/<?= Security::e($template['background_image']) ?>') center/cover no-repeat;pointer-events:none;"></div>
        <?php endif; ?>
        <div class="watermark"><?= htmlspecialchars($certificate['course_title'] ?? 'CERTIFICATE') ?></div>
        <div class="title">Huawei Certification</div>
        <div class="recipient"><?= Security::e($certificate['trainee_name'] ?? 'Recipient Name') ?></div>
        <div class="line">has successfully completed the certification requirements and is recognized as a</div>
        <div style="text-align:center; margin-top:8px; color:#b93b3b; font-weight:600;"><?= Security::e($certificate['course_title'] ?? 'Certified Specialist') ?></div>
        <div class="badge">HCSA</div>
        <div style="text-align:center; margin-top:18px;"><a href="<?= APP_URL ?>/index.php?page=download-certificate&id=<?= Security::e($certificate['id'] ?? '') ?>" class="btn-download" style="display:inline-block;background:var(--centex-primary);color:#fff;padding:10px 18px;border-radius:6px;text-decoration:none;margin-bottom:10px;">Download PDF</a></div>

        <div class="footer">
            <div class="valid">Valid Through <?= Security::e(date('M d, Y', strtotime($certificate['issue_date'] ?? $certificate['issued_at'] ?? date('Y-m-d')))) ?></div>
            <div class="signature">
                <?php if (!empty($template['signature'])): ?>
                    <img src="<?= APP_URL ?>/storage/uploads/<?= Security::e($template['signature']) ?>" alt="signature" style="height:60px;"><br>
                <?php else: ?>
                    <div style="height:60px;"></div>
                <?php endif; ?>
                <div>CEO</div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
