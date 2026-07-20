<?php use App\Core\Security; use App\Core\View; ?>

<!-- ── Hero Section ── -->
<section class="hero-band py-5">
    <div class="container py-4 py-lg-5">
        <div class="row align-items-center g-4">
            <div class="col-lg-7">
                <span class="badge text-bg-light mb-3 fw-bold px-3 py-2 text-uppercase" style="letter-spacing:0.08em;font-size:0.75rem;color:var(--ims-primary)!important">CENTEXS ITOP IMS</span>
                <h1 class="display-5 fw-bold mb-3"><?= Security::e($settings['hero_title'] ?? 'CENTEXS training operations in one secure web platform') ?></h1>
                <p class="lead mb-4" style="opacity:0.9;font-size:1.1rem;line-height:1.7"><?= Security::e($settings['hero_subtitle'] ?? 'Manage programme registration, learning materials, assessments, progress, reports, evaluations, and certificates for real-world ITOP delivery.') ?></p>
                <div class="d-flex flex-wrap gap-2">
                    <a class="btn btn-light btn-lg px-4 py-2.5 fw-bold" href="index.php?page=courses">Browse Courses</a>
                    <?php if (!\App\Core\Auth::check()): ?>
                        <a class="btn btn-outline-light btn-lg px-4 py-2.5 fw-bold" href="index.php?page=register">Create Account</a>
                    <?php else: ?>
                        <a class="btn btn-outline-light btn-lg px-4 py-2.5 fw-bold" href="index.php?page=dashboard">Go to Dashboard</a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="metric-panel">
                    <div><strong><?= count($courses) ?></strong><span>Available Courses</span></div>
                    <div><strong><?= count($announcements) ?></strong><span>Announcements</span></div>
                    <div><strong>24/7</strong><span>Self-Service Portal</span></div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ── Academies Overview ── -->
<section class="container py-5">
    <div class="text-center mb-5">
        <span class="section-label">Our Training Centers</span>
        <h2 class="section-title">Specialist Academies</h2>
        <p class="text-muted max-width-md mx-auto">Explore courses designed to meet next-generation industry needs in Sarawak.</p>
    </div>
    <div class="row g-4">
        <!-- ADGEA -->
        <div class="col-md-6">
            <a href="index.php?page=courses&academy=ADGEA" class="academy-card">
                <span class="academy-chip">ADGEA</span>
                <h2>Aerospace, Digital & Green Energy Academy</h2>
                <p>Equipping talents with high-value skills in modern digital technologies, aerospace engineering, and green energy initiatives.</p>
                <div class="academy-meta">
                    <div>
                        <strong><?php
                            $adgeaCourses = array_filter($courses, fn($c) => strtoupper($c['academy_code'] ?? '') === 'ADGEA');
                            echo count($adgeaCourses);
                        ?></strong>
                        <span>Courses</span>
                    </div>
                </div>
            </a>
        </div>
        <!-- IESGA -->
        <div class="col-md-6">
            <a href="index.php?page=courses&academy=IESGA" class="academy-card">
                <span class="academy-chip">IESGA</span>
                <h2>Industry & ESG Academy</h2>
                <p>Providing professional qualifications and certification in safety management, industrial machinery, and environmental sustainability.</p>
                <div class="academy-meta">
                    <div>
                        <strong><?php
                            $iesgaCourses = array_filter($courses, fn($c) => strtoupper($c['academy_code'] ?? '') === 'IESGA');
                            echo count($iesgaCourses);
                        ?></strong>
                        <span>Courses</span>
                    </div>
                </div>
            </a>
        </div>
    </div>
</section>

<!-- ── Course Preview Section (Limit to 3) ── -->
<section class="container py-5">
    <div class="d-flex justify-content-between align-items-end mb-4">
        <div>
            <span class="section-label">Featured Courses</span>
            <h2 class="section-title mb-0">Course Preview</h2>
        </div>
        <a href="index.php?page=courses" class="btn btn-outline-primary fw-bold" style="border-radius:999px;font-size:0.85rem">View All Courses →</a>
    </div>
    <div class="row g-4">
        <?php foreach (array_slice($courses, 0, 3) as $course): ?>
            <div class="col-md-6 col-xl-4"><?php View::partial('partials/course-card', ['course' => $course, 'actions' => 'public']); ?></div>
        <?php endforeach; ?>
        <?php if (!$courses): ?>
            <div class="col-12"><div class="overview-panel text-muted text-center py-4">No public courses are available yet.</div></div>
        <?php endif; ?>
    </div>
</section>

<!-- ── Upcoming Intakes ── -->
<?php if (($settings['show_upcoming_intakes'] ?? '1') === '1' && !empty($intakes)): ?>
    <section class="container py-5">
        <div class="mb-4">
            <span class="section-label">Important Dates</span>
            <h2 class="section-title">Upcoming Intakes</h2>
        </div>
        <div class="intake-grid">
            <?php foreach ($intakes as $intake): ?>
                <div class="intake-card">
                    <span class="intake-date">
                        <span>📅</span> <?= date('d M Y', strtotime($intake['intake_date'])) ?>
                    </span>
                    <h3 class="intake-title"><?= Security::e($intake['intake_title']) ?></h3>
                    <p class="intake-desc"><?= Security::e($intake['description']) ?></p>
                    <a href="index.php?page=register" class="small fw-bold text-decoration-none mt-2 d-inline-block" style="color:var(--ims-primary)">Apply Now →</a>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
<?php endif; ?>

<!-- ── Latest Announcements ── -->
<?php if (($settings['show_announcements_home'] ?? '1') === '1' && !empty($announcements)): ?>
    <section class="container py-5">
        <div class="mb-4">
            <span class="section-label">Updates</span>
            <h2 class="section-title">Latest Announcements</h2>
        </div>
        <div class="announcement-grid">
            <?php foreach (array_slice($announcements, 0, 3) as $ann): ?>
                <div class="announcement-card">
                    <h3 class="announcement-card-title"><?= Security::e($ann['title']) ?></h3>
                    <p class="announcement-card-body"><?= Security::e($ann['body']) ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
<?php endif; ?>

<!-- ── Success Stories ── -->
<?php if (($settings['show_success_stories'] ?? '1') === '1' && !empty($stories)): ?>
    <section class="container py-5">
        <div class="mb-4 text-center">
            <span class="section-label">Alumni Network</span>
            <h2 class="section-title">Success Stories</h2>
        </div>
        <div class="stories-grid">
            <?php foreach ($stories as $story): ?>
                <div class="story-card">
                    <p class="story-quote"><?= Security::e($story['quote']) ?></p>
                    <div class="story-author mt-3">
                        <div class="story-avatar">
                            <?= Security::e(strtoupper(substr($story['trainee_name'], 0, 1))) ?>
                        </div>
                        <div>
                            <div class="story-name"><?= Security::e($story['trainee_name']) ?></div>
                            <div class="story-course"><?= Security::e($story['course_title']) ?></div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
<?php endif; ?>
