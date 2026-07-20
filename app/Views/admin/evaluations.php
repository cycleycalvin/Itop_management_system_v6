<?php use App\Core\Security; use App\Core\View; ?>
<section class="container py-4">
    <?php View::partial('partials/role-nav'); ?>
    <h1 class="h3 mb-3">Evaluation Reports</h1>
    
    <!-- Summary Metrics Card -->
    <div class="row g-3 mb-4 animate-in">
        <div class="col-md-4">
            <div class="overview-panel d-flex align-items-center gap-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background: rgba(5, 77, 158, 0.1); color: #054d9e;">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                </div>
                <div>
                    <span class="text-muted small d-block">Total Feedbacks</span>
                    <h4 class="mb-0 fw-bold text-dark"><?= $totalCount ?></h4>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="overview-panel d-flex align-items-center gap-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background: rgba(234, 88, 12, 0.1); color: #ea580c;">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                </div>
                <div>
                    <span class="text-muted small d-block">Average Course Rating</span>
                    <h4 class="mb-0 fw-bold text-dark"><?= number_format($avgCourse, 1) ?> <span class="text-muted" style="font-size: 0.8rem;">/ 5.0</span></h4>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="overview-panel d-flex align-items-center gap-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background: rgba(22, 163, 74, 0.1); color: #16a34a;">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                </div>
                <div>
                    <span class="text-muted small d-block">Average Instructor Rating</span>
                    <h4 class="mb-0 fw-bold text-dark"><?= number_format($avgInstructor, 1) ?> <span class="text-muted" style="font-size: 0.8rem;">/ 5.0</span></h4>
                </div>
            </div>
        </div>
    </div>

    <div class="panel table-responsive">
        <table class="table align-middle">
            <thead><tr><th>Course</th><th>Trainee</th><th>Course Rating</th><th>Instructor Rating</th><th>Feedback</th><th>Submitted</th></tr></thead>
            <tbody>
                <?php foreach ($evaluations as $evaluation): ?>
                    <tr><td><?= Security::e($evaluation['course_title']) ?><br><span class="small text-muted"><?= Security::e($evaluation['instructor_name'] ?? '-') ?></span></td><td><?= Security::e($evaluation['trainee_name']) ?></td><td><?= (int) ($evaluation['course_rating'] ?? $evaluation['rating']) ?>/5</td><td><?= (int) ($evaluation['instructor_rating'] ?? 0) ?>/5</td><td><?= Security::e(Security::excerpt($evaluation['feedback'] ?? $evaluation['comments'] ?? '', 120)) ?></td><td><?= Security::e($evaluation['completed_at'] ?? $evaluation['created_at']) ?></td></tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
