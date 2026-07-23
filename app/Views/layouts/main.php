<?php
use App\Core\Auth;
use App\Core\Security;
use App\Models\Message;
use App\Models\Notification;

$messageCount = Auth::check() ? (new Message())->unreadCount((int) Auth::id()) : 0;
$notificationCount = Auth::check() ? (new Notification())->unreadCount((int) Auth::id()) : 0;

// Load website settings for footer
$footerSettings = [];
try {
    $db = \App\Core\Model::getDb();
    $stmt = $db->query('SELECT setting_key, setting_value FROM website_settings');
    foreach ($stmt->fetchAll() as $row) {
        $footerSettings[$row['setting_key']] = $row['setting_value'];
    }
} catch (\Exception $e) {
    // fallback silently
}
?>
<?php
$userTheme = Auth::check() ? (Auth::user()['theme_preference'] ?? 'light') : 'light';
?>
<!doctype html>
<html lang="en" data-theme="<?= Security::e($userTheme) ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?= Security::e(Security::csrfToken()) ?>">
    <meta name="description" content="CENTEXS ITOP Management System — Manage programme registration, learning materials, assessments, and certification.">
    <title><?= Security::e(APP_NAME) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= APP_URL ?>/public/assets/css/app.css?v=2.8" rel="stylesheet">
</head>
<body>

<?php if (Auth::check()): ?>
    <!-- ══ Authenticated Layout: Sidebar + Pushed Content ══ -->
    <?php \App\Core\View::partial('partials/sidenav'); ?>

    <div class="sidenav-content" id="sidenavContent">
        <!-- Top Bar (Dashboard context) -->
        <header class="topbar" id="topbar">
            <div class="topbar-inner">
                <div class="topbar-left">
                    <button class="topbar-toggle" id="sidenavToggle" aria-label="Toggle sidebar">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
                    </button>
                    <form class="topbar-search" method="get" action="index.php">
                        <input type="hidden" name="page" value="courses">
                        <svg class="topbar-search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        <input class="topbar-search-input" name="q" placeholder="Search courses..." autocomplete="off">
                    </form>
                </div>
                <div class="topbar-right">
                    <a class="topbar-icon-btn" href="index.php?page=messages" title="Messages">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                        <?php if ($messageCount > 0): ?>
                            <span class="topbar-badge" data-badge="messages"><?= (int) $messageCount ?></span>
                        <?php endif; ?>
                    </a>
                    <a class="topbar-icon-btn" href="index.php?page=notifications" title="Notifications">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                        <?php if ($notificationCount > 0): ?>
                            <span class="topbar-badge" data-badge="notifications"><?= (int) $notificationCount ?></span>
                        <?php endif; ?>
                    </a>
                    <div class="topbar-user-pill d-flex align-items-center gap-2 px-3 py-1.5 rounded-pill bg-light border">
                        <span class="topbar-user-name fw-semibold small text-dark"><?= Security::e(Auth::user()['name']) ?></span>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content Area -->
        <main class="main-content">
            <div class="main-content-inner">
                <?= $content ?>
            </div>
        </main>

        <!-- Footer (inside pushed content) -->
        <footer class="site-footer">
            <div class="container">
                <div class="footer-main">
                    <!-- Brand Column -->
                    <div class="footer-brand">
                        <h3>CENTEXS IMS</h3>
                        <p><?= Security::e($footerSettings['footer_about'] ?? 'Centre for Technology Excellence Sarawak (CENTEXS) is a premier training institution in Sarawak, delivering industry-driven technical and vocational programmes.') ?></p>
                    </div>

                    <!-- Quick Links -->
                    <div>
                        <h4 class="footer-heading">Quick Links</h4>
                        <ul class="footer-links">
                            <li><a href="index.php">Home</a></li>
                            <li><a href="index.php?page=about">About ITOP</a></li>
                            <li><a href="index.php?page=courses">Browse Courses</a></li>
                            <li><a href="index.php?page=news">News & Updates</a></li>
                            <li><a href="index.php?page=contact">Contact Us</a></li>
                            <li><a href="index.php?page=verify-certificate">Verify Certificate</a></li>
                        </ul>
                    </div>

                    <!-- Academies -->
                    <div>
                        <h4 class="footer-heading">Academies</h4>
                        <ul class="footer-links">
                            <li><a href="index.php?page=courses&academy=ADGEA">ADGEA</a></li>
                            <li><a href="index.php?page=courses&academy=IESGA">IESGA</a></li>
                            <li><a href="index.php?page=register">Register Now</a></li>
                            <li><a href="index.php?page=login">Trainee Login</a></li>
                        </ul>
                    </div>

                    <!-- Contact Info -->
                    <div>
                        <h4 class="footer-heading">Contact Us</h4>
                        <div class="footer-contact-item">
                            <span class="footer-contact-icon">📍</span>
                            <span><?= Security::e($footerSettings['footer_address'] ?? 'CENTEXS Kuching, Jalan Canna, Off Jalan Wan Alwi, 93350 Kuching, Sarawak, Malaysia') ?></span>
                        </div>
                        <div class="footer-contact-item">
                            <span class="footer-contact-icon">📧</span>
                            <span><?= Security::e($footerSettings['footer_email'] ?? 'info@centexs.my') ?></span>
                        </div>
                        <div class="footer-contact-item">
                            <span class="footer-contact-icon">📞</span>
                            <span><?= Security::e($footerSettings['footer_phone'] ?? '+60 82-363 200') ?></span>
                        </div>
                    </div>
                </div>

                <!-- Bottom Bar -->
                <div class="footer-bottom">
                    <span>&copy; <?= date('Y') ?> Centre for Technology Excellence Sarawak. All rights reserved.</span>
                    <div class="footer-social">
                        <?php if (!empty($footerSettings['footer_social_facebook'])): ?>
                            <a href="<?= Security::e($footerSettings['footer_social_facebook']) ?>" target="_blank" rel="noopener" title="Facebook">f</a>
                        <?php endif; ?>
                        <?php if (!empty($footerSettings['footer_social_linkedin'])): ?>
                            <a href="<?= Security::e($footerSettings['footer_social_linkedin']) ?>" target="_blank" rel="noopener" title="LinkedIn">in</a>
                        <?php endif; ?>
                        <a href="mailto:<?= Security::e($footerSettings['footer_email'] ?? 'info@centexs.my') ?>" title="Email">@</a>
                    </div>
                </div>
            </div>
        </footer>
    </div>

<?php else: ?>
    <!-- ══ Public Layout: Classic Navbar ══ -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary sticky-top">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">
                <img src="public/assets/img/centexs-logo-with-outline-1.png" alt="CENTEXS Logo" height="30" class="d-inline-block align-text-top me-2">
                CENTEXS IMS
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav"><span class="navbar-toggler-icon"></span></button>
            <div class="collapse navbar-collapse" id="nav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link" href="index.php?page=about">About ITOP</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php?page=courses">Courses</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php?page=news">News</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php?page=contact">Contact</a></li>
                </ul>
                <form class="d-none d-lg-flex me-3" method="get" action="index.php">
                    <input type="hidden" name="page" value="courses">
                    <input class="form-control form-control-sm top-search" name="q" placeholder="Search courses">
                </form>
                <div class="d-flex gap-2 align-items-center">
                    <a class="btn btn-outline-light btn-sm" href="index.php?page=login">Login</a>
                    <a class="btn btn-light btn-sm" href="index.php?page=register">Register</a>
                </div>
            </div>
        </div>
    </nav>

    <main>
        <?= $content ?>
    </main>

    <!-- ══ CENTEXS-Style Footer ══ -->
    <footer class="site-footer">
        <div class="container">
            <div class="footer-main">
                <div class="footer-brand">
                    <h3>CENTEXS IMS</h3>
                    <p><?= Security::e($footerSettings['footer_about'] ?? 'Centre for Technology Excellence Sarawak (CENTEXS) is a premier training institution in Sarawak, delivering industry-driven technical and vocational programmes.') ?></p>
                </div>
                <div>
                    <h4 class="footer-heading">Quick Links</h4>
                    <ul class="footer-links">
                        <li><a href="index.php">Home</a></li>
                        <li><a href="index.php?page=about">About ITOP</a></li>
                        <li><a href="index.php?page=courses">Browse Courses</a></li>
                        <li><a href="index.php?page=news">News & Updates</a></li>
                        <li><a href="index.php?page=contact">Contact Us</a></li>
                        <li><a href="index.php?page=verify-certificate">Verify Certificate</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="footer-heading">Academies</h4>
                    <ul class="footer-links">
                        <li><a href="index.php?page=courses&academy=ADGEA">ADGEA</a></li>
                        <li><a href="index.php?page=courses&academy=IESGA">IESGA</a></li>
                        <li><a href="index.php?page=register">Register Now</a></li>
                        <li><a href="index.php?page=login">Trainee Login</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="footer-heading">Contact Us</h4>
                    <div class="footer-contact-item">
                        <span class="footer-contact-icon">📍</span>
                        <span><?= Security::e($footerSettings['footer_address'] ?? 'CENTEXS Kuching, Jalan Canna, Off Jalan Wan Alwi, 93350 Kuching, Sarawak, Malaysia') ?></span>
                    </div>
                    <div class="footer-contact-item">
                        <span class="footer-contact-icon">📧</span>
                        <span><?= Security::e($footerSettings['footer_email'] ?? 'info@centexs.my') ?></span>
                    </div>
                    <div class="footer-contact-item">
                        <span class="footer-contact-icon">📞</span>
                        <span><?= Security::e($footerSettings['footer_phone'] ?? '+60 82-363 200') ?></span>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <span>&copy; <?= date('Y') ?> Centre for Technology Excellence Sarawak. All rights reserved.</span>
                <div class="footer-social">
                    <?php if (!empty($footerSettings['footer_social_facebook'])): ?>
                        <a href="<?= Security::e($footerSettings['footer_social_facebook']) ?>" target="_blank" rel="noopener" title="Facebook">f</a>
                    <?php endif; ?>
                    <?php if (!empty($footerSettings['footer_social_linkedin'])): ?>
                        <a href="<?= Security::e($footerSettings['footer_social_linkedin']) ?>" target="_blank" rel="noopener" title="LinkedIn">in</a>
                    <?php endif; ?>
                    <a href="mailto:<?= Security::e($footerSettings['footer_email'] ?? 'info@centexs.my') ?>" title="Email">@</a>
                </div>
            </div>
        </div>
    </footer>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= APP_URL ?>/public/assets/js/app.js?v=2.8"></script>
</body>
</html>
